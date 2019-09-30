# Компонент для загрузки релизов на сервер с gitlab

Пример использования

```php
$params = [
    'access_token' => 'you access token', // Ключ доступа
    'project_id'   => 1,                  // Идентификатор проэкта
    'storage_dir'  => 'storage',          // Корневая директория для работы с архивами
    'repository'   => 'release',          // Название архива сохраненого на FTP сервере
    'ftp'   => [    // Дополнительные параметры https://flysystem.thephpleague.com/docs/adapter/ftp/
        'host'      => 'ftp host',
        'username'  => 'ftp user name',
        'password'  => 'ftp password'
    ]
];

// Определение последней версии
$repository = new Repository($params['access_token']);
$archiveResult = $repository->download($params['project_id']);

// Загрузка релиза на FTP
$upload = new Uploader($params['ftp'], $params['storage_dir'], $params['repository']);
$upload->upload($archiveResult['content'], $archiveResult['version']);
```