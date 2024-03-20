<?php

namespace Drupal\clamav\Plugin\AntiVirus;

use Drupal\antivirus\Attribute\AntiVirus;
use Drupal\clamav\ScannerType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Anti-virus plugin to scan using a local ClamAV executable.
 */
#[AntiVirus(
  id: 'clamav:local_executable',
  admin_label: 'ClamAV: Local executable',
)]
class LocalExecutable extends ClamAvPluginBase implements PluginFormInterface {

  use StringTranslationTrait;

  const SCANNER = ScannerType::LOCAL_EXECUTABLE;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configuration['clamav_settings'] ?? [];

    $form['clamav_settings'] = [];

    $form['clamav_settings']['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#default_value' => $config['path'] ?? '',
      '#required' => TRUE,
    ];

    $form['clamav_settings']['parameters'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Parameters'),
      '#description' => $this->t('Optional parameters to pass to the clamscan executable, such as %example.', [
        '%example' => '--max-recursion=10',
      ]),
      '#default_value' => $config['parameters'] ?? '',
    ];

    return $form;
  }

}
