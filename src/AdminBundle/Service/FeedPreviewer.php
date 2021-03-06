<?php

namespace AdminBundle\Service;

use AdminBundle\Entity\Feed;
use AdminBundle\Service\FeedPreviewer\EventImporter;
use AdminBundle\Service\FeedReader\ValueConverter;
use AppBundle\Entity\User;
use Gedmo\Blameable\BlameableListener;
use Psr\Log\LoggerInterface;

/**
 *
 */
class FeedPreviewer extends FeedReader {
  /**
   * @var \AdminBundle\Service\FeedPreviewer\EventImporter
   */
  protected $eventImporter;

  protected $events = [];

  /**
   * @param \AdminBundle\Service\FeedReader\ValueConverter $valueConverter
   * @param \AdminBundle\Service\FeedReader\EventImporter $eventImporter
   * @param array $configuration
   * @param \Psr\Log\LoggerInterface $logger
   * @param \AdminBundle\Service\AuthenticatorService $authenticator
   * @param \Gedmo\Blameable\BlameableListener $blameableListener
   */
  public function __construct(ValueConverter $valueConverter, array $configuration, LoggerInterface $logger, AuthenticatorService $authenticator, BlameableListener $blameableListener) {
    $this->eventImporter = new EventImporter();
    parent::__construct($valueConverter, $this->eventImporter, $configuration, $logger, $authenticator, $blameableListener);
    $this->authenticator = null;
  }

  /**
   * @param \AdminBundle\Entity\Feed $feed
   */
  public function read(Feed $feed, User $user = NULL) {
    $this->events = [];
    parent::read($feed, null);
  }

  public function getEvents() {
    return $this->events;
  }

  /**
   * @param array $data
   */
  public function createEvent(array $data) {
    $data['feed'] = $this->feed;
    $data['feed_event_id'] = $data['id'];
    $event = $this->eventImporter->import($data);

    $this->events[] = $event;
  }

}
