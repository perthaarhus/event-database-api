<?php

namespace PropertiableBundle\Entity;

use Doctrine\ORM\EntityRepository;

class EntityPropertyRepository extends EntityRepository {
  public function findByEntity($entity) {
    $className = $this->getEntityManager()->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
    return $this->findBy(['entityType' => $className, 'entityId' => $entity->getId()]);
  }

  public function findOneByEntity($entity, $name) {
    $className = $this->getEntityManager()->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
    return $this->findOneBy(['entityType' => $className, 'entityId' => $entity->getId(), 'name' => $name]);
  }
}
