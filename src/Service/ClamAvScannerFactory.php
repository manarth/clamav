<?php

namespace Drupal\clamav\Service;

use Drupal\clamav\Scanner\ClamAvScannerInterface;
use Drupal\clamav\Scanner\LocalExecutable;
use Drupal\clamav\Scanner\TcpIpSocket;
use Drupal\clamav\Scanner\UnixSocket;
use Drupal\clamav\ScannerType;

/**
 * Factory service to create ClamAV scanners.
 */
class ClamAvScannerFactory implements ClamAvScannerFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createScanner(ScannerType $type, array $params) : ClamAvScannerInterface {
    return new $type->value(...$params);
  }

  /**
   * {@inheritdoc}
   */
  public function tcpIpSocket(string $hostname, int $port) : TcpIpSocket {
    return new (ScannerType::TCP_IP_SOCKET->value)($hostname, $port);
  }

  /**
   * {@inheritdoc}
   */
  public function unixSocket(string $socket) : UnixSocket {
    return new (ScannerType::UNIX_SOCKET->value)($socket);
  }

  /**
   * {@inheritdoc}
   */
  public function localExecutable(string $path) : LocalExecutable {
    return new (ScannerType::LOCAL_EXECUTABLE->value)($path);
  }

}
