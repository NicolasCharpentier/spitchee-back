<?php

/** @var Container $app */
$app = require __DIR__ . '/../app/app.php';

$app->boot();

//$app->getSpitcheeService('test')->test();

$app->after(function () use ($app) {
    $app->getAsteriskEventConsequencesService()->log();
});

$app->get('/', function() use ($app) {
    $var = 'ok';
    return new \Symfony\Component\HttpFoundation\Response(`echo $var`);
});

$app->get($app['config']['asterisk']['logsRoute'], function() use ($app) {
			    $logs = `tail -1000 /var/log/asterisk/messages`;
			    $logs = explode(PHP_EOL, $logs);
			    $logs = array_reverse($logs);
			    $logs = implode('<br/>', $logs);
	       return new \Symfony\Component\HttpFoundation\Response($logs);
});

$app->get('/test/rabbit', function () use ($app) {
    $conference = $app->getRepositoryService()->getConferenceRepository()->find(1);

    if (! $conference) {
        return $app->json([], 404);
    }

    $app->getRabbitPublisherService()->publishConferenceState($conference);

    return $app->json();
});

$app->get('/ban/{mdp}/{ip}', function ($mdp, $ip) use ($app) {
     if (0 == $app['config']['iptables']['on']) {
         return new \Symfony\Component\HttpFoundation\Response('ko', 400);
     }

    if ($mdp != $app['config']['iptables']['secret']) {
        return new \Symfony\Component\HttpFoundation\Response('ko', 403);
    }

    //$output = shell_exec("iptables -I INPUT -s $ip -j DROP");

    $app->getLogger()->addInfo("Ipban $ip, output : " . `ip_ban $ip`);

    return new \Symfony\Component\HttpFoundation\Response('ok');
});

$app->get('/onBoot/test/baljan', function () use ($app) {
    $conferencier   = new Spitchee\Entity\UserTemp(\Spitchee\Entity\UserTemp::ROLE_CONFERENCIER, 1);
    $hautparleur    = new \Spitchee\Entity\UserTemp(\Spitchee\Entity\UserTemp::ROLE_HP, 2);
    $agora          = new \Spitchee\Entity\UserTemp(\Spitchee\Entity\UserTemp::ROLE_PUBLIC, 3);
    $sipAccount     = new \Spitchee\Entity\SipAccount($hautparleur, 999, 999);
    $conference     = new \Spitchee\Entity\Conference(1, $conferencier, $hautparleur);

    $conference->addActiveUser($agora);
    $agora->registerWannaTalk();

    $app->getEntityManager()->persist($conference);
    $app->getEntityManager()->persist($sipAccount);
    $app->getEntityManager()->persist($hautparleur);
    $app->getEntityManager()->persist($conferencier);
    $app->getEntityManager()->persist($agora);
    $app->getEntityManager()->flush();

    $a = $app->getAsteriskServicesAskerService()->registerToConference($conference, $sipAccount)->isSuccessfull();
    $b = $app->getAsteriskServicesAskerService()->sipReload()->isSuccessfull();

    $result = $app->getRepositoryService()->getUserRepository()->findWannaTalkUsersInConference($conference);
    //$c = $app->getAsteriskServicesAskerService()->originate($conference, $sipAccount);

    // Ca devrait appeler tout seul une fois que speaker est sip register

    return new \Symfony\Component\HttpFoundation\Response(join('<br/>', [
        "Conf add $a", "Sip reload $b",// "Originate $c"
        'Nb wanna talk (1) --> ' . count($result)
    ]));
});


$app->get('/onBoot/test/connection', function () use ($app) {
    $sipReload  = $app->getAsteriskServicesAskerService()->sipReload()->isSuccessfull();
    $testSu     = $app->getAsteriskServicesAskerService()->testSipClient()->isSuccessfull();
    try {
        $app->getRabbitPublisherService()->publishBullshit('oklm');
        $testRab = true;
    } catch (Exception $e) {
        $testRab = false;
    }


    $str = function ($boule) {
        return ' [' . ($boule ? 'OK' : 'KO') . ']';
    };
    $validation = [
        'NAMI + Asterisk' . $str($sipReload),
        'SU' . $str($testSu),
        'RABBIT ' . $str($testRab)
    ];

    return new \Symfony\Component\HttpFoundation\Response(join('<br/>', $validation));
});

$app->error(function (\Exception $e, $code) use ($app) {
    if (500 <= $code) {
        if ($app['debug']) {
            return;
        }

        $errorMessage = 'Oops, ' . $e->getMessage()
            . PHP_EOL . $e->getFile() . ' : ' . $e->getLine()
            . PHP_EOL . $e->getTraceAsString();


        $app->getLogger()->addError($errorMessage);

        file_put_contents(
            __DIR__ . '/../app/logs/500.log',
            (new \DateTime())->format('[d/m/Y H:i] ') . $errorMessage . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );

        return new \Symfony\Component\HttpFoundation\Response(
            'Une erreur interne s\'est produite, contacte les autorités les plus proches',
            $code
        );
    }

    if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
        return new \Symfony\Component\HttpFoundation\Response(
            $e->getMessage(), $e->getStatusCode()
        );
    }

    if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        return new \Symfony\Component\HttpFoundation\Response(
            $e->getMessage(), 404
        );
    }
});

$app->run();