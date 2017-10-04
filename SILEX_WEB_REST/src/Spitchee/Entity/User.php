<?php

namespace Spitchee\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


/**
 * @Entity(repositoryClass="Spitchee\Entity\Repository\UserRepository")
 * @Table(name="User")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"user" = "User", "temp_user" = "UserTemp", "persistant_user" = "UserPersistant"})
 */
abstract class User
{
    const ROLE_CONFERENCIER = 'lecturer';
    const ROLE_PUBLIC = 'agora';
    const ROLE_HP = 'speaker';
    
	/**
	 * @Id
	 * @Column(name="uuid", type="string", length=100, unique=true)
	 */
	protected $uuid;
	
	/**
	 * @Column(name="username", type="string", length=100, nullable=true, unique=true)
	 */
	protected $username;

    /**
     * @Column(name="nickname", type="string", length=100, nullable=true, unique=false)
     */
    protected $nickname;

	/**
	 * @Column(name="password", type="string", length=100)
	 */
	protected $password;

    /**
     * @Column(name="active_role", type="string", length=20, nullable=true)
     */
    protected $activeRole;

    /**
     * @Column(name="wanna_talk_since", type="datetime", nullable=true)
     */
    protected $wannaTalkSince;

    /**
     * @OneToOne(targetEntity="SipAccount", mappedBy="user", cascade={"persist"})
     */
    protected $sipAccount;

	/**
	 * @ManyToOne(targetEntity="Conference", inversedBy="activeUsers")
	 * @JoinColumn(referencedColumnName="uuid", name="active_conference_uuid")
	 */
	protected $activeConference;

    /*
    public function __construct($sipId, $sipSecret, UserPasswordEncoder $encoder) {
        $this->setUuid(Uuid::uuid4());
        $this->setPassword($encoder->encodePassword(Uuid::uuid4(), null));
    }
    public function getDescription() {
        return $this->getUsername() ? : $this->getTempUsername();
    }
    */

    abstract public function validatePassword($password, MessageDigestPasswordEncoder $encoder);
    
    public function toArray() {
        return [
            'id' => $this->getUuid(),
            'role' => $this->getActiveRole(),
            'username' => $this->getUsername(),
            'conferenceId' => $this->getActiveConferenceId(),
            'wannaTalkSince' => $this->getWannaTalkSinceStr(),
            'sip' => $this->getSipAccount() ? $this->getSipAccount()->toArray() : []
        ];
    }

    public function __construct()
    {
        //$this->setSipStatus(self::SIP_STATUS_OFFLINE);
    }

    public function __toString()
    {
        return $this->getUuid();
    }

    public function isSipOnline()
    {
        return $this->getSipAccount() and $this->getSipAccount()->isOnline();
    }

    public function isSipOnCall()
    {
        return $this->getSipAccount() and $this->getSipAccount()->isOnCall();
    }

    public function getWannaTalkSinceStr()
    {
        return $this->getWannaTalkSince() ? $this->getWannaTalkSince()->format('d/m/Y H:i:s') : '';
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return User
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set activeConference
     *
     * @param Conference $activeConference
     *
     * @return User
     */
    public function setActiveConference(Conference $activeConference = null)
    {
        $this->activeConference = $activeConference;

        return $this;
    }

    /**
     * Get activeConference
     *
     * @return Conference
     */
    public function getActiveConference()
    {
        return $this->activeConference;
    }

    public function getActiveConferenceId() {
        return $this->getActiveConference() ? $this->getActiveConference()->getUuid() : null;
    }

    /**
     * @return mixed
     */
    public function getActiveRole()
    {
        return $this->activeRole;
    }

    /**
     * @param mixed $activeRole
     * 
     * @return User
     */
    public function setActiveRole($activeRole)
    {
        $this->activeRole = $activeRole;
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param mixed $nickname
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }

    /**
     * @return SipAccount
     */
    public function getSipAccount()
    {
        return $this->sipAccount;
    }

    /**
     * @param mixed $sipAccount
     * @return $this
     */
    public function setSipAccount($sipAccount)
    {
        $this->sipAccount = $sipAccount;
        
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getWannaTalkSince()
    {
        return $this->wannaTalkSince;
    }

    public function wannaTalk()
    {
        return $this->getWannaTalkSince() instanceof \DateTime;
    }

    public function registerWannaTalk()
    {
        $this->setWannaTalkSince(new \DateTime());
    }

    /**
     * @param \DateTime $wannaTalkSince
     * @return User
     */
    public function setWannaTalkSince($wannaTalkSince)
    {
        $this->wannaTalkSince = $wannaTalkSince;

        return $this;
    }

}
