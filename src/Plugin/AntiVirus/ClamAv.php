<?php

namespace Drupal\clamav\Plugin\AntiVirus;

use Drupal\antivirus\Attribute\AntiVirus;
use Drupal\antivirus\PluginDefinition\AntiVirusPluginInterface;
use Drupal\antivirus\ScanResultInterface;
use Drupal\clamav\Plugin\AntiVirus\Derivative\ClamAvPluginDeriver;
use Drupal\clamav\Scanner\ClamAvScannerInterface;
use Drupal\clamav\Service\ClamAvScannerFactoryInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AntiVirus scanner plugin providing integration with ClamAV.
 */
#[AntiVirus(
  'clamav',
  ClamAvPluginDeriver::class
)]
class ClamAv extends PluginBase implements AntiVirusPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The ClamAV scanner service.
   *
   * @var \Drupal\clamav\Scanner\ClamAvScannerInterface
   */
  protected ClamAvScannerInterface $scanner;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClamAvScannerFactoryInterface $scanner_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->scanner = $scanner_factory->createScanner(
      $this->pluginDefinition['scanner'],
      $configuration['clamav_settings'] ?? []
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('clamav.scanner.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function scan(FileInterface $file) : ScanResultInterface {
    return $this->scanner->scan($file);
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() : bool {
    return $this->scanner->isAvailable();
  }

}
