<?php

namespace Drupal\event_management\Form;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\ContentEntityForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


use Drupal\event_management\Event\EventCreated;
use Drupal\event_management\Event\EventUpdated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


use Drupal\Core\Entity\EntityTypeManagerInterface;


use Drupal\Core\Entity\EntityFormInterface;

/**
 * Form controller for adding and editing Event entities.
 */
class EventForm extends ContentEntityForm {
    protected $messenger;
    protected $eventDispatcher;
    protected $entityTypeManager;
  
    public function __construct(MessengerInterface $messenger,EventDispatcherInterface $event_dispatcher,EntityTypeManagerInterface $entity_type_manager) {
      $this->messenger = $messenger;
      $this->eventDispatcher = $event_dispatcher;
      $this->entityTypeManager= $entity_type_manager;
    }
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('messenger'),
        $container->get('event_dispatcher'),
        $container->get('entity_type.manager')
      );
    }
    public function buildForm(array $form, FormStateInterface $form_state, $entity = NULL) {
      // \Drupal::logger('event_management')->debug('Entity: <pre>@entity</pre>', ['@entity' => print_r($entity, TRUE)]);
      if (!$entity) {
        $this->messenger->addError($this->t('No entity available.'));
        return [];
      } else {
        $this->setEntity($entity);
      }
      $event = $this->entity;

   
    $form = parent::buildForm($form, $form_state);

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $event->get('title')->value,
      '#required' => TRUE,
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#default_value' => $event->get('image')->value,
      '#upload_location' => 'public://events/',
      '#default_value' => $event->get('image'),
      '#required' => FALSE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $event->get('description')->value,
    ];

    $form['start_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start Time'),
      '#default_value' => $event->get('start_time')->value,
      '#required' => TRUE,
    ];

    $form['end_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End Time'),
      '#default_value' => $event->get('end_time')->value,
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="start_time"]' => ['!value' => ''],
        ],
      ],
    ];

    $form['category'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Category'),
      '#default_value' => $event->get('category')->value,
    ];

    $form['published'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Published'),
      '#default_value' => $event->get('published')->value,
    ];

    return $form;
  }
  
 
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Custom validation: Ensure end time is after start time.
    $start_time = $form_state->getValue('start_time');
    $end_time = $form_state->getValue('end_time');

    if ($start_time && $end_time && strtotime($end_time) <= strtotime($start_time)) {
      $form_state->setErrorByName('end_time', $this->t('End Time must be after Start Time.'));
    }
  }

  public function save(array $form,FormStateInterface $form_state) {
    $event = $this->entity;
    $status = $event->save();

     // Dispatch custom event after saving.
    $event_id = $event->id();
    if ($status == SAVED_NEW) {
      $event_created = new EventCreated($event_id);
      $this->eventDispatcher->dispatch($event_created, EventCreated::EVENT_NAME);
      $this->messenger->addMessage($this->t('Created the %label event.', ['%label' => $event->label()]));
    } else {
      $event_updated = new EventUpdated($event_id);
      $this->eventDispatcher->dispatch($event_updated, EventUpdated::EVENT_NAME);
      $this->messenger->addMessage($this->t('Saved the %label event.', ['%label' => $event->label()]));
    }

    $form_state->setRedirect('entity.event.canonical', ['event' => $event->id()]);
  }
}

