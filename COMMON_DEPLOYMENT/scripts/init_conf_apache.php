<?php

$args = require 'parse_args.php';

$apacheVersion  = ! isset($args['apache']) ? '2.2' : $args['apache']; // || 2.4

$app = isset($args['app']) ? $args['app'] : 'app';

// Cash le path depuis root, ou juste le path depuis /var/www/, ou R et deviné selon app
$wwwPath = isset($args['www']) ?
    ($args['www'][0] === '/' ? $args['www'] : (__DIR__ . '/../../' . $args['www']))
    : (__DIR__ . '/../../' . $app . '/');

if (! file_exists($wwwPath)) {
    die('wwwPath faux');
}

$wwwPath = realpath($wwwPath);

$apacheDebugLogLevel = true;
$port = 80;

$documentRoot = $wwwPath;
$projectName  = $app;
//$documentRoot = realpath(__DIR__ . '/../beta/');
//$projectName = substr($documentRoot, strrpos($documentRoot, '/') + 1);
$confPath = '/etc/apache2/sites-available/' . $projectName . '.conf';

$virtualHost = [
    'open'  => 'VirtualHost *:' . $port,
    'close' => 'VirtualHost',
    'content' => [
        'ServerAdmin'   => 'osef',
        'ServerName'    => 'osef',
        'ServerAlias'   => 'osef',
        'DocumentRoot'  => $documentRoot,
        'ErrorLog'      => '${APACHE_LOG_DIR}/' . $projectName . '_error.log',
        'CustomLog'     => '${APACHE_LOG_DIR}/' . $projectName . '_access.log combined',
        'block' => [
            'open'  => 'Directory "' . $documentRoot . '"',
            'close' => 'Directory',
            'content' => [
                'Options' => 'All',
                'Require' => 'all granted',
                'AllowOverride' => 'All',
                'Order' => 'Allow,Deny',
                'Allow' => 'from all',
                'Deny'  => 'from 175.125.250.235' // Un spammer
            ]
        ]
    ]
];

if ($apacheDebugLogLevel)
    $virtualHost['content']['LogLevel'] = 'debug';

if ($apacheVersion === '2.2')
    unset($virtualHost['content']['block']['content']['Require']);


if (! file_put_contents($confPath, buildVirtualHostFile($virtualHost))) {
    die('Faut lancer en sudo HEIN' . PHP_EOL);
}

// 2.4
shell_exec('sudo rm -f /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf');

// 2.2
shell_exec('sudo rm -f /etc/apache2/sites-available/default /etc/apache2/sites-enabled/000-default');

$a = shell_exec('sudo a2ensite ' . $projectName . '.conf');

$b = shell_exec('sudo service apache2 restart');


if ($a) {
    echo 'Activation vhost output: ' . $a . PHP_EOL;
}

if ($b) {
    echo 'Reload output: ' . $b . PHP_EOL;
    echo 'Check /var/log/syslog si err' . PHP_EOL;
}

echo 'Apache est un mytho, c\'est reload' . PHP_EOL;


function buildVirtualHostFile($options) {
    return buildBlock($options['open'], $options['close'], $options['content'], 0);
}

function buildBlock($opening, $closing, $content, $depth) {
    $tabs = buildTabs($depth);
    $blockContent = $tabs[0] . '<' . $opening . '>' . PHP_EOL;
    foreach ($content as $key => $val) {
        if ($key === 'block') {
            $blockContent .= buildBlock($val['open'], $val['close'], $val['content'], $depth + 1);
            continue;
        }
        $blockContent .= $tabs[1] . $key . ' ' . $val . PHP_EOL;
    }
    $blockContent .= $tabs[0] . '</' . $closing . '>' . PHP_EOL;

    return $blockContent;
}

function buildTabs($depth) {
    $tabs = [''];
    while ($depth -- > 0)
        $tabs[0] .= chr(9);
    $tabs[1] = $tabs[0] . chr(9);

    return $tabs;
}


/*
<VirtualHost *:80>
    ServerAdmin nicolas.charpentier78@gmail.com
    ServerName pinapp

    DocumentRoot /var/www
    <Directory "/var/www">
        Options All
	AllowOverride All
        Order Allow,Deny
	Allow from all
	Deny from 175.125.250.235
    </Directory>

    LogLevel debug
    CustomLog ${APACHE_LOG_DIR}/pinapp_access.log combined
    ErrorLog ${APACHE_LOG_DIR}/pinapp_error.log
</VirtualHost

 */
