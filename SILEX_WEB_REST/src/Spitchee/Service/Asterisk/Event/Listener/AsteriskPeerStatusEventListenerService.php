<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

use Spitchee\Entity\Conference;
use Spitchee\Entity\NamiEvent;
use Spitchee\Entity\SipAccount;
use Spitchee\Entity\User;
use Spitchee\Service\Asterisk\Event\AsteriskEventsDefinitionService;
use Spitchee\Service\Generic\ContainerAwareService;
use Spitchee\Service\Rabbit\RabbitPublisherService;

class AsteriskPeerStatusEventListenerService extends ContainerAwareService implements AsteriskEventListenerService
{
    public function processEvent($eventArray) {
        $peerId = str_replace('SIP/', '', $eventArray['peer']);
        $sipAccount = $this->getContainer()->getRepositoryService()->getSipAccountRepository()->find($peerId);

        $event = new NamiEvent(AsteriskEventsDefinitionService::TYPE_PEER_STATUS, $eventArray, $sipAccount);

        if (! $sipAccount instanceof SipAccount) {
            return $event;
        }

        $oldStatus = $sipAccount->getStatus();

        $this->handleSipAccountEffects($sipAccount, $eventArray['peerstatus']);

        // On register ap si c la meme pour eviter que 99.9992% de la db soit des register
        if ($sipAccount->getStatus() === $oldStatus) {
            return null;
        }

        $conference = $this->handleConferenceEffects($sipAccount);
        $event->setRelatedConference($conference);

        if (User::ROLE_HP === $sipAccount->getUser()->getActiveRole() and
            SipAccount::SIP_STATUS_OFFLINE === $sipAccount->getStatus())
        {
            $this->getContainer()->getRabbitPublisherService()->publishWarning(
                $conference, RabbitPublisherService::WARNING_SPEAKER_OFFLINE
            );
        }

        $this->getContainer()->getEntityManager()->persist($conference);
        $this->getContainer()->getEntityManager()->persist($sipAccount);

        return $event;
    }
    
    private function handleSipAccountEffects(SipAccount $sipAccount, $newStatus)
    {
        // Registered || Unregistered || Lagged|Reachable|Unreachable
        if (in_array($newStatus, ['Registered', 'Reachable', 'Lagged'])) {
            if (SipAccount::SIP_STATUS_ON_CALL !== $sipAccount->getStatus()) {
                $newStatus = SipAccount::SIP_STATUS_ONLINE;
            }
        } else {
            $newStatus = SipAccount::SIP_STATUS_OFFLINE;
        }

        $sipAccount->setStatus($newStatus);
        
        return $sipAccount;
    }
    
    private function handleConferenceEffects(SipAccount $sipAccount)
    {
        $user = $sipAccount->getUser();
        $conference = $sipAccount->getUser()->getActiveConference();

        // Si conference init ou deja en waiting et que le speaker co, go l'appeler
        if (User::ROLE_HP === $user->getActiveRole() and
           (Conference::STATE_INITIALIZED === $conference->getState() or
            Conference::STATE_WAITING_FOR_SPEAKER === $conference->getState() or
            Conference::STATE_INACTIVE === $conference->getState()) and
            SipAccount::SIP_STATUS_ONLINE === $sipAccount->getStatus()
        ) {
            $this->getContainer()->getConferenceService()->tryConferenceCall($conference, false);
        }

        return $conference;
    }
}