<?php

namespace Spitchee\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class SipAccount
 * @package Spitchee\Entity
 * @Entity(repositoryClass="Spitchee\Entity\Repository\SipAccountRepository")
 * @Table(name="SipAccount")
 */
class SipAccount
{
    const SIP_STATUS_OFFLINE    = 0;
    const SIP_STATUS_ONLINE     = 1;
    const SIP_STATUS_ON_CALL    = 3; // Dans un appel, après l'event ConfbridgeJoin

    //const SIP_STATUS_ON_CALL    = 3; // Dans un appel, avant l'event ConfbridgeJoin
    //const SIP_STATUS_FULLY_ON_CALL = 7; // Dans un appel, après l'event ConfbridgeJoin

    /**
     * @Id
     * @Column(name="id", type="integer", unique=true)
     */
    private $id;

    /**
     * @Column(name="status", type="integer")
     */
    private $status;

    /**
     * @Column(name="secret", type="string", length=100)
     */
    private $secret;

    /**
     * @Column(name="active_channel", type="string", length=100, nullable=true)
     */
    private $activeChannel;

    /**
     * @OneToOne(targetEntity="User", inversedBy="sipAccount")
     * @JoinColumn(name="user_uuid", referencedColumnName="uuid")
     */
    protected $user;

    /**
     * @OneToMany(targetEntity="NamiEvent", mappedBy="relatedSipAccount")
     */
    protected $relatedEvents;
    
    
    public function toArray() {
        return [
            'id' => $this->getId(),
            'secret' => $this->getSecret(),
            'status' => $this->getStatus(),
        ];
    }
    
    public function __construct(User $user, $sipId, $sipSecret) {
        $this
            ->setStatus(self::SIP_STATUS_OFFLINE)
            ->setId($sipId)
            ->setSecret($sipSecret)
            ->setUser($user);
    }

    public function isOnline()
    {
        return self::SIP_STATUS_ONLINE & $this->getStatus();
    }

    public function isOnCall()
    {
        return self::SIP_STATUS_ON_CALL === $this->getStatus();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param mixed $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $user->setSipAccount($this);
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelatedEvents()
    {
        return $this->relatedEvents;
    }

    /**
     * @param mixed $relatedEvents
     * @return $this
     */
    public function setRelatedEvents($relatedEvents)
    {
        $this->relatedEvents = $relatedEvents;
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActiveChannel()
    {
        return $this->activeChannel;
    }

    /**
     * @param mixed $activeChannel
     * @return $this
     */
    public function setActiveChannel($activeChannel)
    {
        $this->activeChannel = $activeChannel;
        
        return $this;
    }
    
}