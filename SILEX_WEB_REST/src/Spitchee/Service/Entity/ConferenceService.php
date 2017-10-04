<?php

namespace Spitchee\Service\Entity;

use Ramsey\Uuid\Uuid;
use Spitchee\Entity\Conference;
use Spitchee\Entity\SipAccount;
use Spitchee\Entity\User;
use Spitchee\Service\Generic\BaseEntityService;
use Spitchee\Util\Operation\OperationFailure;
use Spitchee\Util\Operation\OperationResult;
use Spitchee\Util\Operation\OperationSuccess;

class ConferenceService extends BaseEntityService
{
    public function createActiveConference(User $confMaster, User $speaker, $wantedId, $save = true)
    {
        $errorOnWantedId = $this->getErrorOnWantedId($wantedId);

        if ($errorOnWantedId) {
            return $errorOnWantedId;
        }

        $conferenceId = $wantedId ?: $this->generateConferneceId();

        $conf = new Conference($conferenceId, $confMaster, $speaker);

        if ($save) {
            $this->persist($conf);
            $this->persist($speaker);
            $this->persist($confMaster);
            $this->flush();
        }

        return $conf;
    }

    /**
     * Lier un utilisateur à une conference-call Asterisk
     *
     * @param User $user
     * @param Conference $conference
     * @param bool $save
     * @return OperationResult
     */
    public function registerUserToSipConference(User $user, Conference $conference, $save = true)
    {
        if (true !== UserService::isSipRole($user->getActiveRole()))
        {
            return OperationFailure::fromClient(
                'Ca tente de add un user non admissible sip dans une conf (usr: ' . $user->getUuid() . ')'
            );
        }

        $registerOperation = $this->getContainer()->getAsteriskServicesAskerService()->registerToConference($conference, $user->getSipAccount());

        if (true !== $registerOperation->isSuccessfull())
        {
            return $registerOperation;
        }

        $sipReloadOperation = $this->getContainer()->getAsteriskServicesAskerService()->sipReload();

        if (true !== $sipReloadOperation->isSuccessfull())
        {
            return $sipReloadOperation;
        }

        $user->setActiveConference($conference);

        if ($save) {
            $this->persist($user);
            $this->flush();
        }
        
        return OperationSuccess::create();
    }

    /**
     * Démarrer un appel-conférence
     *
     * @param Conference $conference
     * @param bool $save
     * @return OperationResult
     */
    public function tryConferenceCall(Conference $conference, $save = true)
    {
        if (! in_array($conference->getState(), [
            Conference::STATE_INACTIVE,
            Conference::STATE_WAITING_FOR_SPEAKER,
            Conference::STATE_INITIALIZED
        ])) {
            return OperationFailure::fromClient("Etat de conference non admissible");
        }
        
        if (null === $speaker = $conference->getSpeaker()) {
            return OperationFailure::fromClient("La conf a pas de speaker");
        }
        
        if (SipAccount::SIP_STATUS_ONLINE !== $speaker->getSipAccount()->getStatus()) {
            return OperationFailure::fromClient("Le speaker est sip-offline");
        }

        $originateResult = $this->originateCall(
            $conference, $speaker->getSipAccount()
        );

        if (false === $originateResult->isSuccessfull()) {
            return $originateResult;
        }

        $conference->setState(Conference::STATE_WAITING_FOR_SPEAKER);

        $this->getContainer()->getRabbitPublisherService()->publishConferenceState($conference);
        
        if ($save) {
            $this->persist($conference);
            $this->flush();
        }
        
        return OperationSuccess::create();
    }

    public function callIntoConference(Conference $conference, User $user)
    {
        if (Conference::STATE_ACTIVE !== $conference->getState()) {
            return OperationFailure::fromClient("Mauvais état de conférence");
        }

        if (User::ROLE_PUBLIC !== $user->getActiveRole()) {
            return OperationFailure::fromClient("Un non-agora ne peut pas être call par ici");
        }

        if (SipAccount::SIP_STATUS_ONLINE !== $user->getSipAccount()->getStatus()) {
            return OperationFailure::fromClient("Le mec est offlinem, allo");
        }

        return $this->originateCall($conference, $user->getSipAccount());
    }

    /**
     * Lancer un appel (Asterisk vers user dans channel de la conf)
     *
     * @param Conference $conference
     * @param SipAccount $sipAccount
     * @return OperationResult
     */
    private function originateCall(Conference $conference, SipAccount $sipAccount)
    {
        return $this
            ->getContainer()
            ->getAsteriskServicesAskerService()
            ->originate($conference, $sipAccount);
    }

    /**
     * @param $id
     * @return array|null
     */
    private function getErrorOnWantedId($id)
    {
        if (! $this->conferenceIdIsInValidFormat($id)) {
            return [
                'type' => 'ConferenceIdUnvalidFormat',
                'details' => 'Que des lettres et des chiffres PD'
            ];
        }

        if (! $this->conferenceIdIsAvailable($id)) {
            return [
                'type' => 'ConferenceIdUnavailable',
            ];
        }

        return null;
    }

    private function conferenceIdIsAvailable($id)
    {
        return  $this->getContainer()
            ->getRepositoryService()
            ->getConferenceRepository()
            ->findOneBy(['uuid' => $id])
            === null;
    }

    /**
     * @param $id
     * @return boolean
     */
    private function conferenceIdIsInValidFormat($id)
    {
        return ctype_alnum($id);
    }

    private function generateConferneceId()
    {
        $wordsList = file_get_contents(
            __DIR__ . '/../../../../dico_50kmots_lenmax7.txt'
        );

        $words = explode(PHP_EOL, $wordsList);
        $id = null;

        $cpt = 0;
        while ($id === null or (!$this->conferenceIdIsAvailable($id))) {
            // Si on a test 250 fois on laisse béton
            if ($cpt ++ == 250) {
                return $this->genSmallUuid();
            }

            $maxIndex = count($words) - 1;
            $choosenIndex = rand(0, $maxIndex);

            $id = $words[$choosenIndex];
        }

        return $id;
    }

    private function genSmallUuid() {
        $id = substr(Uuid::uuid4(), 0, 8);

        while (null !== $this->getContainer()->getRepositoryService()->getConferenceRepository()->findOneBy([
                'uuid' => $id
            ])) $id = substr(Uuid::uuid4(), 0, 8);

        return $id;
    }
}