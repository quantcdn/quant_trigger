<?php

namespace Drupal\quant_trigger\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a trigger configuration entity type.
 *
 * @ConfigEntityType(
 *  id = "quant_trigger",
 *  label = @Translation("Quant trigger"),
 *  handlers = {
 *    "list_builder" = "Drupal\quant_trigger\QuantTriggerListBuilder",
 *    "form" = {
 *      "add" = "Drupal\quant_trigger\Form\TriggerAddForm",
 *      "edit" = "Drupal\quant_trigger\Form\TriggerAddForm",
 *      "delete" = "Drupal\quant_trigger\Form\TriggerDeleteForm",
 *    }
 *  },
 *  admin_permission = "administer quant triggers",
 *  config_prefix = "quant_trigger",
 *  entity_keys = {
 *    "id" = "id",
 *    "label" = "label",
 *    "uuid" = "uuid",
 *  },
 *  config_export = {
 *    "id",
 *    "label",
 *    "payload_url",
 *    "preview_url",
 *    "payload",
 *    "secret",
 *    "method",
 *    "headers",
 *    "events",
 *  },
 *  links = {
 *    "canonical" = "/admin/config/services/quant/trigger/{quant_trigger}",
 *    "add-form" = "/admin/config/services/quant/trigger/add",
 *    "edit-form" = "/admin/config/services/quant/trigger/{quant_trigger}/edit",
 *    "delete-form" = "/admin/config/services/quant/trigger/{quant_trigger}/delete",
 *    "collection" = "/admin/config/services/quant/trigger"
 *  }
 * )
 */
class QuantTrigger extends ConfigEntityBase {

  /**
   * The trigger id.
   *
   * @var string
   */
  protected $id;

  /**
   * The trigger label.
   *
   * @var string
   */
  protected $label;

  /**
   * The URL to notify.
   *
   * @var string
   */
  protected $payload_url;

  /**
   * The preview URL domain.
   *
   * @var string
   */
  protected $preview_url;

  /**
   * The trigger secret.
   *
   * @var string
   */
  protected $secret;

  /**
   * The request method.
   *
   * @var string
   */
  protected $method;

  /**
   * The data to send with the request.
   *
   * @var array
   */
  protected $payload;

  /**
   * The request headers.
   *
   * @var array
   */
  protected $headers;

  /**
   * The events listening on.
   *
   * @var array
   */
  protected $events;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    foreach (['events', 'headers', 'payload'] as $array_val) {
      if (isset($values[$array_val]) && is_string($values[$array_val])) {
        $this->{$array_val} = json_decode($values[$array_val], TRUE);
      }
    }

    if (empty($values['method'])) {
      $values['method'] = 'post';
    }
  }

  /**
   * Get the trigger id.
   *
   * @return string
   *   The trigger id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get the trigger label.
   *
   * @return string
   *   The trigger label.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Return the payload URL.
   *
   * @return string
   *   The payload url.
   */
  public function getPayloadUrl() {
    return $this->payload_url;
  }

  /**
   * Return the preview URL.
   *
   * @return string
   *   The preview url.
   */
  public function getPreviewUrl() {
    return $this->preview_url;
  }

  /**
   * Get the configured secret.
   *
   * @return string
   *   The trigger secret.
   */
  public function getSecret() {
    return $this->secret;
  }

  /**
   * The request method.
   *
   * @return string
   *   The request method.
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * Get the payload.
   *
   * @return array
   *   The configured payload.
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * Request headers.
   *
   * @return array
   *   The configured request headers.
   */
  public function getHeaders() {
    return $this->headers;
  }

  /**
   * Get the listening events.
   *
   * @return array
   *   The events configured.
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    foreach (['events', 'headers', 'payload'] as $arr_val) {
      if (is_array($this->{$arr_val})) {
        $value = array_filter($this->{$arr_val});
        $this->{$arr_val} = json_encode($value);
      }
    }
  }

}
