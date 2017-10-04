<?php

namespace Spitchee\Service\Generic;

use Container;

abstract class ContainerAwareService
{
    private $container;

    public function __construct(Container $app)
    {
        $this->container = $app;
    }
    
    protected function getContainer()
    {
        return $this->container;
    }
    
    /*
    protected function getEntityManager()
    {
        return $this->app->getEntityManager();
    }
    
    protected function getService($serviceId)
    {
        return $this->app->getSpitcheeService($serviceId);
    }
    
    protected function getSipAccountService()
    {
        return $this->app->getSipAccountService();
    }
    
    protected function getConferenceService()
    {
        return $this->app->getConferenceService();
    }
    
    protected function getAsteriskServicesAskerService()
    {
        return $this->app->getAsteriskServicesAskerService();
    }
    
    protected function getNamiService()
    {
        return $this->app->getNamiEventService();
    }
    
    protected function getUserService()
    {
        return $this->app->getUserService();
    }
    
    protected function log($message, $context = array(), $level = Logger::INFO)
    {
        return $this->app->log($message, $context, $level);
    }
    */
}