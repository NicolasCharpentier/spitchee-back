<?php

namespace Spitchee\Service\Generic;

class BaseEntityService extends ContainerAwareService
{
    protected function persist($entity) {
        if (null !== $entity)
            $this->getContainer()->getEntityManager()->persist($entity);
    }
    
    protected function flush() {
        $this->getContainer()->getEntityManager()->flush();
    }

    protected function remove($entity) {
        if (null !== $entity)
            $this->getContainer()->getEntityManager()->remove($entity);
    }
}