<?php

use Doctrine\ORM\EntityManager;
use Silex\Application;
use Spitchee\Service\Asterisk\AsteriskServicesAskerService;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Container extends Application {
    use Application\SecurityTrait;
    use Application\UrlGeneratorTrait;
    use Application\TwigTrait;
    use EntityManagerTrait;
    use SpitcheeServiceLocatorTrait;
    use BetterMonologTrait;
}


trait EntityManagerTrait
{
    /**
     * @return EntityManager
     */
    public function getEntityManager() {
        return $this['orm.em'];
    }
}

trait BetterMonologTrait
{
    /**
     * @return Monolog\Logger
     */
    public function getLogger() {
        return $this['monolog'];
    }
}

trait SpitcheeServiceLocatorTrait
{
    public function getSpitcheeService($serviceId)
    {
        return $this["spitchee.services.$serviceId"];
    }

    /**
     * @return AsteriskServicesAskerService
     */
    public function getAsteriskServicesAskerService() 
    {
        return $this->getSpitcheeService('AsteriskServicesAsker');
    }

    /**
     * @return \Spitchee\Service\Entity\Repository\RepositoryService
     */
    public function getRepositoryService() 
    {
        return $this->getSpitcheeService('Repository');
    }

    /**
     * @return \Spitchee\Service\Entity\ConferenceService
     */
    public function getConferenceService() 
    {
        return $this->getSpitcheeService('Conference');
    }

    /**
     * @return \Spitchee\Service\Entity\UserService
     */
    public function getUserService()
    {
        return $this->getSpitcheeService('User');
    }

    /**
     * @return \Spitchee\Service\Entity\SipAccountService
     */
    public function getSipAccountService() 
    {
        return $this->getSpitcheeService('SipAccount');
    }

    /**
     * @return \Spitchee\Service\Entity\NamiEventService
     */
    public function getNamiEventService() 
    {
        return $this->getSpitcheeService('NamiEvent');
    }

    /**
     * @return \Spitchee\Service\Rabbit\RabbitPublisherService
     */
    public function getRabbitPublisherService()
    {
        return $this->getSpitcheeService('RabbitPublisher');
    }

    /**
     * @return \Spitchee\Service\Asterisk\Event\AsteriskEventConsequencesService
     */
    public function getAsteriskEventConsequencesService()
    {
        return $this->getSpitcheeService('AsteriskEventConsequences');
    }
}