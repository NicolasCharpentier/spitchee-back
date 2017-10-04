<?php

$appPath = __DIR__ . '/../../SILEX_WEB_REST';

require_once $appPath .  '/vendor/autoload.php';

use Silex\Application;

$app = new Application;
$app->register(new DerAlex\Silex\YamlConfigServiceProvider($appPath . '/app/parameters.prod.yml'));

$preLogin = 'root';
$prePwd = 'root';
$doctrineInstalled = ($argc == 2 and $argv[1] !== '0');

$dbOpts = $app['config']['db'];
$newLog = $dbOpts['user'];
$newNam = $dbOpts['dbname'];
$newPwd = $dbOpts['password'];

$access = 'mysql -u' . $preLogin . ' -p' . $prePwd . ' -e ';

shell_exec($access .
    '"CREATE DATABASE ' . $newNam . '"'
);

shell_exec($access .
    '"CREATE USER \'' . $newLog . '\'@\'%\' IDENTIFIED BY \'' . $newPwd . '\'"'
);

shell_exec($access .
    '"GRANT ALL PRIVILEGES ON ' . $newNam . '.* TO \'' . $newLog . '\'@\'%\'"'
);

shell_exec($access .
    '"FLUSH privileges"'
);

if ($doctrineInstalled) { // non car il s'attends à un cli-config depuis ici -____________________________________________________-
    echo
    shell_exec(
        'php ' .  __DIR__ . '/../../' . 'vendor/bin/doctrine orm:schema-tool:create && ' .
        'php ' .  __DIR__ . '/../../' . 'vendor/bin/doctrine orm:schema-tool:update --force'
    );
}

echo 'Succès' . PHP_EOL;