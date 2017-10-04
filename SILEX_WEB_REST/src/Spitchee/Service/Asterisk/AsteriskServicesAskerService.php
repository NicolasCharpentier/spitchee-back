<?php

namespace Spitchee\Service\Asterisk;


use Monolog\Logger;
use Spitchee\Entity\Conference;
use Spitchee\Entity\SipAccount;
use Spitchee\Service\Asterisk\Event\AsteriskEventConsequencesService;
use Spitchee\Util\HttpClient\NamiClient;
use Spitchee\Util\HttpClient\SuClient;
use Container;
use Spitchee\Util\Operation\OperationFailure;
use Spitchee\Util\Operation\OperationResult;
use Spitchee\Util\Operation\OperationSuccess;

class AsteriskServicesAskerService
{
    /** @var Logger $logger */
    private $logger;

    /** @var string $namiUrl */
    private $namiUrl;

    /** @var string $suUrl */
    private $suUrl;

    /** @var AsteriskEventConsequencesService $eventConsequencesService */
    private $eventConsequencesService;

    private $loggingEnabled;

    const TYPE_SU   = 'SU';
    const TYPE_NAMI = 'NAMI';

    public function __construct(Container $app)
    {
        $this->namiUrl  = $app['config']['services']['nami'];
        $this->suUrl    = $app['config']['services']['su'];
        $this->logger   = $app['monolog'];

        $this->eventConsequencesService = $app->getAsteriskEventConsequencesService();
        $this->loggingEnabled           = false;
    }

    /** @return OperationResult */
    private function analyzeAndReturnResponse($action, $type, $response)
    {
        $success   = $response === false ? false : $response->ok;
        $response  = json_decode(json_encode($response), true);
        $resume    = "$type::$action --> [" . ($success ? 'OK' : 'KO') . ']';

        if (! $success and $this->loggingEnabled) {
            $this->logger->addRecord(Logger::ERROR, "$resume", $response);
        }

        $this->eventConsequencesService->addConsequence($resume);

        return $success
            ? OperationSuccess::create()
            : OperationFailure::fromServer("$resume");
    }
    
    public function registerToConference(Conference $conference, SipAccount $user) {
        return $this->analyzeAndReturnResponse('registerToConference', self::TYPE_SU, $this
            ->getSuClient()
            ->registerToConference($conference->getUuid(), $user->getId(), $user->getSecret())
            ->getResponse()
        );
    }
    
    public function originate(Conference $conference, SipAccount $sipAccount) {
        return $this->analyzeAndReturnResponse('originate', self::TYPE_NAMI, $this
            ->getNamiClient()
            ->originate($conference->getUuid(), $sipAccount->getId())
            ->getResponse()
        );
    }
    
    public function kickFromConference(Conference $conference, $channel) {
        return $this->analyzeAndReturnResponse('kickFromConference', self::TYPE_NAMI, $this
            ->getNamiClient()
            ->kickFromConference($conference->getUuid(), $channel)
            ->getResponse()
        );
    }
    
    public function sipReload() {
        return $this->analyzeAndReturnResponse('sipReload', self::TYPE_NAMI, $this
            ->getNamiClient()
            ->sipReload()
            ->getResponse()
        );
    }

    public function testSipClient() {
        return $this->analyzeAndReturnResponse('testSipClient', self::TYPE_SU, $this
            ->getSuClient()
            ->testConnection()
            ->getResponse()
        );
    }
    
    private function getNamiClient() {
        return new NamiClient($this->namiUrl, 3);
    }
    
    private function getSuClient() {
        return new SuClient($this->suUrl, 3);
    }

    public function enableLogging() {
        $this->loggingEnabled = true;
        return $this;
    }
}