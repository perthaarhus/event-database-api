<?php

namespace AdminBundle\Factory;

use AdminBundle\Entity\Feed;
use AppBundle\Entity\Entity;
use AppBundle\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 */
class EventFactory extends EntityFactory {
  /**
   * @var Feed
   */
  protected $feed;

  /**
   * @var OccurrenceFactory
   */
  protected $occurrenceFactory;

  /**
   * @param \AdminBundle\Entity\Feed $feed
   */
  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    if ($this->valueConverter) {
      $this->valueConverter->setFeed($feed);
    }
  }

  /**
   * @param \AdminBundle\Factory\OccurrenceFactory $occurrenceFactory
   */
  public function setOccurrenceFactory(OccurrenceFactory $occurrenceFactory) {
    $this->occurrenceFactory = $occurrenceFactory;
  }

  /**
   * @param array $data
   * @return \AppBundle\Entity\Event|object
   */
  public function get(array $data) {
    $properties = [
      'feed' => $data['feed']->getId(),
      'feedEventId' => $data['feed_event_id'],
    ];

    $entity = $this->entityPropertyManager->getEntity('AppBundle:Event', $properties);
    if ($entity === NULL) {
      $entity = new Event();
    }
    $this->setValues($entity, $data);
    $this->persist($entity);
    $this->flush();

    $entity->setProperties($properties);
    $this->entityPropertyManager->saveProperties($entity);

    return $entity;
  }

  /**
   * @param \AppBundle\Entity\Entity $entity
   * @param $key
   * @param $value
   */
  protected function setValue(Entity $entity, $key, $value) {
    if ($entity instanceof Event) {
      if ($this->accessor->isWritable($entity, $key)) {
        switch ($key) {
          case 'occurrences':
            $occurrences = new ArrayCollection();
            foreach ($value as $item) {
              $item['event'] = $entity;
              $occurrence = $this->occurrenceFactory->get($item);
              $occurrences->add($occurrence);
            }
            $entity->setOccurrences($occurrences);
            return;
        }
      }
    }

    parent::setValue($entity, $key, $value);
  }

}
