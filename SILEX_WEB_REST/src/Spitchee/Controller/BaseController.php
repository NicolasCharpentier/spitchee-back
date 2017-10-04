<?php

namespace Spitchee\Controller;

use Container;
use Spitchee\Entity\User;
use Spitchee\Service\Generic\ContainerAwareService;
use Spitchee\Util\Auth\SpitcheeAuthManager;
use Spitchee\Util\Operation\OperationFailure;
use Spitchee\Util\Operation\OperationResult;
use Spitchee\Util\Operation\OperationSuccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class BaseController extends ContainerAwareService
{
    private $app;
    private $user;
    
    public function __construct(Container $app)
    {
        parent::__construct($app);
        $this->app  = $app;
        $this->user = SpitcheeAuthManager::findAuthUser(
            $this->getRequest(), $app->getRepositoryService()->getUserRepository(),
            $app['security.encoder.digest']
        );
    }

    /** @return Request */
    protected function getRequest()
    {
        return $this->app['request'];
    }

    /** @return User|null */
    protected function getUser() 
    {
        return $this->user;
    }
    
    protected function getUserService()
    {
        return $this->getContainer()->getUserService();
    }
    
    protected function getNamiEventService()
    {
        return $this->getContainer()->getNamiEventService();
    }
    
    protected function getSipAccountService()
    {
        return $this->getContainer()->getSipAccountService();
    }
    
    protected function getConferenceService()
    {
        return $this->getContainer()->getConferenceService();
    }
    
    protected function getRepositoryService()
    {
        return $this->getContainer()->getRepositoryService();
    }
    
    protected function authRestrict($roles = []) 
    {
        if (null === $this->getUser())
            throw new AccessDeniedHttpException('Basic auth toi stp', null, 401);
        
        if (0 == count($roles))
            return;
        
        if (! is_array($roles))
            $roles = [$roles];
        
        foreach ($roles as $role)
            if ($role === $this->getUser()->getActiveRole())
                return;
        
        throw new AccessDeniedHttpException(
            'Tu es ' . $this->getUser()->getActiveRole() . ', il faut être ' . 
            join(' ou ', $roles) . ' pour survivre dans cette route. dsl.',
            null, 403
        );
    }
    
    protected function ipRestrict($ips = [])
    {
        if (! is_array($ips)) {
            $ips = [$ips];
        }
        
        if (! in_array($_SERVER['REMOTE_ADDR'], $ips)) {
            throw new AccessDeniedHttpException("Ip non autorisée", null, 401);
        }
    }
    
    protected function json($data = [], $code = 200, $headers = [])
    {
        if (! is_array($data)) {
            $data = [$data];
        }
        
        return $this->app->json($data, $code, $headers);
    }
    
    protected function status($code = 200) 
    {
        return new Response('', $code);
    }
    
    protected function error($message, $code)
    {
        if (is_array($message)) {
            $message = join(PHP_EOL, $message);
        }
        
        return new Response($message, $code);
    }

    protected function operationResult(OperationResult $op)
    {
        if ($op->isSuccessfull())
        {
            return $this->status(200);
        }

        if ($op instanceof OperationFailure)
        {
            if ($op->getDetails()) {
                $this->getContainer()->getLogger()->addInfo($op->getDetails());
            }

            return $this->error(
                $op->getDetails(),
                OperationFailure::REASON_TYPE_CLIENT === $op->getReason() ? 400 : 500
            );
        }

        throw new \LogicException('L\'opération résultat est très étrange');
    }
    
    
    // --- Parsing de la request

    protected function getQueryArg($arg, $defaultValue = null) {
        return $this->getRequest()->query->get($arg, $defaultValue);
    }

    /*
    protected function getQueryDateArg($arg, $wantedFormat = 'Y/m/d ', $givenFormats = ['d/m/Y'], $defaultValue = null) {
        $strDate = $this->getQueryArg($arg, $defaultValue);

        if ($defaultValue === $strDate) {
            return $defaultValue;
        }

        if (! is_array($givenFormats)) {
            $givenFormats = [$givenFormats];
        }

        $date = null;
        foreach ($givenFormats as $givenFormat) {
            $date = \DateTime::createFromFormat($givenFormat, $strDate);
            if ($givenFormat instanceof \DateTime) {
                break;
            }
        }

        if (null === $date) {
            return $defaultValue;
        }

        return $date->format($wantedFormat);
    }
    */

    protected function parseQueryArgs($wanted = array(), $nullable = true) {
        return $this->buildFromArgs($wanted, $this->getRequest()->query->all(), $nullable);
    }
    
    protected function getPostArg($arg, $defaultValue = null) {
        $args = $this->parsePostArgs($arg);

        return  $args[$arg] === null ? $defaultValue : $args[$arg];
    }

    protected function parsePostArgs($wanted = array(), $nullable = true) {
        // On accepte en form-data et en cash json dans le body

        $args = json_decode($this->getRequest()->getContent(), true);
        $args = array_merge($this->getRequest()->request->all(), $args === null ? [] : $args);

        return $this->buildFromArgs($wanted, $args, $nullable);
    }

    // potentiel probleme si nullable === false et args[key] === null
    private function buildFromArgs($wanted, $args, $nullable) {
        $response = array();

        if (! is_array($wanted)) {
            $wanted = [$wanted];
        }

        if (! count($wanted)) {
            foreach ($args as $key => $arg) {
                $response[$key] = $arg;
            }
        }

        foreach ($wanted as $field) {
            if (isset($args[$field])) {
                $response[$field] = $args[$field];
            } elseif ($nullable) {
                $response[$field] = null;
            }
        }

        return $response;
    }
}