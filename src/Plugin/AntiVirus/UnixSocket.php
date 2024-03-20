<?php

namespace Drupal\clamav\Plugin\AntiVirus;

use Drupal\antivirus\Attribute\AntiVirus;
use Drupal\clamav\ScannerType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Anti-virus plugin which connects to a ClamAV daemon over a unix socket.
 */
#[AntiVirus(
  id: 'clamav:unixsocket',
  admin_label: 'ClamAV: Unix socket',
)]
class UnixSocket extends ClamAvPluginBase implements PluginFormInterface {

  use StringTranslationTrait;

  const SCANNER = ScannerType::UNIX_SOCKET;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configuration['clamav_settings'] ?? [];

    $form['clamav_settings'] = [];

    $form['clamav_settings']['socket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Socket path'),
      '#description' => $this->t('Enter the socket location as an absolute file path.'),
      '#default_value' => $config['socket'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

}
