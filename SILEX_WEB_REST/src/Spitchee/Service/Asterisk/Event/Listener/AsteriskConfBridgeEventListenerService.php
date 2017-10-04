<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

use Spitchee\Entity\Conference;
use Spitchee\Entity\NamiEvent;
use Spitchee\Entity\SipAccount;
use Spitchee\Entity\User;
use Spitchee\Service\Asterisk\Event\AsteriskEventsDefinitionService;
use Spitchee\Service\Generic\ContainerAwareService;

class AsteriskConfBridgeEventListenerService extends ContainerAwareService implements AsteriskEventListenerService
{
    /**
     * On ne set le activeChannel uniquement depuis ici car l'event ConfbridgeJoin arrive environ 
     * 10-15 secondes après le originate / confbridgeStart. Ce qui fais que les actions via un channel
     * comme un kick ou un mute, agencées dans ce laps de temps, n'auront aucun effet sur la conference
     * MAIS pour Asterisk si, ce qui fait que les actions ne seront pas réutilisables.
     * 
     * En gros chaque action concernant un channel pendant ce laps de temps, ne fonctionnera pas 
     * malgrés un success recu, et surtout empechera leur re-utilisation, car Asterisk croira 
     * qu'elles ont déjà été faites (genre on ne peut pas kick un user deja kick). 
     */
    
    
    
    public function processEvent($eventArray)
    {
        $namiEvent = new NamiEvent($eventArray['event'], $eventArray);
        
        $conference = $this->getContainer()->getRepositoryService()->getConferenceRepository()->find($eventArray['conference']);
        $namiEvent->setRelatedConference($conference);
        
        switch ($eventArray['event']) {
            case AsteriskEventsDefinitionService::TYPE_CONFBRIDGE_END:
                $this->handleEnding($namiEvent);
                break;
            case AsteriskEventsDefinitionService::TYPE_CONFBRIDGE_LEAVE:
                $this->handleLeaving($namiEvent);
                break;
            case AsteriskEventsDefinitionService::TYPE_CONFBRIDGE_JOIN:
                $this->handleJoining($namiEvent);
                break;
            case AsteriskEventsDefinitionService::TYPE_CONFBRIDGE_START:
                $this->handleStarting($namiEvent);
                break;
            default:
                throw new \LogicException("The fuck");
        }

        $this->getContainer()->getEntityManager()->persist($conference);
        
        return $namiEvent;
    }
    
    private function handleEnding(NamiEvent $event) {
        $event->getRelatedConference()->setState(Conference::STATE_INACTIVE);

        $this->getRabbitPublisherService()->publishConferenceState($event->getRelatedConference());
    }
    
    private function handleJoining(NamiEvent $event) {
        $this->registerSipAccountOnChannelPresence($event);

        $sipAccount = $event->getRelatedSipAccount();
        $sipAccount->setActiveChannel($event->getInformation()->channel);
        $sipAccount->setStatus(SipAccount::SIP_STATUS_ON_CALL);

        $user = $sipAccount->getUser();
        $user->setWannaTalkSince(null);

        if (User::ROLE_HP === $user->getActiveRole()) {
            return;
        }

        $this->getRabbitPublisherService()->publishAsks($event->getRelatedConference(), $this->getContainer()->getRepositoryService()->getUserRepository(), $user);
        $this->getRabbitPublisherService()->publishCallUsersIncrement($event->getRelatedConference(), $user);
    }

    private function handleLeaving(NamiEvent $event) {
        $this->registerSipAccountOnChannelPresence($event);

        $event->getRelatedSipAccount()->setStatus(SipAccount::SIP_STATUS_ONLINE);
        $event->getRelatedSipAccount()->setActiveChannel(null);

        $user = $event->getRelatedSipAccount()->getUser();
        if (User::ROLE_HP === $user->getActiveRole()) {
            return;
        }

        $this->getRabbitPublisherService()->publishCallUsersDecrement($event->getRelatedConference(), $user);
    }

    private function handleStarting(NamiEvent $event) {
        $conference = $event->getRelatedConference();
        $conference->setState(Conference::STATE_ACTIVE);
        $conference->getSpeaker()->getSipAccount()->setStatus(SipAccount::SIP_STATUS_ON_CALL);

        $this->getRabbitPublisherService()->publishConferenceState($conference);
    }

    private function registerSipAccountOnChannelPresence(NamiEvent $event) {
        $channel = $event->getInformation()->channel;
        $sipAcId = str_replace('SIP/', '', $channel);

        if (false !== $pos = strpos($sipAcId, '-')) {
            $sipAcId = substr($sipAcId, 0, $pos);
        }

        $sipAccount = $this->getContainer()->getRepositoryService()->getSipAccountRepository()->find($sipAcId);

        if (! $sipAccount instanceof SipAccount) {
            throw new \LogicException("Pas de compte sip pour channel $channel de conf " . $event->getRelatedConference()->getUuid());
        }

        $event->setRelatedSipAccount($sipAccount);

        $this->getContainer()->getEntityManager()->persist($sipAccount);
    }

    private function getRabbitPublisherService()
    {
        return $this->getContainer()->getRabbitPublisherService();
    }
}