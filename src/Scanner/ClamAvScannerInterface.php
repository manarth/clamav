<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus\ScannerInterface;
use Drupal\clamav\ClamAvVersion;

/**
 * Interface to be implemented by each ClamAV scanner.
 */
interface ClamAvScannerInterface extends ScannerInterface {

  /**
   * Get the ClamAV version for the remote ClamAV daemon.
   *
   * @return \Drupal\clamav\ClamAvVersion
   *   The version definition.
   */
  public function version() : ClamAvVersion;

}
