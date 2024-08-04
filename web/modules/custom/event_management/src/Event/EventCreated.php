<?php

namespace Drupal\event_management\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines the EventCreated event.
 */
class EventCreated extends Event {
  public const EVENT_NAME = 'event_management.event_created';

  protected $eventId;

  public function __construct($eventId) {
    $this->eventId = $eventId;
  }

  public function getEventId() {
    return $this->eventId;
  }
}
