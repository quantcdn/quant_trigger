<?php

namespace Drupal\quant_trigger\Event;

/**
 * Class QuantTriggerEvents.
 *
 * @package Drupal\quant_trigger
 */
final class QuantTriggerEvents {

  /**
   * Event name for request building.
   */
  const BUILD = 'quant_trigger.build';

  /**
   * Determine if a trigger is allowed tob e sent.
   */
  const ALLOWED = 'quant_trigger.allowed';

  /**
   * Name of the event that is emitted after the trigger has executed.
   */
  const SUCCESS = 'quant_trigger.success';

  /**
   * Event name for error cases.
   */
  const ERROR = 'quant_trigger.error';

}
