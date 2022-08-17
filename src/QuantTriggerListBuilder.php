<?php

namespace Drupal\quant_trigger;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Webhook entities.
 */
class QuantTriggerListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'label' => $this->t('Trigger'),
      'id' => $this->t('Machine name'),
      'status' => $this->t('Status'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildOperations(EntityInterface $entity) {
    return [
      '#type' => 'operations',
      '#links' => $this->getOperations($entity),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [
      'label' => $entity->label(),
      'id' => $entity->id(),
      'status' => $entity->status() ? $this->t('Active') : $this->t('Inactive'),
    ] + parent::buildRow($entity);
  }

}
