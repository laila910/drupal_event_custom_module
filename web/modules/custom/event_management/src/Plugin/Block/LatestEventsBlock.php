<?php

namespace Drupal\event_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Latest Events' Block.
 *
 * @Block(
 *   id = "latest_events_block",
 *   admin_label = @Translation("Latest Events"),
 *   category = @Translation("Custom")
 * )
 */
class LatestEventsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query = \Drupal::database()->select('event', 'e');
    $query->fields('e', ['id', 'title']);
    $query->orderBy('id', 'DESC');
    $query->range(0, 5);
    $result = $query->execute();

    $events = [];
    foreach ($result as $record) {
      $events[] = [
        'id' => $record->id,
        'title' => $record->title,
      ];
    }

    return [
      '#theme' => 'latest_events_block',
      '#events' => $events,
    ];
  }
}
