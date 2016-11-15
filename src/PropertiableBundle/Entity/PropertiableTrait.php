<?php

namespace PropertiableBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PropertiableTrait {
  /**
   * @var array
   */
  private $properties = [];

  public function setProperties(array $properties, bool $clear = false) {
    if ($clear) {
      $this->properties = [];
    }

    $this->properties = array_merge($this->properties, $properties);

    return $this;
  }

  public function getProperties() {
    return $this->properties;
  }
}
