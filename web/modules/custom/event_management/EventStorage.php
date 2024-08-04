<?php 
namespace Drupal\event_management;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventStorage extends SqlContentEntityStorage implements ContentEntityStorageInterface {

  /**
   * Constructs a new EventStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  protected $entityType;
  protected $database;
  protected $entityFieldManager;

  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityType=$entity_type;
    $this->database=$database;
    $this->entityFieldManager=$entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager')
    );
  }

  // Implement additional methods required by the interface here.
  public function createWithSampleValues($bundle = FALSE, array $values = []){
    return true;
  }


}
