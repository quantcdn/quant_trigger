<?php

namespace Drupal\quant_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Quant Preview controller.
 *
 * Manages actions related to the live preview environments.
 */
class QuantPreview extends ControllerBase
{

  /**
   * Returns a redirect response if preview URL is set.
   */
  public static function redirectLivePreview(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {

    $node = $form_state->getFormObject()->getEntity();

    // Only save the node for the Save and Preview button. The regular Preview
    // button will show the last saved node revision.
    if ($form_state->getTriggeringElement()['#name'] == 'save_preview') {
      $node->save();
    }

    // Check there is a primary site to use.
    $primary_site = $node->get('field_primary_site');
    if ($primary_site->isEmpty()) {
      return;
    }

    // Get what we need from the node.
    $site_label = $primary_site->referencedEntities()[0]->label();
    $url = $node->toUrl()->toString();

    // Get the quant triggers to process.
    /** @var \Drupal\quant_trigger\TriggerService $trigger_service */
    $trigger_service = \Drupal::service('quant_trigger.service');
    $event = implode(':', ['entity', $node->getEntityType()->id(), 'create']);
    $triggers = $trigger_service->loadMultipleByEvent($event);

    // Go through all triggers to find the site to process.
    /** @var \Drupal\quant_trigger\Entity\QuantTrigger $trigger */
    foreach ($triggers as $trigger) {

      // Quant triggers expect a matching site name.
      if (stripos($site_label, $trigger->label()) === FALSE) {
        continue;
      }

      // Open the preview URL for this node.
      $preview = $trigger->getPreviewUrl();
      if (!empty($preview)) {
        $form_state->setResponse(new TrustedRedirectResponse($preview . $url, 302));
      }
    }

  }

}
