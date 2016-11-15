<?php

namespace AppBundle\Entity;

use AdminBundle\Entity\Feed;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use DoctrineExtensions\Taggable\Taggable;
use Gedmo\Blameable\Blameable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppBundle\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use PropertiableBundle\Entity\PropertiableEntity;
use PropertiableBundle\Entity\PropertiableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An event happening at a certain time and location, such as a concert, lecture, or festival. Ticketing information may be added via the 'offers' property. Repeated events may be structured as separate Event objects.
 *
 * @see http://schema.org/Event Documentation on Schema.org
 *
 * @ORM\Entity
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @ApiResource(
 *   iri = "http://schema.org/Event",
 *   attributes = {
 *     "jsonld_embed_context" = true,
 *     "normalization_context" = { "groups" = { "event_read" } },
 *     "denormalization_context" = { "groups" = { "event_write" } },
 *     "filters" = { "event.search", "event.search.date", "event.search.tag", "event.search.owner", "event.order", "event.order.default" }
 *   }
 * )
 */
class Event extends Thing implements Taggable, Blameable, PropertiableEntity {
  use TimestampableEntity;
  use BlameableEntity;
  use SoftDeleteableEntity;
  use PropertiableTrait;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var ArrayCollection
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="event", cascade={"persist", "remove"}, orphanRemoval=true)
   * @ORM\OrderBy({"startDate"="ASC", "endDate"="ASC"})
   */
  private $occurrences;

  /**
   * @var string The URI for ticket purchase
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\Column(nullable=true)
   * @Assert\Type(type="string")
   * @ApiProperty(iri="http://schema.org/url")
   */
  private $ticketPurchaseUrl;

  /**
   * @var string Excerpt, i.e. short description, without any markup
   *
   * @Groups({"event_read", "event_write"})
   * @ORM\Column(nullable=true)
   * @Assert\Type(type="string")
   * @Assert\Length(
   *      max = 255,
   *      maxMessage = "The excerpt cannot be longer than {{ limit }} characters"
   * )
   */
  private $excerpt;

  /**
   * Sets id.
   *
   * @param int $id
   *
   * @return $this
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * Gets id.
   *
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   *
   */
  public function setOccurrences($occurrences) {
    // Remove (and implicitly delete) occurrences that will be orphaned after
    // updating settings (new) occurrences.
    $keepIds = [];
    foreach ($occurrences as $occurrence) {
      $keepIds[] = $occurrence->getId();
    }
    foreach ($this->occurrences as $occurrence) {
      if (!in_array($occurrence->getId(), $keepIds)) {
        $this->occurrences->removeElement($occurrence);
      }
    }

    $this->occurrences = $occurrences;

    foreach ($this->occurrences as $occurrence) {
      $occurrence->setEvent($this);
    }

    return $this;
  }

  /**
   * @return ArrayCollection
   */
  public function getOccurrences() {
    return $this->occurrences;
  }

  /**
   * @return mixed
   */
  public function getTicketPurchaseUrl() {
    return $this->ticketPurchaseUrl;
  }

  /**
   * @param mixed $ticketPurchaseUrl
   */
  public function setTicketPurchaseUrl($ticketPurchaseUrl) {
    $this->ticketPurchaseUrl = $ticketPurchaseUrl;
  }

  /**
   * @return string
   */
  public function getExcerpt() {
    return $this->excerpt;
  }

  /**
   * @param string $excerpt
   */
  public function setExcerpt($excerpt) {
    $this->excerpt = $excerpt;
  }

  /**
   *
   */
  public function __construct() {
    $this->occurrences = new ArrayCollection();
  }

  /**
   * @var ArrayCollection
   *
   * @Groups({"event_read", "event_write"})
   * @ ORM\Column(type="array", nullable=true)
   */
  private $tags;

  /**
   * Returns the unique taggable resource type.
   *
   * @return string
   */
  public function getTaggableType() {
    return 'event';
  }

  /**
   * Returns the unique taggable resource identifier.
   *
   * @return string
   */
  public function getTaggableId() {
    return $this->getId();
  }

  // Method stub needed to make CustomItemNormalizer work. If no setter is.

  /**
   * Defined, tags will not be processed during normalization.
   */
  public function setTags($tags) {
  }

  /**
   * Returns the collection of tags for this Taggable entity.
   *
   * @return ArrayCollection
   */
  public function getTags() {
    $this->tags = $this->tags ?: new ArrayCollection();
    return $this->tags;
  }

}
