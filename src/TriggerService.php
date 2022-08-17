<?php

namespace Drupal\quant_trigger;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\quant_trigger\Entity\QuantTrigger;
use Drupal\quant_trigger\Entity\QuantTriggerRun;
use Drupal\quant_trigger\Event\QuantTriggerEvents;
use Drupal\quant_trigger\Event\TriggerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Class TriggerService.
 *
 * @package Drupal\quant_trigger
 */
class TriggerService {

  /**
   * The HTTP client with which to send requests.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $storage;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * TriggerService constructor.
   */
  public function __construct(
    Client $client,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $dispatcher,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->client = $client;
    $this->storage = $entity_type_manager->getStorage('quant_trigger');
    $this->dispatcher = $dispatcher;
    $this->logger = $logger_factory->get('quant_trigger');
  }

  /**
   * Retrieve triggers based on events.
   *
   * @return [Drupal\Core\Entity\EntityStorageInterface]
   *   Matching triggers.
   */
  public function loadMultipleByEvent($event) {
    $query = $this->storage->getQuery()
      ->condition('status', 1)
      ->condition('events', $event, 'CONTAINS');
    $ids = $query->execute();
    return $this->storage->loadMultiple($ids);
  }

  /**
   * Dispatch the trigger to the remote service.
   */
  public function send(QuantTrigger $trigger_config, TriggerEvent $trigger_event) {
    if (!$trigger_config->status()) {
      // This trigger is not active - so we skip dispatching events.
      return;
    }

    $this->dispatcher->dispatch(
      QuantTriggerEvents::BUILD,
      $trigger_event,
    );

    $this->dispatcher->dispatch(
      QuantTriggerEvents::ALLOWED,
      $trigger_event
    );

    if (!$trigger_event->canSend()) {
      $this->logger->notice('Send disabled. Config (%id) for %event', [
        '%id' => $trigger_config->id(),
        '%event' => $trigger_event->getTriggeringEvent(),
      ]);
      return;
    }

    $request = new Request(
      $trigger_event->getMethod(),
      $trigger_event->getUrl(),
      $trigger_event->getHeaders(),
      json_encode($trigger_event->getPayload())
    );

    try {
      $response = $this->client->send($request);
    }
    catch (\Exception $e) {
      $this->logger->error('Trigger Failed: Subscriber %event on trigger %id failed: @message', [
        '%event' => $trigger_event->getTriggeringEvent(),
        '%id' => $trigger_config->id(),
        '@message' => $e->getMessage(),
      ]);
      $this->dispatcher->dispatch(
        QuantTriggerEvents::ERROR,
        $trigger_event
      );
      $this->saveRunInfo($trigger_config, [
        'status' => 0,
        'message' => $e->getMessage(),
      ]);
      return FALSE;
    }

    $trigger_event->setResponse($response);

    $this->saveRunInfo($trigger_config, [
      'status' => 1,
      'message' => 'Trigger dispatch successful',
      'status' => $response->getStatusCode(),
      'response' => (string) $response->getBody(),
    ]);

    $this->dispatcher->dispatch(
      QuantTriggerEvents::SUCCESS,
      $trigger_event
    );

  }

  /**
   * Save a run for the trigger.
   *
   * @param \Drupal\quant_trigger\Entity\QuantTrigger $config
   *   The trigger config for the run.
   * @param array $details
   *   Any details to save with the run.
   */
  public function saveRunInfo(QuantTrigger $config, array $details = []) {
    $details['timestamp'] = strtotime('now');
    $run = QuantTriggerRun::create([
      'trigger' => $config->id(),
      'details' => json_encode($details),
    ]);
    $run->save();
  }

}
