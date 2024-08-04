<?php

namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Query\SelectInterface;
use Symfony\Component\HttpFoundation\Response;


use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Controller for managing event CRUD operations.
 */
class EventController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Constructs an EventController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Display a form for creating a new event.
   *
   * @return array
   *   A renderable array representing the form to create a new event.
   */
  public function createEvent() {
    $entity = $this->entityTypeManager->getStorage('event')->create([]);
    $form = $this->entityTypeManager->getFormObject('event', 'add');

    if (!$entity) {
      throw new NotFoundHttpException();
    }else{
      $form->setEntity($entity);
    }

    return $this->formBuilder()->getForm($form);


  }
 

  /**
   * Display a list of events.
   *
   * @return array
   *   A renderable array representing the list of events.
   */
  public function listEvents() {
    $config = $this->config('event_management.settings');
    $events_per_page = $config->get('events_per_page');
    $show_past_events = $config->get('show_past_events');

    // Create the query for the events.
    $query = \Drupal::entityQuery('event')
      ->condition('published', TRUE)
      ->sort('start_time', 'ASC');

    if (!$show_past_events) {
      $query->condition('end_time', REQUEST_TIME, '>=');
    }


      // Explicitly set access check to FALSE for simplicity.
    // Set to TRUE if you want access checking.
    $query->accessCheck(FALSE);

    // Add pagination.
    $pager = $query->pager($events_per_page);
    $event_ids = $query->execute();
    
    // Load events based on IDs.
    $events = $this->entityTypeManager->getStorage('event')->loadMultiple($event_ids);

    $rows = [];
    foreach ($events as $event) {
      $rows[] = [
        'title' => $event->get('title')->value,
        'description' => $event->get('description')->value,
        'start_time' => date('Y-m-d H:i:s', $event->get('start_time')->value),
        'end_time' => date('Y-m-d H:i:s', $event->get('end_time')->value),
        'category' => $event->get('category')->value,
        'actions' => $this->buildActions($event->id()),
      ];
    }

    $header = [
      ['data' => $this->t('Title'), 'field' => 'title'],
      ['data' => $this->t('Description'), 'field' => 'description'],
      ['data' => $this->t('Start Time'), 'field' => 'start_time'],
      ['data' => $this->t('End Time'), 'field' => 'end_time'],
      ['data' => $this->t('Category'), 'field' => 'category'],
      ['data' => $this->t('Actions')],
    ];

    $build = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No events available.'),
      '#attached' => [
        'library' => [
          'event_management/event_management',
        ],
      ],
    ];

    // Add pager.
    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

/**
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   ...
 * )
 */
  private function buildActions($event_id) {
    return [
      'view' => [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => Url::fromRoute('event_management.event_view', ['event' => $event_id]),
      ],
      'edit' => [
        '#type' => 'link',
        '#title' => $this->t('Edit'),
        '#url' => Url::fromRoute('event_management.event_edit', ['event' => $event_id]),
      ],
      'delete' => [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => Url::fromRoute('event_management.event_delete', ['event' => $event_id]),
      ],
    ];
  }

  // Other methods as before...
  /**
   * View a single event.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return array
   *   A renderable array representing the event detail page.
   */
  public function viewEvent($event) {
    $event = $this->entityTypeManager->getStorage('event')->load($event);
    if (!$event) {
      throw new NotFoundHttpException();
    }
  
    $build = [
      '#theme' => 'event_detail',
      '#title' => $event->get('title'),
      '#image' => $event->get('image'),
      '#description' => $event->get('description'),
      '#start_time' => date('Y-m-d H:i:s', $event->get('start_time')),
      '#end_time' => date('Y-m-d H:i:s', $event->get('end_time')),
      '#category' => $event->get('category'),
    ];
  
    return $build;
  }

  /**
   * Edit an existing event.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return array
   *   A renderable array representing the form to edit an event.
   */
  public function editEvent($event_id) {
    $event = $this->entityTypeManager->getStorage('event')->load($event_id);
    if (!$event) {
      throw new NotFoundHttpException();
    }

    $form = $this->entityTypeManager->getFormObject('event', 'edit');
    $form->setEntity($event);
    return $this->formBuilder()->getForm($form);
  }


  /**
   * Delete an existing event.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return array
   *   A renderable array representing the form to confirm event deletion.
   */
  public function deleteEvent($event_id) {
    $event = $this->entityTypeManager->getStorage('event')->load($event_id);
    if (!$event) {
      throw new NotFoundHttpException();
    }

    $form = $this->entityTypeManager->getFormObject('event', 'delete');
    $form->setEntity($event);
    return $this->formBuilder()->getForm('Drupal\event_management\Form\EventDeleteForm', $event);
  }

  /**
   * Helper function to load an event by ID.
   *
   * @param int $event_id
   *   The event ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The event entity or NULL if not found.
   */
  // private function loadEvent($event_id) {
  //   return $this->entityTypeManager->getStorage('event')->load($event_id);
  // }
}
