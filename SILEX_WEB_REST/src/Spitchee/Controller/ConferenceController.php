<?php

namespace Spitchee\Controller;

use Spitchee\Entity\Conference;
use Spitchee\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Prefix /api/conference
 */
class ConferenceController extends BaseController
{
    private function findConference($id = null)
    {
        if (null !== $id) {
            $conference = $this->getRepositoryService()->getConferenceRepository()->find($id);

            if ($conference instanceof Conference) {
                return $conference;
            }

            throw new NotFoundHttpException("Conference d'id $id introuvable");
        }

        // Attention ca fix à conferencier only atm
        if (null === $this->getUser() or User::ROLE_CONFERENCIER !== $this->getUser()->getActiveRole()) {
            throw new NotFoundHttpException("Conférence introuvable");
        }

        return $this->getUser()->getActiveConference();
    }

    /**
     * Création d'une conference prête à démarrer
     * 
     * @Path /active/create
     * @Method POST
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createActiveConferenceAction()
    {
        $this->authRestrict(User::ROLE_CONFERENCIER);

        // TODO bien dire dans doc le traitement que je fais
        $wantedId   = $this->getPostArg('conferenceId', null);
        $wantedId   = strtoupper($wantedId);
        $wantedId   = str_replace(' ', '', $wantedId);
        $speaker    = $this->getUserService()->createTempUser(User::ROLE_HP, null, true, false);
        $conference = $this->getConferenceService()->createActiveConference(
            $this->getUser(), $speaker, $wantedId
        );

        if (! ($conference instanceof Conference)) {
            $error = $conference;
            return $this->jsonError($error, 400);
        }

        $sipSubscription = $this->getConferenceService()->registerUserToSipConference($speaker, $conference);

        if (! $sipSubscription->isSuccessfull()) {
            // todo - remove la conference
            return $this->operationResult($sipSubscription);
        }
        
        return $this->json([
            'conferenceId' => $conference->getUuid(),
            'speakerId' => $speaker->getUuid(),
        ], 201);
    }

    /**
     * Rejoindre une conférence
     * 
     * @Path /active/{id}/subscribe
     * @Method POST
     * @param $id
     * @return Response
     */
    public function subscribeToActiveConferenceAction($id)
    {
        $this->authRestrict(User::ROLE_PUBLIC);

        $conference = $this->findConference($id);

        $this->getSipAccountService()->createSipAccount($this->getUser());

        $subscription = $this->getConferenceService()->registerUserToSipConference($this->getUser(), $conference, true);

        if (! $subscription->isSuccessfull())
            return $this->operationResult($subscription);

        return $this->json($this->getUser()->toArray());
    }

    /**
     * Virer un utilisateur d'un appel-conférence
     * 
     * @Path /active/kick/{userId}
     * @Method POST
     * @param $userId
     * @return Response
     */
    public function kickFromCallAction($userId)
    {
        $this->authRestrict(User::ROLE_CONFERENCIER);

        $conference = $this->findConference();

        if (null === $user = $this->getRepositoryService()->getUserRepository()->find($userId)) {
            return $this->error("User d'id $userId introuvable", 404);
        }

        if (null === $user->getSipAccount() or null === $user->getSipAccount()->getActiveChannel()) {
            return $this->error("L'utilisateur n'est pas actuellement enregistré dans une conference Asterisk", 400);
        }

        return $this->operationResult($this->getContainer()
            ->getAsteriskServicesAskerService()
            ->kickFromConference($conference, $user->getSipAccount()->getActiveChannel())
        );
    }

    /**
     * Lancer l'appel de conférence (ne s'utilise pas par défaut car c fait auto, c au cas où)
     *
     * @Path /active/startCall
     * @Method POST
     * @return Response
     */
    public function startCallAction()
    {
        $this->authRestrict(User::ROLE_CONFERENCIER);
        
        $conference = $this->findConference();

        return $this->operationResult(
            $this->getConferenceService()->tryConferenceCall($conference)
        );
    }

    /**
     * Appelle un agora dans la conf
     *
     * @Path /active/call/{userId}
     * @Method POST
     * @param $userId
     * @return Response
     */
    public function callAgoraAction($userId)
    {
        $this->authRestrict(User::ROLE_CONFERENCIER);

        $conference = $this->findConference();

        if (null === $user = $this->getRepositoryService()->getUserRepository()->find($userId)) {
            return $this->error("User $userId introuvable", 404);
        }

        return $this->operationResult($this
            ->getConferenceService()->callIntoConference($conference, $user)
        );
    }


    /**
     * Recevoir tous les utilisateurs inscrits à une conférence
     *
     * @Path /{id}/debug
     * @Method GET
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function tmpDebugConferenceAction($id)
    {
        $conference = $this->findConference($id);

        return $this->json($conference->getActiveUsers()->map(
            function (User $user) {
                return $user->toArray();
            }
        )->toArray());
    }
}