<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus\ScannerInterface;
use Drupal\clamav\Exception\DaemonUnreachableException;

/**
 * Scan with a ClamAV daemon over a TCP/IP socket.
 */
class LocalExecutable implements ClamAvScannerInterface {

}
