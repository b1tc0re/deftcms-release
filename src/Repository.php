<?php namespace DeftCMS\Components\Build;

use Gitlab\Api\Repositories;
use Gitlab\Client;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DeftCMS
 *
 * @package	    DeftCMS
 * @category    Core
 * @author	    b1tc0re
 * @copyright   (c) 2019, DeftCMS (http://deftcms.ru/)
 * @since	    Version 0.0.1
 */
class Repository
{
    /**
     * Клиент для работы с gitlab
     * @var Client
     */
    protected $client;

    /**
     * Builder constructor.
     * @param string $access_token  - Ключь доступа к gitlab
     */
    public function __construct(string $access_token)
    {
        $this->client = Client::create('http://gitlab.com')->authenticate($access_token, Client::AUTH_OAUTH_TOKEN);
    }

    /**
     * Получить последний релиз и вернуть имя тэга
     * @param int $project_id   - Идентификатор репозитория
     * @return string
     */
    public function getReleaseTag(int $project_id)
    {
        $repositories = new Repositories($this->client);
        $releases = $repositories->releases($project_id) ?? [];

        usort($releases, function($a, $b) {
            return intval($a['name']) <=> intval($b['name']);
        });

        $latest = array_shift($releases);
        return $latest['tag_name'];
    }

    /**
     * @param int $project_id       - Идентификатор репозитория
     * @param string $filepath     - Путь к файлу
     * @return bool
     */

    /**
     * Скачать последнюю версию релиза
     *
     * @param int $project_id   - Идентификатор репозитория
     * @return array
     */
    public function download(int $project_id)
    {
        $tag_name = $this->getReleaseTag($project_id);
        $repositories = new Repositories($this->client);
        $archive = $repositories->archive($project_id, ['sha' => $tag_name], 'zip');

        return  [
            'version'   => $tag_name,
            'content'   => $archive
        ];
    }
}