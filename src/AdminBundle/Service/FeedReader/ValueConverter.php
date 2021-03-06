<?php

namespace AdminBundle\Service\FeedReader;

use AdminBundle\Entity\Feed;
use League\Uri\Modifiers\Resolve;
use League\Uri\Schemes\Http as HttpUri;

/**
 *
 */
class ValueConverter {
  protected $feed;
  protected $urlResolver;

  /**
   * @param \AdminBundle\Entity\Feed $feed
   */
  public function setFeed(Feed $feed) {
    $this->feed = $feed;
    $this->urlResolver = $this->feed->getBaseUrl() ? new Resolve(HttpUri::createFromString($this->feed->getBaseUrl())) : NULL;
  }

  /**
   * @param $value
   * @param $name
   * @return \DateTime|null|string
   */
  public function convert($value, $name) {
    switch ($name) {
      case 'startDate':
      case 'endDate':
        return $this->parseDate($value);

      case 'image':
      case 'url':
        if ($this->urlResolver) {
          $relativeUrl = HttpUri::createFromString($value);
          $url = $this->urlResolver->__invoke($relativeUrl);
          $value = (string) $url;
        }
        break;

    }

    return $value;
  }

  /**
   * @param $value
   * @return \DateTime|null
   */
  private function parseDate($value) {
    if (!$value) {
      return NULL;
    }
    if ($value instanceof \DateTime) {
      return $value;
    }

    $date = NULL;
    // JSON date (/Date(...)/)
    if (preg_match('@/Date\(([0-9]+)\)/@', $value, $matches)) {
      $date = new \DateTime();
      $date->setTimestamp(((int) $matches[1]) / 1000);
    }
    elseif (is_numeric($value)) {
      $date = new \DateTime();
      $date->setTimestamp($value);
    }

    if ($date === NULL) {
      try {
        $timeZone = $this->feed ? $this->feed->getTimeZone() : null;
        $format = $this->feed->getDateFormat();
        $date = $format
              ? \DateTime::createFromFormat($format, $value, $timeZone)
              : new \DateTime($value, $timeZone);
      }
      catch (\Exception $e) {
      }
    }

    return $date;
  }

}
