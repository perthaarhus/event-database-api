<?php

namespace PropertiableBundle\Service;

use PropertiableBundle\Entity\PropertiableEntity;

/**
 *
 */
interface EntityPropertyManagerInterface {
  public function getEntities($className, array $criteria);
  public function getEntity($className, array $criteria);

  //public function setProperties(PropertiableEntity $entity, array $values, bool $clear = false);
  //public function getProperties(PropertiableEntity $entity);

  public function saveProperties(PropertiableEntity $entity);
  public function loadProperties(PropertiableEntity $entity);
}
