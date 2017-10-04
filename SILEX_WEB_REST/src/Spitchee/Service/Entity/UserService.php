<?php

namespace Spitchee\Service\Entity;

use Ramsey\Uuid\Uuid;
use Spitchee\Entity\User;
use Spitchee\Entity\UserTemp;
use Spitchee\Service\Generic\BaseEntityService;

class UserService extends BaseEntityService
{
    public function createTempUser($role, $nickname = null, $withSipAccount = false, $save = true)
    {
        $user = new UserTemp($role, $this->createUuid($role), $nickname);
        //if (in_array($role, self::getSipRoles())) {
        //    $sipAccount = $this->getContainer()->getSipAccountService()->createSipAccount($user);
        //    if ($save) {
        //        $this->persist($sipAccount);
        //    }
        //}
        if ($withSipAccount) {
            $this->getContainer()->getSipAccountService()->createSipAccount($user, false);
        }
        
        if ($save) {
            $this->persist($user);
            $this->persist($user->getSipAccount());
            $this->flush();
        }
        
        return $user; 
    }

    public function registerWannaTalk(User $user)
    {
        if (true === $user->wannaTalk() or null === $user->getActiveConference()) {
            return false;
        }

        $user->registerWannaTalk();
        $this->persist($user);
        $this->flush();

        $this->getContainer()->getRabbitPublisherService()->publishAsks(
            $user->getActiveConference(),
            $this->getContainer()->getRepositoryService()->getUserRepository()
        );

        return true;
    }

    static public function getAvailableRoles() {
        return [
            User::ROLE_CONFERENCIER,
            User::ROLE_PUBLIC,
            User::ROLE_HP
        ];
    }
    
    static private function getSelfRegistrableRoles() {
        return [
            User::ROLE_CONFERENCIER,
            User::ROLE_PUBLIC
        ];
    }

    static public function getSipRoles() {
        return [
            User::ROLE_PUBLIC,
            User::ROLE_HP
        ];
    }
    
    static private function getShortedRoles() {
        return [
            User::ROLE_HP
        ];
    }
    
    static public function isValidRole($role) {
        return $role !== null and in_array($role, self::getAvailableRoles());
    }
    
    static public function isSelfRegistrableRole($role) {
        return self::isValidRole($role) and in_array($role, self::getSelfRegistrableRoles());
    }
    
    static public function isSipRole($role) {
        return self::isValidRole($role) and in_array($role, self::getSipRoles());
    }
    
    private function createUuid($role) {
        if (! in_array($role, self::getShortedRoles())) {
            return Uuid::uuid4();
        }

        $id = substr(Uuid::uuid4(), 0, 8);

        while (null !== $this->getContainer()->getRepositoryService()->getUserRepository()->findOneBy([
                'uuid' => $id
            ])) $id = substr(Uuid::uuid4(), 0, 8);

        return $id;
    }
    
    
}