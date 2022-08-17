<?php

namespace Drupal\quant_trigger\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TriggerAddFrom.
 *
 * @package Drupal\quant_trigger\Form
 */
class TriggerAddForm extends EntityForm {

  /**
   * Entity hooks that can be used.
   *
   * @var array
   */
  protected $entityHooks = [
    'create',
    'update',
    'delete',
  ];

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\quant_trigger\Entity\QuantTrigger $trigger */
    $trigger = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#tile' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $trigger->label(),
      '#description' => $this->t('Administration label for the trigger'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $trigger->id(),
      '#machine_name' => [
        'exists' => '\Drupal\quant_trigger\Entity\QuantTrigger::load',
      ],
      '#disabled' => !$trigger->isNew(),
    ];
    $form['secret'] = [
      '#type' => 'password',
      '#attributes' => [
        'placeholder' => $this->t('Secret'),
      ],
      '#title' => $this->t('Secret'),
      '#maxlength' => 255,
      '#default_value' => $trigger->getSecret(),
      '#description' => $this->t('A secret token to authenticate the outgoing request.'),
    ];
    $form['payload_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Payload URL'),
      '#attributes' => [
        'placeholder' => $this->t('http://example.com/post'),
      ],
      '#default_value' => $trigger->getPayloadUrl(),
      '#maxlength' => 255,
      '#description' => $this->t('Target URL for your payload.'),
    ];
    $form['preview_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Preview Domain URL'),
      '#attributes' => [
        'placeholder' => $this->t('https://content-preview.dea.ga.gov.au'),
      ],
      '#default_value' => $trigger->getPreviewUrl(),
      '#maxlength' => 255,
      '#description' => $this->t('Content Preview URL.'),
    ];
    $form['method'] = [
      '#type' => 'select',
      '#options' => array_combine(
        ['delete', 'get', 'post', 'put'],
        ['delete', 'get', 'post', 'put']
      ),
      '#description' => $this->t('The HTTP method to use in the request.'),
      '#default_value' => $trigger->getMethod() ?: 'post',
    ];
    $form['payload'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Request payload'),
      '#default_value' => $trigger->getPayload() ? json_encode($trigger->getPayload(), JSON_PRETTY_PRINT) : '',
      '#description' => $this->t('Specify body values to send with the request, use JSON notation.'),
    ];
    $form['headers'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Request Headers'),
      '#default_value' => $trigger->getPayload() ? json_encode($trigger->getHeaders(), JSON_PRETTY_PRINT) : '',
      '#description' => $this->t('Additional HTTP headers to add to the request, use JSON notation.'),
    ];
    $form['events'] = [
      '#title' => $this->t('Enabled Events'),
      '#type' => 'tableselect',
      '#header' => [
        'type' => 'Hook / Event',
        'event' => 'Machine name'
      ],
      '#description' => $this->t("The events you want to use to trigger a notification."),
      '#options' => $this->eventOptions(),
      '#default_value' => empty($trigger->getEvents()) ? [] : $trigger->getEvents(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Active"),
      '#description' => $this->t("Shows if the trigger is active or not."),
      '#default_value' => $trigger->isNew() ? TRUE : $trigger->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\quant_trigger\Entity\QuantTrigger $trigger */
    $trigger = $this->entity;
    if (empty($form_state->getValue('secret'))) {
      $trigger->set('secret', $form['secret']['#default_value']);
    }

    foreach (['headers', 'payload'] as $encoded_field) {
      if ($form_state->getValue($encoded_field)) {
        $trigger->set($encoded_field, json_decode($form_state->getValue($encoded_field), TRUE));
      }
    }

    $active = $trigger->save();
    switch ($active) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t(
          'Created the %label trigger.',
          [
            '%label' => $trigger->label(),
          ]
        ));
        break;

      default:
        $this->messenger()->addStatus($this->t(
          'Saved the %label trigger.',
          [
            '%label' => $trigger->label(),
          ]
        ));
    }
    /** @var \Drupal\Core\Url $url */
    $url = $trigger->toUrl('collection');
    $form_state->setRedirectUrl($url);
  }

  /**
   * Generate a list of available events.
   */
  protected function eventOptions() {
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $options = [];
    foreach ($entity_types as $entity_type => $definition) {
      if ($entity_type != 'node') {
        // Only support nodes at this stage.
        continue;
      }
      if ($definition->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface')) {
        foreach ($this->entityHooks as $hook) {
          $options['entity:' . $entity_type . ':' . $hook] = [
            'type' => $this->t('Hook: %entity_label', ['%entity_label' => ucfirst($definition->getLabel())]),
            'event' => 'entity:' . $entity_type . ':' . $hook,
          ];
        }
      }
    }

    return $options;
  }

}
