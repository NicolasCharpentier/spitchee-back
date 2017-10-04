<?php

namespace Spitchee\Service\Entity\Repository;

use Spitchee\Entity\Repository\ConferenceRepository;
use Spitchee\Entity\Repository\NamiEventRepository;
use Spitchee\Entity\Repository\SipAccountRepository;
use Spitchee\Entity\Repository\UserRepository;
use Spitchee\Service\Generic\ContainerAwareService;

class RepositoryService extends ContainerAwareService
{
    const SPITCHEE_ENTITY_NAMESPACE = 'Spitchee\\Entity';

    /**
     * @param $entity
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getSpitcheeRepository($entity)
    {
        return $this->getContainer()->getEntityManager()->getRepository(
            self::SPITCHEE_ENTITY_NAMESPACE . '\\' . $entity
        );
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository()
    {
        return $this->getSpitcheeRepository('User');
    }

    /**
     * @return SipAccountRepository
     */
    public function getSipAccountRepository()
    {
        return $this->getSpitcheeRepository('SipAccount');
    }

    /**
     * @return NamiEventRepository
     */
    public function getNamiEventRepository()
    {
        return $this->getSpitcheeRepository('NamiEvent');
    }

    /**
     * @return ConferenceRepository
     */
    public function getConferenceRepository()
    {
        return $this->getSpitcheeRepository('Conference');
    }
}