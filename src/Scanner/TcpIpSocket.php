<?php

namespace Drupal\clamav\Scanner;

use Drupal\clamav\Exception\DaemonUnreachableException;

/**
 * Scan with a ClamAV daemon over a TCP/IP socket.
 */
class TcpIpSocket extends DaemonScanner implements ClamAvScannerInterface {

  /**
   * By default, connect to a scanner running on the same host.
   */
  const DEFAULT_HOSTNAME = 'localhost';

  /**
   * ClamAV's default port is 3310.
   */
  const DEFAULT_PORT = 3310;

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
  public function __construct(protected string $hostname = self::DEFAULT_HOSTNAME,
                              protected int $port = self::DEFAULT_PORT,
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
