<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus\ScannerInterface;
use Drupal\clamav\Exception\DaemonUnreachableException;

/**
 * Scan with a ClamAV daemon over a TCP/IP socket.
 */
class TcpIpSocket extends DaemonScanner implements ClamAvScannerInterface {

  /**
   * Constructor.
   *
   * @param string $hostname
   *   (optional) The host address of the ClamAV daemon. Defaults to localhost.
   * @param int $port
   *   (optional) The port number on which the ClamAV daemon is listening.
   *   Defaults to 3310.
   * @param int $timeout
   *   (optional) The time (in seconds) to wait for a connection to the daemon
   *   to succeed. Defaults to 5 seconds.
   */
  public function __construct(protected string $hostname = 'localhost',
                              protected int $port = 3310,
                              protected int $timeout = 5) {
  }

  /**
   * {@inheritdoc}
   */
  protected function getConnection() {
    $address = sprintf('tcp://%s:%d', $this->hostname, $this->port);
    $connection = stream_socket_client($address, $errno, $errstr, $this->timeout);
    if (!$connection) {
      throw new DaemonUnreachableException($errstr, $errno);
    }
    return $connection;
  }

}
