<?php

namespace Drupal\clamav\Service;

use Drupal\clamav\Scanner\ClamAvScannerInterface;
use Drupal\clamav\Scanner\LocalExecutable;
use Drupal\clamav\Scanner\TcpIpSocket;
use Drupal\clamav\Scanner\UnixSocket;
use Drupal\clamav\ScannerType;

/**
 * An interface for ClamAV Scanner factories.
 */
interface ClamAvScannerFactoryInterface {

  /**
   * Create a ClamAV scanner.
   *
   * @param \Drupal\clamav\ScannerType $type
   *   The type of ClamAV scanner to create.
   * @param array $params
   *   The configuration, such as hostname or filepath.
   *
   * @return \Drupal\clamav\Scanner\ClamAvScannerInterface
   *   A ClamAV scanner.
   */
  public function createScanner(ScannerType $type, array $params) : ClamAvScannerInterface;

  /**
   * Create a ClamAV scanner which connects over a TCP/IP socket.
   *
   * @param string $hostname
   *   The hostname of the ClamAV service.
   * @param int $port
   *   The port on which the ClamAV service is listening.
   *
   * @return \Drupal\clamav\Scanner\TcpIpSocket
   *   A TcpIpSocket ClamAV scanner.
   */
  public function tcpIpSocket(string $hostname, int $port) : TcpIpSocket;

  /**
   * Create a ClamAV scanner which connects over a unix socket.
   *
   * @param string $socket
   *   The filepath to the ClamAV unix socket.
   *
   * @return \Drupal\clamav\Scanner\UnixSocket
   *   A UnixSocket ClamAV scanner.
   */
  public function unixSocket(string $socket) : UnixSocket;

  /**
   * Create a ClamAV scanner which uses a local executable.
   *
   * @param string $path
   *   The filepath to the ClamAV executable.
   * @param string $parameters
   *   (optional) Parameters to pass to the ClamAV executable.
   *
   * @return \Drupal\clamav\Scanner\LocalExecutable
   *   A LocalExecutable ClamAV scanner.
   */
  public function localExecutable(string $path, string $parameters = '') : LocalExecutable;

}
