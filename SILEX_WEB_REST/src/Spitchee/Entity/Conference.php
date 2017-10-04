<?php

namespace Spitchee\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;
use Spitchee\Util\Type\ArrayUtil;

/**
 * @Entity(repositoryClass="Spitchee\Entity\Repository\ConferenceRepository")
 * @Table(name="Conference")
 */
class Conference
{
    const STATE_INITIALIZED = 0; // Au tout début quand elle est créée mais rien en SIP
    
    const STATE_WAITING_FOR_SPEAKER = 16; // Quand le speaker se register mais a pas rep
    
    const STATE_ACTIVE = 2; // À partir du moment ou speaker est dedans
    //const STATE_ACTIVE_SPEAKER_ONLY = 2;
    //const STATE_ACTIVE_SPEAKER_NOT_ALONE = 3;
    
    const STATE_INACTIVE = 4; // Quand y'a plus personne (veut pas dire forcement que c finit)

    static $strStates = [
        self::STATE_INITIALIZED                 => 'INITIALIZED',
        self::STATE_WAITING_FOR_SPEAKER         => 'WAITING_FOR_SPEAKER',
        self::STATE_ACTIVE                      => 'ACTIVE',
        //self::STATE_ACTIVE_SPEAKER_NOT_ALONE    => 'ACTIVE_1',
        //self::STATE_ACTIVE_SPEAKER_ONLY         => 'ACTIVE_1+',
        self::STATE_INACTIVE                    => 'INACTIVE',
    ];

    //const STATE_ON_PAUSE_CAUSE_OF_SPEAKER = 3;

	/**
	 * @Id
	 * @Column(name="uuid", type="string", length=100, unique=true)
	 */
	protected $uuid;

    /**
     * @Column(name="state", type="smallint")
     */
    protected $state;

	/**
	 * @OneToMany(targetEntity="User", mappedBy="activeConference") 
	 */
	protected $activeUsers;

    /**
     * @OneToMany(targetEntity="NamiEvent", mappedBy="relatedConference")
     */
    protected $relatedEvents;

    /**
     * Constructor
     * @param User $confMaster
     * @param User $speaker
     */
    public function __construct($uuid, User $confMaster, User $speaker)
    {
        $this->activeUsers = new ArrayCollection();
        $this->setUuid($uuid);
        $this->addActiveUser($confMaster);
        $this->addActiveUser($speaker);
        $this->state = self::STATE_INITIALIZED;
    }

    /**
     * @return User|null
     */
    public function getSpeaker()
    {
        $speakers = $this->getActiveUsers()->filter(function (User $user) {
            return User::ROLE_HP === $user->getActiveRole();
        });

        return $speakers->isEmpty() ? null : $speakers->first();
    }

    /**
     * !! Attention après ceci si on ->toArray, l'ordre ne sera pas celui d'un array natif malgré ce que la doc dit
     *
     * @param null $role
     * @return \Doctrine\Common\Collections\Collection|User[]
     */
    public function getOncallUsers($role = null)
    {
        $users = $this->getActiveUsers()->filter(function (User $user) use ($role) {
            return $user->isSipOnCall() and (null === $role or $role === $user->getActiveRole());
        });

        return $users;
    }

    public function countOnCallAgoraUsers()
    {
        return $this->getOncallUsers(User::ROLE_PUBLIC)->count();
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Conference
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
     * Add activeUser
     *
     * @param User $activeUser
     *
     * @return Conference
     */
    public function addActiveUser(User $activeUser)
    {
        if (User::ROLE_HP === $activeUser->getActiveRole() and null !== $this->getSpeaker()) {
            throw new \LogicException("Une conférence ne peut actuellemetn aps avoir plusieurs speakers");
        }

        $this->activeUsers[] = $activeUser;
        $activeUser->setActiveConference($this);

        return $this;
    }

    /**
     * Remove activeUser
     *
     * @param User $activeUser
     */
    public function removeActiveUser(User $activeUser)
    {
        $this->activeUsers->removeElement($activeUser);
    }

    /**
     * Get activeUsers
     *
     * @return \Doctrine\Common\Collections\Collection|User[]
     */
    public function getActiveUsers()
    {
        return $this->activeUsers;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
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
}
