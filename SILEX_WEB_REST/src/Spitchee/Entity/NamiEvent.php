<?php

namespace Spitchee\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class NamiEvent
 * @package Spitchee\Entity
 * @Entity(repositoryClass="Spitchee\Entity\Repository\NamiEventRepository")
 * @Table(name="NamiEvent")
 */
class NamiEvent
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(name="id", type="integer", unique=true)
     */
    private $id;

    /**
     * @Column(name="type", type="string", length=100)
     */
    private $type;

    /**
     * @Column(name="information", type="string", length=1000)
     */
    private $information;

    /**
     * @Column(name="created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;
    
    /**
     * @ManyToOne(targetEntity="SipAccount", inversedBy="relatedEvents")
     * @JoinColumn(name="related_sip_account_id", referencedColumnName="id", nullable=true)
     */
    protected $relatedSipAccount;

    /**
     * @ManyToOne(targetEntity="Conference", inversedBy="relatedEvents")
     * @JoinColumn(name="related_conference_id", referencedColumnName="uuid", nullable=true)
     */
    protected $relatedConference;
    

    public function __construct($type, $information, SipAccount $sipAccount = null)
    {
        $this
            ->setType($type)
            ->setInformation($information)
            ->setRelatedSipAccount($sipAccount);
    }

    public function toArray()
    {
        $sipAccount = $this->getRelatedSipAccount();
        $conference = $this->getRelatedConference();

        return [
            'id' => $this->getId(),
            'sipId' => $sipAccount ? $sipAccount->getId() : null,
            'userId' => $sipAccount ? $sipAccount->getUser()->getUuid() : null,
            'conferenceId' => $conference ? $conference->getUuid() : null,
            'created' => $this->getCreated()->format('d/m/Y H:i:s'),
            'type' => $this->getType(),
            'informations' => $this->getInformation(false),
        ];
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param bool $brut
     * @return mixed
     */
    public function getInformation($brut = false)
    {
        return $brut ? $this->information : json_decode($this->information);
    }

    /**
     * @param mixed $information
     * @return $this
     */
    public function setInformation($information)
    {
        if (is_string($information)) {
            $this->information = $information;   
        } else {
            $this->information = json_encode($information);
        }

        return $this;
    }

    /**
     * @return SipAccount
     */
    public function getRelatedSipAccount()
    {
        return $this->relatedSipAccount;
    }

    /**
     * @param mixed $relatedSipAccount
     * @return $this
     */
    public function setRelatedSipAccount($relatedSipAccount)
    {
        $this->relatedSipAccount = $relatedSipAccount;
        
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
        
        return $this;
    }

    /**
     * @return Conference
     */
    public function getRelatedConference()
    {
        return $this->relatedConference;
    }

    /**
     * @param mixed $relatedConference
     * @return $this
     */
    public function setRelatedConference($relatedConference)
    {
        $this->relatedConference = $relatedConference;
        
        return $this;
    }
    
}