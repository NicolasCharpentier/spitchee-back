<?php

namespace Spitchee\Service\Entity;

use Ramsey\Uuid\Uuid;
use Spitchee\Entity\SipAccount;
use Spitchee\Entity\User;
use Spitchee\Service\Generic\BaseEntityService;

class SipAccountService extends BaseEntityService
{
    public function createSipAccount(User $user, $save = true) {
        list($sipId, $sipPass) = $this->createSipIdentifiers();

        if ($user->getSipAccount()) {
            $this->removeSipAccount($user->getSipAccount());
        }

        $sipAccount = new SipAccount($user, $sipId, $sipPass);

        if ($save) {
            $this->persist($sipAccount);
            $this->persist($user);
            $this->flush();
        }
        
        return $sipAccount;
    }

    private function removeSipAccount(SipAccount $sipAccount)
    {
        $relatedEvents = $this->getContainer()->getRepositoryService()->getNamiEventRepository()->findBy([
            'relatedSipAccount' => $sipAccount
        ]);

        foreach ($relatedEvents as $event) {
            $this->remove($event);
        }

        $this->remove($sipAccount);
        $this->flush();
    }

    private function createSipIdentifiers() {
        while ($sipId = random_int(1015, 9999)) {
            if (null === $this->getContainer()->getRepositoryService()->getSipAccountRepository()->find($sipId)) {
                break;
            }
        }

        return [$sipId, Uuid::uuid4()];
    }
}