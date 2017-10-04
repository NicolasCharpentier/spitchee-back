<?php

namespace Spitchee\Service\Asterisk\Event\Listener;

use Spitchee\Entity\Conference;
use Spitchee\Entity\NamiEvent;
use Spitchee\Entity\SipAccount;
use Spitchee\Entity\User;
use Spitchee\Service\Asterisk\Event\AsteriskEventsDefinitionService;
use Spitchee\Service\Generic\ContainerAwareService;

class AsteriskOriginateResponseEventListenerService extends ContainerAwareService implements AsteriskEventListenerService
{
    public function processEvent($eventArray)
    {
        $peerId = str_replace('SIP/', '', $eventArray['channel']);
        if (false !== $pos = strpos($peerId, '-')) {
            $peerId = substr($peerId, 0, $pos);
        }
        
        $namiEvent = new NamiEvent(AsteriskEventsDefinitionService::TYPE_ORIGINATE_RESPONSE, $eventArray);
        
        $sipAccount = $this->getContainer()->getRepositoryService()->getSipAccountRepository()->find($peerId);
        
        if (! $sipAccount instanceof SipAccount) {
            $this->getContainer()->getLogger()->addWarning("Attention un originateResponse ne retrouve pas le destinataire ($peerId)");
            return $namiEvent;
        }

        $conference = $sipAccount->getUser()->getActiveConference();
        
        $namiEvent->setRelatedSipAccount($sipAccount);
        $namiEvent->setRelatedConference($conference);

        $this->getContainer()->getEntityManager()->persist($conference);
        $this->getContainer()->getEntityManager()->persist($sipAccount);

        $originateSuccess = $eventArray['response'] === 'Success';

        if (true === $originateSuccess) {
            // Ce sera TJRS géré dans les confBridge
            return $namiEvent;
        }
        
        // -- Ne jamais set le activeChannel ici, à cause du bug Asterisk cf. ConfBridgeListener

        // En cas de fail, la conference reste en WAITING_FOR_SPEAKER, on a juste à notify ici

        $this->getContainer()->getRabbitPublisherService()->publishCallDecline($conference, $sipAccount->getUser());

        /*
        if (User::ROLE_HP === $sipAccount->getUser()->getActiveRole() and
            Conference::STATE_WAITING_FOR_SPEAKER === $conference->getState()
        ) {
            //if (! $originateSuccess) {
            //    // tod-o inform lecturer que ca a fail le init Conference
            //    // (et lui permettre de re éssayer)
            //} else {
            //    $conference->setState(Conference::STATE_ACTIVE_SPEAKER_ONLY);
            //    // $sipAccount->setActiveChannel($namiEvent->getInformation()->channel);
            //    // Rien en
            //}
        }
        
        else if (Conference::STATE_ACTIVE & $conference->getState()
        ) {
            if (! $originateSuccess) {
                // to-do inform lecturer que le call a fail
                // (il devrait pouvoir reessayer en redonnat la parole)
            } else {
                $conference->setState(Conference::STATE_ACTIVE_SPEAKER_NOT_ALONE);
                //$sipAccount->setActiveChannel($namiEvent->getInformation()->channel);
                // to-do inform lecturer que appel successfull
            }
        }
        */
        
        //$this->getEntityManager()->persist($sipAccount);
        
        return $namiEvent;
    }
}