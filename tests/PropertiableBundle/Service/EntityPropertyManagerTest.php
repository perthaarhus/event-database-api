<?php

use AppBundle\Entity\User;
use PropertiableBundle\Service\EntityPropertyManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\Test\DatabaseTestCase;
use AppBundle\Entity\Event;

class EntityPropertyManagerTest extends DatabaseTestCase {
  /**
   * @var EntityPropertyManager
   */
  private $entityPropertyManager;

  public function setUp() {
    parent::setUp();
    $this->entityPropertyManager = new EntityPropertyManager($this->em);

    $username = 'username';
    $email = $username . '@example.com';
    $password = 'password';
    $firewall = 'main';
    $roles = ['ROLE_ADMIN'];

    $user = new User();
    $user
      ->setUsername($username)
      ->setPlainPassword($password)
      ->setEmail($email)
      ->setRoles($roles)
      ->setEnabled(TRUE);
    $this->persist($user);
    $this->flush();

    $token = new UsernamePasswordToken($user, $password, $firewall);
    $this->container->get('security.token_storage')->setToken($token);
  }

  private function loadEntities() {
    $sql = <<<SQL
insert into event(id, created_at, updated_at, name) values
(23, '2001-01-01', '2001-01-01', 'Event 23'),
(42, '2001-01-01', '2001-01-01', 'Event 42'),
(87, '2001-01-01', '2001-01-01', 'Event 87')
;
SQL;

    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();

    $sql = 'select * from event';
    $stmt = $this->em->getConnection()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
    $this->assertEquals(3, count($result));
  }

  private function loadEntityProperties() {
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

    $this->assertEquals(6, count($this->getEntityProperties()));
  }

  public function testSaveAndLoadEntityProperties() {
    $this->loadEntities();

    $entity = $this->em->getRepository('AppBundle:Event')->find(87);
    $this->assertNotNull($entity);
    $this->assertEquals(87, $entity->getId());
    $this->assertEquals([], $entity->getProperties());

    $this->assertEquals(0, count($this->getEntityProperties()));

    $entity->setProperties([
      'feedId' => 12,
      'feedEventId' => 112,
    ]);
    $this->saveProperties($entity);
    // $this->printEntityProperties();

    $this->assertEquals(2, count($this->getEntityProperties()));

    $this->loadProperties($entity);
    $this->assertEquals(87, $entity->getId());
    $this->assertEquals([
      'feedId' => 12,
      'feedEventId' => 112,
    ], $entity->getProperties());

    $entity->setProperties([
      'feedId' => 2,
      'feedEventId' => 12,
    ]);
    $this->saveProperties($entity);

    // $this->printEntityProperties();

    // $sql = 'select * from entity_property';
    // $stmt = $this->em->getConnection()->prepare($sql);
    // $stmt->execute();
    // $result = $stmt->fetchAll();
    // var_export($result);

    // $entityProperties = $this->getEntityProperties();
    // $this->assertEquals(2, count($entityProperties));
    // $this->assertEquals('feedEventId', $entityProperties[0]->getName());
    // $this->assertEquals(12, $entityProperties[0]->getValue());
    // $this->assertEquals('feedId', $entityProperties[1]->getName());
    // $this->assertEquals(2, $entityProperties[1]->getValue());




    $this->assertEquals(2, count($this->getEntityProperties()));

    $this->printEntityProperties();
    var_export($entity->getProperties());

    $this->loadProperties($entity);

    $this->printEntityProperties();

    $this->assertEquals(87, $entity->getId());
    $this->assertEquals([
      'feedId' => 2,
      'feedEventId' => 12,
    ], $entity->getProperties());
  }

  public function testStuff() {
    $this->loadEntities();
    $this->loadEntityProperties();

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

    $this->assertEquals(6, count($this->getEntityProperties()));
    $entity = $this->em->getRepository('AppBundle:Event')->find(87);
    $this->loadProperties($entity);
    $entity->setProperties(['feedId' => 2]);
    $this->saveProperties($entity);
    $this->assertEquals(6, count($this->getEntityProperties()));

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

    $this->assertEquals(6, count($this->getEntityProperties()));

    $entity->setProperties([
      'feedId' => 12,
      'feedEventId' => 112,
    ]);
    $this->saveProperties($entity);

    $this->printEntityProperties();

    $this->assertEquals(6, count($this->getEntityProperties()));

    $this->loadProperties($entity);
    $this->assertEquals(87, $entity->getId());
    $this->assertEquals([
      'feedId' => 12,
      'feedEventId' => 112,
    ], $entity->getProperties());

    $event = new Event();
    $event->setProperties(['tests' => 'tests'], true);
    $event->setProperties(['test' => 'test'], true);
    $this->em->persist($event);
    $this->em->flush();
    $this->saveProperties($event);

    $this->printEntityProperties();
    $this->assertEquals(7, count($this->getEntityProperties()));

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

    $this->printEntityProperties();

    $entities = $this->entityPropertyManager->getEntities('AppBundle:Event', [
      'feedId' => 12,
      'feedEventId' => 112,
    ]);
    $this->assertNotNull($entities);
    $this->assertEquals(1, count($entities));

    $this->printEntityProperties();

    $entity = $entities[0];
    $entity->setProperties(['single' => 'single'], true);
    $this->em->persist($entity);
    $this->em->flush();

    $this->printEntityProperties();

    $this->assertEquals(7, count($this->getEntityProperties()));

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
    return;
    $properties = $this->getEntityProperties();

    echo PHP_EOL, str_repeat('-', 80), PHP_EOL;
    echo 'Entity properties', PHP_EOL;
    foreach ($properties as $property) {
      echo '  ' . $property->toString(), PHP_EOL;
    }
    echo str_repeat('-', 80), PHP_EOL;
  }

  private function getEntityProperties() {
    return $this->em->getRepository('PropertiableBundle:EntityProperty')->findAll();
  }

  private function saveProperties($entity) {
    $this->entityPropertyManager->saveProperties($entity);
  }

  private function loadProperties($entity) {
    $this->entityPropertyManager->loadProperties($entity);
  }
}
