<?php

namespace Spitchee\Service\Provider;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Container;

class SpitcheeServicesProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app
     * @return $this
     */
    public function register(Application $app)
    {
        return $this;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     * 
     * @param Application $app
     */
    public function boot(Application $app)
    {
        if (! $app instanceof Container) {
            throw new \InvalidArgumentException("L'app passé n'est pas celle configurée dans le projet");
        }
        
        if (! isset($app['spitchee.services.configuration'])) {
            throw new \InvalidArgumentException("Manque la conf des services Spitchee");
        }
        
        $baseNameSpace = $app['spitchee.services.configuration']['baseNameSpace'];
        
        foreach ($app['spitchee.services.configuration']['services'] as $nameSpace => $services) {
            if (! is_array($services)) {
                $services = [$services];
            }
            
            $nameSpace = $baseNameSpace . '\\' . $nameSpace;
            
            foreach ($services as $serviceName) {
                //$serviceName        = str_replace('Service', '', $serviceName);
                //$serviceName        = ucfirst($serviceName);
                $serviceFullName    = $nameSpace . '\\' . $serviceName . 'Service';
                
                $app["spitchee.services.$serviceName"] = $app->share(
                    function (Container $app) use ($serviceFullName) {
                        return new $serviceFullName($app);
                    }  
                );
            }
        }
    }
}