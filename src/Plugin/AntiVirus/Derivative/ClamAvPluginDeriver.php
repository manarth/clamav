<?php

namespace Drupal\clamav\Plugin\AntiVirus\Derivative;

use Drupal\clamav\ScannerType;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Derive a separate plugin for each of the 3 scanner types.
 */
class ClamAvPluginDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->scanners() as $key => $scanner_definition) {
      $this->derivatives[$key] = array_merge($base_plugin_definition, $scanner_definition);
    }
    return $this->derivatives;
  }

  /**
   * Get a list of the ClamAV scanner definitions.
   */
  protected function scanners() : array {
    $scanners = [];
    $scanners['tcpipsocket'] = [
      'admin_label' => $this->t('ClamAV: TCP/IP socket'),
      'scanner' => ScannerType::TCP_IP_SOCKET,
    ];
    $scanners['unixsocket'] = [
      'admin_label' => $this->t('ClamAV: unix socket'),
      'scanner' => ScannerType::UNIX_SOCKET,
    ];
    $scanners['local_executable'] = [
      'admin_label' => $this->t('ClamAV: local executable'),
      'scanner' => ScannerType::LOCAL_EXECUTABLE,
    ];
    return $scanners;
  }

}
