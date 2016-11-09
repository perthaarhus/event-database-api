<?php

namespace PropertiableBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity property.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="PropertiableBundle\Entity\EntityPropertyRepository")
 */
class EntityProperty {
  /**
   * @var string
   *
   * @ORM\Id
   * @ORM\Column(type="string", length=255)
   */
  private $entityType;

  /**
   * @var int
   *
   * @ORM\Id
   * @ORM\Column(type="integer")
   */
  private $entityId;

  /**
   * @var string
   *
   * @ORM\Id
   * @ORM\Column(type="string", length=255)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(type="text")
   */
  private $value;

  public function __construct($entityType, $entityId, $name, $value) {
    $this->entityType = $entityType;
    $this->entityId = $entityId;
    $this->name = $name;
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getEntityType(): string {
    return $this->entityType;
  }

  /**
   * @return int
   */
  public function getEntityId(): int {
    return $this->entityId;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getValue(): string {
    return $this->value;
  }

  /**
   * Set value.
   *
   * @param mixed $value
   *
   * @return EntityProperty
   */
  public function setValue($value) {
    $this->value = $value;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function toString() {
    return $this->entityType . '#' . $this->entityId . '.' . $this->name . ': ' . $this->value;
  }
}
