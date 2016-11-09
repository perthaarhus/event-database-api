<?php

use PropertiableBundle\Service\EntityPropertyManager;
use Tests\AppBundle\Test\DatabaseTestCase;
use AppBundle\Entity\Event;

class EntityPropertyManagerTest extends DatabaseTestCase {
  /**
   * @var EntityPropertyManager
   */
  private $entityPropertyManager;

  public function setUp() {
    parent::setUp();
    $this->loadData();
    $this->entityPropertyManager = new EntityPropertyManager($this->em);
    // $this->em->getEventManager()->addEventListener(['postLoad', 'postPersist', 'postUpdate'], new PropertiableBundle\EventListener\EventListener($this->container));
  }

  private function loadData() {
    $sql = <<<SQL
insert into entity_property(entity_type, entity_id, name, value) values
('AppBundle\Entity\Event', 23, 'feedId', '1'),
('AppBundle\Entity\Event', 23, 'feedEventId', '17'),

('AppBundle\Entity\Event', 42, 'feedId', '1'),
('AppBundle\Entity\Event', 42, 'feedEventId', '12'),

('AppBundle\Entity\Event', 87, 'feedId', '2'),
('AppBundle\Entity\Event', 87, 'feedEventId', '12')
;
SQL;

    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    $sql = <<<SQL
insert into event(id, created_at, updated_at, name) values
(23, '2001-01-01', '2001-01-01', 'Event 23'),
(42, '2001-01-01', '2001-01-01', 'Event 42'),
(87, '2001-01-01', '2001-01-01', 'Event 87')
;
SQL;

    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    $sql = 'select * from entity_property';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(6, count($result));

    $sql = 'select * from event';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(3, count($result));
  }

  public function testStuff() {
    $entity = $this->entityPropertyManager->getEntity('AppBundle:Event', [
      'feedId' => 1,
      'feedEventId' => '12',
    ]);

    $this->assertNotNull($entity);
    $this->assertEquals(42, $entity->getId());
    $this->assertEquals('Event 42', $entity->getName());

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', []);
    $this->assertNotNull($entities);
    $this->assertEquals(3, count($entities));

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 1,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(2, count($entities));

    $entity = $this->em->getRepository('AppBundle:Event')->find(42);
    $this->loadProperties($entity);
    $this->assertEquals([
      'feedId' => 1,
      'feedEventId' => 12,
    ], $entity->getProperties());

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(1, count($entities));

    $entity = $this->em->getRepository('AppBundle:Event')->find(87);
    $entity->setProperties(['feedId' => 2]);
    $this->saveProperties($entity);

    // $this->printEntityProperties();

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(1, count($entities));

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
      'feedEventId' => 12,
    ]);
    $this->assertNotNull($entities);

    var_export([__FILE__.':'.__LINE__, $entities]);

    $this->assertEquals(1, count($entities));

    $entity->setProperties(['feedEventId' => 12]);
    $this->saveProperties($entity);

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
      'feedEventId' => 12,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(1, count($entities));
    $this->assertEquals(87, $entities[0]->getId());

    $entity->setProperties([
      'feedId' => 12,
      'feedEventId' => 112,
    ]);
    $this->saveProperties($entity);

    $this->loadProperties($entity);
    $this->assertEquals([
      'feedId' => 12,
      'feedEventId' => 112,
    ], $entity->getProperties());

    $event = new Event();
    $event->setProperties(['tests' => 'tests'], true);
    $event->setProperties(['test' => 'test'], true);
    $this->em->persist($event);
    $this->em->flush();

    //$this->printEntityProperties();

    $sql = 'select * from entity_property';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(7, count($result));

    //$this->saveProperties($event);

    $entity = $this->entityPropertyManager->getEntity('AppBundle:Event', [
      'test' => 'test',
    ]);

    $this->assertNotNull($entity);
    $this->assertEquals($event->getId(), $entity->getId());

    $anotherEvent = $this->em->getRepository('AppBundle:Event')->find($event->getId());
    $this->assertNotNull($anotherEvent);
    $this->assertEquals([
      'test' => 'test',
    ], $anotherEvent->getProperties());

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
      'feedEventId' => 12,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(1, count($entities));

    $this->printEntityProperties();

    $entity = $entities[0];
    $entity->setProperties(['single' => 'single'], true);
    $this->em->persist($entity);
    $this->em->flush();

    $this->printEntityProperties();

//    var_export([__LINE__, $entity->getId(), $entity->getName()]);

    $sql = 'select * from entity_property';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(7, count($result));

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 2,
      'feedEventId' => 12,
    ]);
    $this->assertNotNull($entities);
//    var_export([__LINE__, $entities[0]->getId(), $entities[0]->getName(), $entities[0]->getProperties()]);

    $this->printEntityProperties();

//    $this->assertEquals(0, count($entities));
  }

  private function printEntityProperties() {
    $properties = $this->em->getRepository('PropertiableBundle:EntityProperty')->findAll();

    echo PHP_EOL;
    foreach ($properties as $property) {
      echo $property->toString(), PHP_EOL;
    }
    echo PHP_EOL;
  }

  private function saveProperties($entity) {
    $this->entityPropertyManager->saveProperties($entity);
  }

  private function loadProperties($entity) {
    $this->entityPropertyManager->loadProperties($entity);
  }
}
