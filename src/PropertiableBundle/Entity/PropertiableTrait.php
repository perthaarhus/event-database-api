<?php

namespace PropertiableBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait PropertiableTrait {
  /**
   * @var array
   *
   * @ORM\Column(type="json_array", nullable=true)
   */
  private $properties = [];

  public function setProperties(array $properties, bool $clear = false) {
    if ($clear) {
      $this->properties = [];
    }

    $this->properties = array_merge($this->properties, $properties);
  }

  public function getProperties() {
    return $this->properties;
  }
}
