<?php 
namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends ControllerBase {

  /**
   * Returns a custom 404 error page.
   */
  public function custom404() {
    return new Response('<h1>Custom 404 Error Page</h1><p>Sorry, the page you are looking for could not be found.</p>', 404);
  }

  /**
   * Returns a custom 403 error page.
   */
  public function custom403() {
    return new Response('<h1>Custom 403 Error Page</h1><p>You do not have permission to access this page.</p>', 403);
  }
}
