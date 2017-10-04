<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Container.php';

use Gedmo\Timestampable\TimestampableListener;
use Silex\Application;
use Silex\Provider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

ini_set('display_errors', 1);
error_reporting(-1);
ErrorHandler::register();
ExceptionHandler::register();
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Europe/Paris');

$app = new Container();

$mode = trim(file_get_contents(__DIR__ . '/mode.casselescouilles'));

if ($mode !== 'prod' and 'dev' !== $mode) {
    throw new \Exception("Mode $mode incompréhensible");
}

/** @var Application $app */
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . "/parameters.$mode.yml"));

//$mode         = $app['config']['mode'];
$app['debug'] = $mode === 'dev';
$app['bypassAuth'] = $mode === 'dev';


$app->register(new SilexSimpleAnnotations\AnnotationsServiceProvider(), array(
    'simpleAnnots.recursiv' => false,
    'simpleAnnots.controllersPath' => [
        __DIR__ . '/../src/Spitchee/Controller',
        __DIR__ . '/../src/SpitcheeDocumentation/Controller',
    ],
    'simpleAnnots.controllersAsApplicationAwareServices' => true,
));


$app->register(new \Spitchee\Service\Provider\SpitcheeServicesProvider(), array(
    'spitchee.services.configuration' => [
        'baseNameSpace' => 'Spitchee\\Service',
        'services' => [
            'Asterisk' => 'AsteriskServicesAsker',
            'Asterisk\\Event' => 'AsteriskEventConsequences',
            'Asterisk\\Event\\Listener' => [
                'AsteriskPeerStatusEventListener',
                'AsteriskOriginateResponseEventListener',
                'AsteriskConfBridgeEventListener',
                'AsteriskGenericEventListener',
                'AsteriskNullEventListener',
                'AsteriskUnknownEventListener',
            ],
            'Entity\\Repository' => 'Repository',
            'Entity' => [
                'Conference', 'User',
                'SipAccount', 'NamiEvent',
            ],
            'Rabbit' => 'RabbitPublisher',
        ]
    ]
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../src/SpitcheeDocumentation/Resource/View',
));

$app->register(new Provider\HttpFragmentServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider());

$app->register(new Provider\SecurityServiceProvider());
$app->register(new Provider\SessionServiceProvider(), array(
    //'session.storage.save_path' => __DIR__ . '/cache/session',
));

$app['security.firewalls'] = array( // Pour que twig fonctionne (il a besoin d'un authManager dans security)
    'secured_area' => ['pattern' => '^/$', 'anonymous' => true]
);

/*
$app['security.firewalls'] = array(
    'secured_area' => [
        'pattern' => '^/',
        'anonymous' => true,
        'remember_me' => [],
        'form' => [
            //'login_path' => '/login',
            'login_path' => '/#login',
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'check_path' => '/user/check',
        ],
        'oauth' => [
            'callback_path' => '/auth/{service}/callback',
            'anonymous'     => false,
            'failure_path'  => '/',
        ],
        'logout' => [
            'logout_path' => '/logout',
        ],
        'conferences' => $app->share(function($app) {
            return new AbstractUserManager($app);
        }),
    ],
);

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH', 'ROLE_SVM'),
    'ROLE_SVM' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/$','IS_AUTHENTICATED_ANONYMOUSLY'),
    array('^/user/check', 'IS_AUTHENTICATED_FULLY'),


    //array('^/user/login', 'IS_AUTHENTICATED_ANONYMOUSLY'),
    //array('^/user/register', 'IS_AUTHENTICATED_ANONYMOUSLY'),
    //array('^/api', 'IS_AUTHENTICATED_FULLY'),
    //array('^/api', 'IS_AUTHENTICATED_ANONYMOUSLY'),
);
*/

/*
$app['security.entry_point.form._proto'] = $app->protect(function () use ($app) {
    return $app->share(function () use ($app) {
        return new \Utils\SecurityOverrider($app['security.http_utils']);
    });
});
*/

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/logs/logs.log',
    'monolog.name' => $app['config']['name']
));


$app['cache.path'] = __DIR__ . '/cache';

// Doctrine (bdd)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => $app['config']['db']['host'],
    'port'     => $app['config']['db']['port'],
    'dbname'   => $app['config']['db']['dbname'],
    'user'     => $app['config']['db']['user'],
    'password' => $app['config']['db']['password'],
);

$app['orm.proxies_dir']     = $app['cache.path'] . '/doctrine/proxies';
$app['orm.default_cache']   = array(
    'driver'    => 'filesystem',
    'path'      => $app['cache.path'] . '/doctrine/cache',
);
$app['orm.em.options']      = array(
    'mappings' => array(
        array(
            'type' => 'annotation',
            'path' => __DIR__.'/../../src',
            'namespace' => 'Spitchee\\Entity',
        ),
    ),
);

$app['documentation'] = array();

foreach (['roles', 'timeline', 'actions', 'entities', 'changelog', 'backlog', 'rabbit'] as $doc) {
    $app['documentation'] = array_merge_recursive(
        $app['documentation'], \Symfony\Component\Yaml\Yaml::parse(file_get_contents(
            __DIR__ . "/../src/SpitcheeDocumentation/Resource/Documentation/$doc.yaml")
    ));
}

$timestampableListener = new TimestampableListener();
$app['db.event_manager']->addEventSubscriber($timestampableListener);

$softDeleteableListener = new Gedmo\SoftDeleteable\SoftDeleteableListener;
$app['db.event_manager']->addEventSubscriber($softDeleteableListener);


$app['db.config'] = new Doctrine\ORM\Configuration;
$app['db.config']->addFilter('softdeleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');


$app['twig'] = $app->share($app->extend('twig', function(Twig_Environment $twig, $app) {
    $twig->addGlobal('angularApp', $app['config']['name']);

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        $url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
        //if (true !== $app['debug']) {
        //    $url .= '/web';
        //}
        return sprintf("http://$url/%s", ltrim($asset, '/'));
    }));

    $twig->addFunction(new \Twig_SimpleFunction('dump', function ($var) {
        dump($var);
        //return true;
    }));

    $twig->addFunction(new \Twig_SimpleFunction('is_string', function ($var) {
        return is_string($var);
    }));

    $twig->addFunction(new \Twig_SimpleFunction('hasUcFirstEquals', function ($str, $ucFirst) {
        return $str[0] === $ucFirst;
    }));

    $twig->addFunction(new \Twig_SimpleFunction('array_has', function ($needle, $haystack) {
        return array_search($needle, $haystack) !== false;
    }));
    
    $twig->addFunction(new \Twig_SimpleFunction('buildAuthRouteResume', function ($authConfig) use ($app) {
        return \SpitcheeDocumentation\Helper\TwigHelper::getRouteAuthResume($app, $authConfig);
    }));

    $twig->addFunction(new \Twig_SimpleFunction('buildAuthRouteDescription', function ($authConfig) use ($app) {
        return \SpitcheeDocumentation\Helper\TwigHelper::getRouteAuthDescription($app, $authConfig);
    }));

    return $twig;
}));


return $app;