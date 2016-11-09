<?php

namespace PropertiableBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use PropertiableBundle\Entity\EntityProperty;
use PropertiableBundle\Entity\PropertiableEntity;

/**
 *
 */
class EntityPropertyManager implements EntityPropertyManagerInterface {
  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
   */
  private $metadataFactory;

  /**
   * @var \PropertiableBundle\Entity\EntityPropertyRepository
   */
  private $repository;

  /**
   * @param \Doctrine\ORM\EntityManagerInterface $entityManager
   */
  public function __construct(EntityManagerInterface $entityManager) {
    $this->em = $entityManager;
    $this->metadataFactory = $this->em->getMetadataFactory();
    $this->repository = $this->em->getRepository('PropertiableBundle:EntityProperty');
  }

  public function getEntities($className, array $criteria) {
    $className = $this->getClassName($className);
    $parameters = [];
    $builder = $this->em->createQueryBuilder();
    $builder->select('e')->from($className, 'e');
    foreach ($criteria as $name => $value) {
      $index = count($parameters);
      $alias = 'p' . $index;
      $builder->innerJoin($this->repository->getClassName(), $alias,
        Expr\Join::WITH,
        $builder->expr()->andX(
          $alias . '.entityType = :className',
          $alias . '.entityId = e.id'
        ));
      $builder
        ->andWhere($builder->expr()->eq($alias . '.name', ':name' . $index))
        ->andWhere($builder->expr()->eq($alias . '.value', ':value' . $index));
      $parameters[':name' . $index] = $name;
      $parameters[':value' . $index] = $value;
      $parameters[':className'] = $className;
    }
    $query = $builder->getQuery();
    $query->setParameters($parameters);

    return $query->getResult();
  }

  public function getEntity($className, array $criteria) {
    $result = $this->getEntities($className, $criteria);
    return count($result) > 0 ? $result[0] : NULL;
  }

  public function saveProperties(PropertiableEntity $entity) {
    $this->clearProperties($entity);
    $properties = $entity->getProperties();
    foreach ($properties as $name => $value) {
      $property = $this->repository->findOneByEntity($entity, $name);
      if ($property === NULL) {
        $property = new EntityProperty($this->getClassName($entity),
          $entity->getId(), $name, NULL);
      }
      $property->setValue($value);

      echo PHP_EOL, __METHOD__, PHP_EOL, $property->toString(), PHP_EOL;

      $this->em->persist($property);
    }
    $this->em->flush();
  }

  public function loadProperties(PropertiableEntity $entity) {
    $properties = $this->repository->findByEntity($entity);
    $values = [];
    foreach ($properties as $property) {
      $values[$property->getName()] = $property->getValue();
    }
    $entity->setProperties($values, TRUE);
  }

  public function clearProperties(PropertiableEntity $entity) {
    $className = $this->getClassName($entity);
    $builder = $this->em->createQueryBuilder();
    $builder->delete('PropertiableBundle:EntityProperty', 'p')
      ->where('p.entityType = :className and p.entityId = :entityId')
      ;
    $query = $builder->getQuery();
    $query->setParameters([':className' => $className, ':entityId' => $entity->getId()]);
    $query->execute();
  }

  private function getClassName($class) {
    if (is_object($class)) {
      $class = get_class($class);
    }
    return $this->metadataFactory->getMetadataFor($class)->getName();
  }
}
