<?php

namespace Drupal\clamav\Plugin\AntiVirus;

use Drupal\antivirus\PluginDefinition\AntiVirusPluginInterface;
use Drupal\antivirus\ScanResultInterface;
use Drupal\clamav\Scanner\ClamAvScannerInterface;
use Drupal\clamav\ScannerType;
use Drupal\clamav\Service\ClamAvScannerFactoryInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base plugin for each of the ClamAV scanner plugins.
 */
abstract class ClamAvPluginBase extends PluginBase implements AntiVirusPluginInterface, ContainerFactoryPluginInterface {

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
      $this->getScannerType(),
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

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Fetch the type of ClamAV scanner used by this plugin.
   */
  protected function getScannerType() : ScannerType {
    return static::SCANNER;
  }

}
