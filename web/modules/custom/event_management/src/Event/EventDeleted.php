<?php

namespace Drupal\event_management\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines the EventDeleted event.
 */
class EventDeleted extends Event {
  public const EVENT_NAME = 'event_management.event_deleted';

  protected $eventId;

  public function __construct($eventId) {
    $this->eventId = $eventId;
  }

  public function getEventId() {
    return $this->eventId;
  }
}
