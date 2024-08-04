<?php

namespace Drupal\event_management\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventDeleteForm extends ContentEntityDeleteForm {
  protected $currentUser;

   /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_delete_form';
  }
  
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Custom message for delete confirmation.
    $form['confirm'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Are you sure you want to delete the event "%label"?', ['%label' => $this->entity->label()]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    
    if ($this->entity) {
      $entity = $this->entity;
      $entity->delete();

      // Get the current user ID
      $current_user_id = $this->currentUser->id();

      // Log deletion in the event_management_log table.
    if ($this->config('event_management.settings')->get('log_changes')) {
      $message = $this->t('Deleted Successfully the %label Event.', [
        '%label' => $entity->label(),
      ]);
      \Drupal::database()->insert('event_management_log')
        ->fields([
          'operation'=>'Delete Event',
          'details' => $message,
          'timestamp' => \Drupal::time()->getRequestTime(),
          'user' => $current_user_id, // Make sure to provide a value for the 'user' column
      ])
      ->execute();
    }
    
     
    // Redirect after deletion.
      $form_state->setRedirect('entity.event.collection');

    }else{
          \Drupal::messenger()->addError($this->t('The event could not be deleted.'));
    }
  }
}


