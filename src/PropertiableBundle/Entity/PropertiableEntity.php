<?php

namespace PropertiableBundle\Entity;

interface PropertiableEntity {
  public function setProperties(array $properties, bool $clear = false);

  public function getProperties();
}
