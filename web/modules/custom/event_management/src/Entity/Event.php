<?php

namespace Drupal\event_management\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   handlers = {
 *     "storage" = "Drupal\event_management\EventStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_management\EventListBuilder",
 *     "form" = {
 *       "add" = "Drupal\event_management\Form\EventForm",
 *       "edit" = "Drupal\event_management\Form\EventForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "access" = "Drupal\event_management\EventAccessControlHandler",
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *   },
 *   links = {
 *     "canonical" = "/event/{event}",
 *     "add-form" = "/event/add",
 *     "edit-form" = "/event/{event}/edit",
 *     "delete-form" = "/event/{event}/delete",
 *   },
 * )
 */

class Event extends ContentEntityBase{
    use StringTranslationTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
     ->setLabel(\Drupal::translation()->translate('Title'))
     ->setDescription(\Drupal::translation()->translate('The title of the event.'))
     ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
     ])
     ->setRequired(TRUE)
     ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
     ])
     ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
     ])
     ->setDisplayConfigurable('form', TRUE)
     ->setDisplayConfigurable('view', TRUE);

    $fields['image'] =  BaseFieldDefinition::create('image') // uri
     ->setLabel(\Drupal::translation()->translate('Image'))
     ->setRequired(TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
     ->setLabel(\Drupal::translation()->translate('Description'))
     ->setRequired(TRUE);

    $fields['start_time'] = BaseFieldDefinition::create('datetime')
     ->setLabel(\Drupal::translation()->translate('Start Time'))
     ->setRequired(TRUE);

    $fields['end_time'] = BaseFieldDefinition::create('datetime')
      ->setLabel(\Drupal::translation()->translate('End Time'))
      ->setRequired(true)
      ->setSetting('max_length', 20)
      ->addConstraint('DateTimeEndAfterStart');
    $fields['category'] =  BaseFieldDefinition::create('string')
     ->setLabel(\Drupal::translation()->translate('Category'))
     ->setRequired(TRUE);
    $fields['published'] = BaseFieldDefinition::create('boolean')
      ->setLabel(\Drupal::translation()->translate('Published'))
      ->setDefaultValue(true);

    return $fields;
  }
}
