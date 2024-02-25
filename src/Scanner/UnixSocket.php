<?php

namespace Drupal\clamav\Scanner;

use Drupal\clamav\Exception\DaemonUnreachableException;

/**
 * Scan with a ClamAV daemon over a Unix socket connection.
 */
class UnixSocket extends DaemonScanner implements ClamAvScannerInterface {

  /**
   * Constructor.
   *
   * @param string $socket
   *   The filepath to the Unix socket attached to the ClamAV daemon.
   * @param int $timeout
   *   (optional) The time (in seconds) to wait for a connection to the daemon
   *   to succeed. Defaults to 5 seconds.
   */
  public function __construct(protected string $socket,
                              protected int $timeout = 5) {
  }

  /**
   * {@inheritdoc}
   */
  protected function getConnection() {
    $address = sprintf('unix://%s', $this->socket);
    $connection = stream_socket_client($address, $errno, $errstr, $this->timeout);
    if (!$connection) {
      throw new DaemonUnreachableException($errstr, $errno);
    }
    return $connection;
  }

}
