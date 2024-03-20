<?php

namespace Drupal\clamav\Plugin\AntiVirus;

use Drupal\antivirus_core\Attribute\AntiVirus;
use Drupal\clamav\Scanner\TcpIpSocket as ScannerTcpIpSocket;
use Drupal\clamav\ScannerType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Anti-virus plugin which connects to a ClamAV daemon over a TCP/IP socket.
 */
#[AntiVirus(
  id: 'clamav:tcpipsocket',
  admin_label: 'ClamAV: TCP/IP socket',
)]
class TcpIpSocket extends ClamAvPluginBase implements PluginFormInterface {

  use StringTranslationTrait;

  const SCANNER = ScannerType::TCP_IP_SOCKET;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configuration['clamav_settings'] ?? [];

    $form['clamav_settings'] = [];

    $form['clamav_settings']['hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hostname'),
      '#default_value' => $config['hostname'] ?? ScannerTcpIpSocket::DEFAULT_HOSTNAME,
      '#required' => TRUE,
    ];

    $form['clamav_settings']['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#description' => $this->t('ClamAV often listens on TCP port @default_port.', [
        '@default_port' => ScannerTcpIpSocket::DEFAULT_PORT,
      ]),
      '#default_value' => $config['port'] ?? ScannerTcpIpSocket::DEFAULT_PORT,
      '#min' => 1,
      '#max' => 65535,
      '#required' => TRUE,
    ];

    return $form;
  }

}
