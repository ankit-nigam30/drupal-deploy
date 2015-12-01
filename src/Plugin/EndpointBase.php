<?php

/**
 * @file
 * Contains \Drupal\deploy\Plugin\EndpointBase.
 */

namespace Drupal\deploy\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Base class for Endpoint plugins.
 */
Class EndpointBase extends PluginBase implements EndpointInterface, PluginFormInterface {

  /** @var string Uri scheme. */
  protected $scheme = '';
  /** @var string Uri user info. */
  protected $userInfo = '';
  /** @var string Uri host. */
  protected $host = '';
  /** @var int|null Uri port. */
  protected $port = 80;
  /** @var string Uri path. */
  protected $path = '';
  /** @var string Uri query string. */
  protected $query = '';
  /** @var string Uri fragment. */
  protected $fragment = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @inheritDoc
   */
  public function getScheme() {
    return $this->scheme;
  }

  /**
   * @inheritDoc
   */
  public function getAuthority() {
    if (empty($this->host)) {
      return '';
    }
    $authority = $this->host;
    if (!empty($this->userInfo)) {
      $authority = $this->userInfo . '@' . $authority;
    }
    if (!empty($this->port)) {
      $authority .= ':' . $this->port;
    }
    return $authority;
  }

  /**
   * @inheritDoc
   */
  public function getUserInfo() {
    return $this->userInfo;
  }

  /**
   * @inheritDoc
   */
  public function getHost() {
    return $this->host;
  }

  /**
   * @inheritDoc
   */
  public function getPort() {
    return $this->port;
  }

  /**
   * @inheritDoc
   */
  public function getPath() {
    return $this->path == null ? '' : $this->path;
  }

  /**
   * @inheritDoc
   */
  public function getQuery() {
    return $this->query;
  }

  /**
   * @inheritDoc
   */
  public function getFragment() {
    return $this->fragment;
  }

  /**
   * @inheritDoc
   */
  public function withScheme($scheme) {
    $new = clone $this;
    $new->scheme = $scheme;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function withUserInfo($user, $password = NULL) {
    $info = $user;
    if ($password) {
      $info .= ':' . $password;
    }

    $new = clone $this;
    $new->userInfo = $info;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function withHost($host) {
    $new = clone $this;
    $new->host = $host;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function withPort($port) {
    $new = clone $this;
    $new->port = $port;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function withPath($path) {
    $new = clone $this;
    $new->path = $path;
    return $new;// TODO: Implement withPath() method.
  }

  /**
   * @inheritDoc
   */
  public function withQuery($query) {
    $new = clone $this;
    $new->query = $query;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function withFragment($fragment) {
    $new = clone $this;
    $new->fragment = $fragment;
    return $new;
  }

  /**
   * @inheritDoc
   */
  public function __toString() {
    $uri = '';
    if (!empty($this->scheme)) {
      $uri .= $this->scheme . '://';
    }
    if (!empty($this->getAuthority())) {
      $uri .= $this->getAuthority();
    }
    if ($this->getPath() != null) {
      // Add a leading slash if necessary.
      if ($uri && substr($this->getPath(), 0, 1) !== '/') {
        $uri .= '/';
      }
      $uri .= $this->getPath();
    }
    if ($this->query != null) {
      $uri .= '?' . $this->query;
    }
    if ($this->fragment != null) {
      $uri .= '#' . $this->fragment;
    }
    return $uri;
  }

  /**
   * Apply parse_url parts to a URI.
   *
   * @param $parts Array of parse_url parts to apply.
   */
  protected function applyParts(array $parts) {
    $this->scheme = isset($parts['scheme'])
      ? $parts['scheme']
      : '';
    $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
    $this->host = isset($parts['host']) ? $parts['host'] : '';
    $this->port = !empty($parts['port'])
      ? $parts['port']
      : 80;
    $this->path = isset($parts['path'])
      ? $parts['path']
      : '';
    $this->query = isset($parts['query'])
      ? $parts['query']
      : '';
    $this->fragment = isset($parts['fragment'])
      ? $parts['fragment']
      : '';
    if (isset($parts['pass'])) {
      $this->userInfo .= ':' . $parts['pass'];
    }
  }

  /**
   * @inheritDoc
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * @inheritDoc
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * @inheritDoc
   */
  public function defaultConfiguration() {
    return [
      'username' => '',
      'password' => '',
    ];
  }

  /**
   * @inheritDoc
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['username'],
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => t('Password'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('username'))) {
      $form_state->setErrorByName('username', t('Username not set.'));
    }
    if (empty($form_state->getValue('password'))) {
      $form_state->setErrorByName('password', t('Password not set.'));
    }
  }

  /**
   * @inheritDoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['username'] = $form_state->getValue('username');
    $this->configuration['password'] = base64_encode($form_state->getValue('password'));
  }

}
