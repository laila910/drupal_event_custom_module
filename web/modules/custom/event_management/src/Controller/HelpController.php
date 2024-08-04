<?php

namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class HelpController
 *
 * Provides the help page for the Event Management module.
 */
class HelpController extends ControllerBase {

  /**
   * Returns the help page content.
   *
   * @return array
   *   A renderable array.
   */
  public function help() {
    return [
      '#markup' => $this->t('<h2>Event Management Help</h2><p>This module helps you manage events...</p>'),
    ];
  }

}
