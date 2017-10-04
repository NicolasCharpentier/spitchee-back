<?php

namespace Spitchee\Controller;

use Spitchee\Entity\User;
use Spitchee\Service\Entity\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Prefix /api/user
 */
class UserController extends BaseController
{
    /**
     * Fill un user HP qui a juste eu son Id à la création de conference du conferencier
     *
     * @Path /temp/speaker/register
     * @Method POST
     * @return JsonResponse
     */
    public function tempRegisterSpeakerAction()
    {
        if (null !== $this->getUser()) {
            return $this->error('Tu es deja co fdp', 403);
        }

        if (null === $id = $this->getPostArg('id', null)) {
            return $this->error('id doit etre present', 400);
        }

        if (null === $user = $this->getRepositoryService()->getUserRepository()->find($id)) {
            return $this->error("Id $id inexistant", 404);
        }

        if (User::ROLE_HP !== $user->getActiveRole()) {
            return $this->error("L'user doit etre de role " . User::ROLE_HP, 400);
        }

        return $this->json($user->toArray(), 200);
    }


    /**
     * Permet de se register en tant que lecturer|agora
     *
     * @Path /temp/{role}/register
     * @Method POST
     * @param $role
     * @return JsonResponse
     */
    public function tempRegisterAction($role)
    {
        if (null !== $this->getUser()) {
            return $this->error('Tu es deja auth', 403);
        }
        
        if (false === UserService::isSelfRegistrableRole($role)) {
            return $this->json("role $role non valide pour le self register", 400);
        }

        $user = $this->getUserService()->createTempUser(
            $role, $this->getPostArg('username')
        );

        return $this->json($user->toArray(), 201);
    }


    /**
     * Enregistre une demande de parole, renvoie 400 si pas possible
     *
     * @Path /wannaTalk
     * @Method POST
     */
    public function registerWannaTalkAction()
    {
        $this->authRestrict(User::ROLE_PUBLIC);

        $success = $this->getUserService()->registerWannaTalk($this->getUser());

        return $this->status($success ? 200 : 400);
    }
}