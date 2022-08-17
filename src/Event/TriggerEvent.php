<?php

namespace Drupal\quant_trigger\Event;

use Drupal\quant_trigger\Entity\QuantTrigger;
use Symfony\Component\EventDispatcher\Event;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TriggerEvent.
 *
 * @package Drupal\quant_trigger
 */
class TriggerEvent extends Event {

  /**
   * The trigger config.
   *
   * @var \Drupal\quant_trigger\Entity\QuantTrigger
   */
  protected $config;

  /**
   * Event details.
   *
   * @var array
   */
  protected $context;


  /**
   * The event that is being fired.
   *
   * @var string
   */
  protected $event;

  /**
   * The payload destination.
   *
   * @var string
   */
  protected $url;

  /**
   * Payload to send with the request.
   *
   * @var array
   */
  protected $payload;

  /**
   * The request method.
   *
   * @var string
   */
  protected $method;

  /**
   * The request headers.
   *
   * @var array
   */
  protected $headers;

  /**
   * The response of the HTTP request.
   *
   * @var \Psr\Http\Message\ResponseInterface|null
   */
  protected $response;

  /**
   * Construct the trigger event.
   *
   * @param Drupal\quant_trigger\Entity\QuantTrigger $config
   *   Configuration for the trigger.
   * @param array $context
   *   Event context.
   */
  public function __construct(QuantTrigger $config, array $context) {
    $this->config = $config;
    $this->context = $context;
    $this->canSend = TRUE;
    $this->url = $config->getPayloadUrl();

    $this->event = isset($context['event']) ? $context['event'] : 'undef';

    $this->method = $config->getMethod();
    $this->headers = $config->getHeaders();
    $this->payload = $config->getPayload();

    if ($config->getSecret()) {
      $this->headers['Authorization'] = "Token {$config->getSecret()}";
    }

    $this->response = NULL;
  }

  /**
   * The config entity.
   *
   * @return Drupal\quant_trigger\Entity\QuantTrigger
   *   The trigger config.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * The event context.
   *
   * @return array
   *   The event context.
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * Get the triggering event.
   *
   * @return string
   *   The triggering event name.
   */
  public function getTriggeringEvent() {
    return $this->event;
  }

  /**
   * Fetch the payload URL.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * If this trigger can be sent.
   *
   * @return bool
   *   If the HTTP request can be sent or not.
   */
  public function canSend() {
    return (bool) $this->canSend;
  }

  /**
   * Disable the trigger and prevent the HTTP request.
   */
  public function disable() {
    $this->canSend = FALSE;
  }

  /**
   * Add data to the payload.
   *
   * @param string $key
   *   The key to add for the payload.
   * @param string $value
   *   The value to add to the payload.
   */
  public function addPayload($key, $value) {
    $this->payload[$key] = $value;
    return $this;
  }

  /**
   * Update the payload.
   *
   * @param array $payload
   *   The payload.
   */
  public function setPayload(array $payload) {
    $this->payload = $payload;
    return $this;
  }

  /**
   * Get the payload.
   *
   * @return array
   *   The construct payload.
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * Set request headers.
   *
   * @param string $header
   *   The request header.
   * @param string $value
   *   The header value.
   */
  public function addRequestHeader($header, $value) {
    $this->headers[$header] = $value;
    return $this;
  }

  /**
   * Get the request headers.
   *
   * @return array
   *   The request headers.
   */
  public function getHeaders() {
    $headers = $this->headers;
    if (empty($headers['Content-Type'])) {
      $headers['Content-Type'] = 'application/json';
    }
    return $headers;
  }

  /**
   * Set the request method.
   *
   * @param string $method
   *   The request method.
   */
  public function setMethod($method) {
    $allowed_methods = ['get', 'post', 'put', 'delete'];

    if (!in_array($method, $allowed_methods)) {
      throw new \Exception('Unsupported request method given.');
    }

    $this->method = $method;
    return $this;
  }

  /**
   * Get the request method.
   *
   * @return string
   *   The request method.
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * Set the result of the request.
   *
   * @param Psr\Http\Message\ResponseInterface $response
   *   The response of a request.
   */
  public function setResponse(ResponseInterface $response) {
    $this->response = $response;
    return $this;
  }

  /**
   * Get the response.
   *
   * @return Psr\Http\Message\ResponseInterface|NULL
   *   The response.
   */
  public function getResponse() {
    return $this->response;
  }

}
