<?php

namespace PropertiableBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use PropertiableBundle\Entity\PropertiableEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListener {
  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $container;

  /**
   * @var \PropertiableBundle\Service\EntityPropertyManagerInterface
   */
  private $entityPropertyManager;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function postPersist(LifecycleEventArgs $args) {
    $this->postUpdate($args);
  }

  public function postUpdate(LifecycleEventArgs $args) {
    $entity = $args->getEntity();

    if (!$entity instanceof PropertiableEntity) {
      return;
    }

    $this->getEntityPropertyManager()->saveProperties($entity);
  }

  public function postLoad(LifecycleEventArgs $args) {
    $entity = $args->getEntity();

    if (!$entity instanceof PropertiableEntity) {
      return;
    }

    $this->getEntityPropertyManager()->loadProperties($entity);
  }

  private function getEntityPropertyManager() {
    if ($this->entityPropertyManager === null) {
      $this->entityPropertyManager = $this->container->get('entity_property_manager');
    }

    return $this->entityPropertyManager;
  }
}
