<?php

namespace Drupal\quant_trigger\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * The trigger run entity.
 *
 * @ContentEntityType(
 *   id = "quant_trigger_run",
 *   label = @Translation("Quant trigger run"),
 *   base_table = "quant_trigger_run",
 *   entity_keys = {"id" = "id"}
 * )
 */
class QuantTriggerRun extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the run.'))
      ->setReadOnly(TRUE);

    $fields['trigger'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Trigger'))
      ->setDescription(t('The trigger that caused the run'))
      ->setSetting('target_type', 'quant_trigger')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $fields['details'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Details of the run'))
      ->setDescription(t('JSON string of the run details'));

    return $fields;
  }

  /**
   * Get details about the run.
   */
  public function getDetail($key) {
    $details = $this->get('details')->first();

    if (!$details) {
      return '';
    }

    $details = json_decode($details->getValue()['value'], TRUE);
    return isset($details[$key]) ? $details[$key] : '';
  }

}
