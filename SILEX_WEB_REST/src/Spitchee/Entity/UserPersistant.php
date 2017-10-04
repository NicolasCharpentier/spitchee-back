<?php

namespace Spitchee\Entity;

use Doctrine\ORM\Mapping\Entity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * @Entity()
 */
class UserPersistant extends User
{
    public function __construct($username, $password, MessageDigestPasswordEncoder $encoder, $activeRole = null) {
        parent::__construct();
        
        $this
            ->setUuid(Uuid::uuid4())
            ->setUsername($username)
            ->setPassword($encoder->encodePassword($password, null))
            ->setActiveRole($activeRole);
    }
    
    public function validatePassword($password, MessageDigestPasswordEncoder $encoder) {
        return $encoder->encodePassword($password, null) === $this->getPassword();
    }
}