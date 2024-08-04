<?php

namespace Drupal\event_management\EventSubscriber;

// use Drupal\Core\EventSubscriber\EventSubscriberInterface;
use Drupal\event_management\Event\EventCreated;
use Drupal\event_management\Event\EventUpdated;
use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      EventCreated::EVENT_NAME => 'onEventCreated',
      EventUpdated::EVENT_NAME => 'onEventUpdated',
    ];
  }

  public function onEventCreated(EventCreated $event) {
    // Handle the event created logic.
    $event_id = $event->getEventId();
    \Drupal::logger('event_management')->notice('Event created with ID: @id', ['@id' => $event_id]);
  }

  public function onEventUpdated(EventUpdated $event) {
    // Handle the event updated logic.
    $event_id = $event->getEventId();
    \Drupal::logger('event_management')->notice('Event updated with ID: @id', ['@id' => $event_id]);
  }
}
