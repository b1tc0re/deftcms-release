<?php namespace DeftCMS\Components\Build;

use Alchemy\Zippy\Zippy;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DeftCMS      Загрузка релиза на сервер
 *
 * @package	    DeftCMS
 * @category    Core
 * @author	    b1tc0re
 * @copyright   (c) 2019, DeftCMS (http://deftcms.ru/)
 * @since	    Version 0.0.1
 */
class Uploader
{
    /**
     * Работа с файловой системой по FTP
     * @var Filesystem
     */
    protected $fileSystemFTP;

    /**
     * Работа с локальной файловой системой
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var string
     */
    protected $repository;

    /**
     * Uploader constructor.
     * @param array $params         - Параметры подключение к FTP серверу
     * @param string $storage_dir   - Путь к корневой директории
     * @param string $repository    - Название репозитория
     */
    public function __construct(array $params, string $storage_dir, string $repository = 'release')
    {
        $adapter_ftp = new Ftp($params);
        $this->fileSystemFTP = new Filesystem($adapter_ftp);

        $adapter_local = new Local($storage_dir);
        $this->fileSystem = new Filesystem($adapter_local);

        $this->repository = $repository;
    }

    /**
     * Загрузить данные на FTP сервер
     *
     * @param string $content   - Содержимое релиза
     * @param string $version   - Версия релиза
     */
    public function upload($content, $version)
    {
        $content = $this->normalizeStructureZip($content);

        // Обновить версию релиза
        $version_path = 'release/' . $version . DIRECTORY_SEPARATOR;

        if( !$this->fileSystemFTP->has($version_path) ) {

            // Обновить версию релиза
            $path = 'release/latest/'. $this->repository .'.zip';
            $this->fileSystemFTP->put($path, $content);

            // Загрузить версию
            $this->fileSystemFTP->put($version_path .$this->repository .'.zip', $content);
        }
    }

    /**
     * Нормальзация структуры архива
     * @param string $content
     * @return string
     */
    protected function normalizeStructureZip($content)
    {
        $release_archive    = 'archive-release.zip';
        $storage_root       = $this->fileSystem->getAdapter()->getPathPrefix();
        $this->fileSystem->put($release_archive, $content);


        $zippy          = Zippy::load();
        $archive        = $zippy->open($storage_root . $release_archive);
        $archive_root   = false;

        // Получить название папки
        foreach ($archive as $member) {
            $archive_root = basename($member);
            break;
        }

        $archive->extract($storage_root);
        $archive_files = [];

        foreach ($this->fileSystem->listContents($archive_root) ?? [] as $value)
        {
            $archive_files[] = $storage_root . $value['path'];
        }

        $this->fileSystem->delete($release_archive);
        $zippy->create($storage_root . $release_archive, $archive_files);

        $this->fileSystem->deleteDir($archive_root);

        $content = $this->fileSystem->read($release_archive);
        $this->fileSystem->delete($release_archive);

        return $content;
    }
}