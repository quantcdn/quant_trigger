<?php

namespace Drupal\quant_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\quant_trigger\Entity\QuantTrigger;
use Symfony\Component\HttpFoundation\Request;

/**
 * View runs.
 */
class Runs extends ControllerBase {

  /**
   * View the runs.
   */
  public function view(QuantTrigger $quant_trigger, Request $request) {
    $storage = \Drupal::entityTypeManager()->getStorage('quant_trigger_run');
    $query = $storage->getQuery();
    $query->condition('trigger', $quant_trigger->id());
    $query->pager(10);
    $ids = $query->execute();

    $rows = [];

    /** @var \Drupal\quant_trigger\Entity\QuantTriggerRun $run */
    foreach ($storage->loadMultiple($ids) as $run) {
      $rows[] = [
        'id' => $run->id(),
        'status' => $run->getDetail('status'),
        'message' => $run->getDetail('message'),
      ];
    }

    $render['table'] = [
      '#type' => 'table',
      '#header' => ['Id', 'Status', 'Message'],
      '#rows' => $rows,
      '#empty' => $this->t('No recorded runs for this trigger'),
    ];

    $render['pager'] = ['#type' => 'pager'];
    return $render;

  }

}
