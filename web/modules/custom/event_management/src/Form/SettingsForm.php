<?php

namespace Drupal\event_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;

class SettingsForm extends ConfigFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  protected $currentUser;


  /**
   * Constructs a new SettingsForm.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database,AccountInterface $current_user) {
    $this->database = $database;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_management.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_management_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // config
    $config = $this->config('event_management.settings');

     // Add help link.
     $url = Url::fromRoute('event_management.help');
     $link = Link::fromTextAndUrl($this->t('Help'), $url)->toString();

     $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<div class="help-link">' . $link . '</div>',
      '#weight' => -10,
    ];

    // Add permission icon.
    $url = Url::fromRoute('user.admin_permissions'); // route  /admin/people/permissions
    $link = Link::fromTextAndUrl($this->t('Permissions'), $url)->toString();
    $icon = '<span class="permission-icon"></span>';
    $link_with_icon = $icon . ' ' . $link;

    $form['permissions'] = [
      '#type' => 'markup',
      '#markup' => '<div class="permissions-link">' . $link_with_icon . '</div>',
      '#weight' => -10,
    ];

    $form['#attached']['library'][] = 'event_management/event_management';
    

    $form['show_past_events'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show past events'),
      '#default_value' => $config->get('show_past_events'),
    ];

    $form['events_per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of events to list per page'),
      '#default_value' => $config->get('events_per_page'),
      '#min' => 1
    ];

    $form['log_changes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log changes'),
      '#default_value' => $config->get('log_changes'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

      // Get the current user ID
      $current_user_id = $this->currentUser->id();
      
    $this->config('event_management.settings')
      ->set('show_past_events', $form_state->getValue('show_past_events'))
      ->set('events_per_page', $form_state->getValue('events_per_page'))
      ->set('log_changes', $form_state->getValue('log_changes'))
      ->save();

    if ($form_state->getValue('log_changes')) {
      $message = $this->t('Configuration settings updated.');
      $this->database->insert('event_management_log')
        ->fields([
          'operation'=>'Configuration Update or Create',
          'details' => $message,
          'timestamp' => \Drupal::time()->getRequestTime(),//REQUEST_TIME
          'user' => $current_user_id, // Make sure to provide a value for the 'user' column
        ])
        ->execute();
    }
    parent::submitForm($form, $form_state);
  }
}

