<?php

namespace Spitchee\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * @Entity()
 */
class UserTemp extends User
{
    public function __construct(/*$sipId, $sipSecret,*/ $activeRole, $uuid = null, $nickname = null) {
        if ($activeRole === null) {
            throw new InvalidArgumentException('activeRole ne peut être null pour un user temporaire');
        }
        
        parent::__construct();
        
        $this
            ->setUuid($uuid ?: Uuid::uuid4())
            ->setPassword(Uuid::uuid4())
            ->setNickname($nickname)
            //->setSipId($sipId)
            //->setSipSecret($sipSecret)
            ->setActiveRole($activeRole);
    }
    
    public function validatePassword($password, MessageDigestPasswordEncoder $encoder) {
        return $password === $this->getPassword();
    }
    
    public function getUsername()
    {
        return $this->getNickname();
    }

    public function toArray() {
        return array_merge(parent::toArray(), [
            'password' => $this->getPassword()
        ]);
    }
}