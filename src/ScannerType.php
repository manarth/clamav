<?php

namespace Drupal\clamav;

use Drupal\clamav\Scanner\LocalExecutable;
use Drupal\clamav\Scanner\TcpIpSocket;
use Drupal\clamav\Scanner\UnixSocket;

/**
 * Types of ClamAV scanner and their implementing class.
 */
enum ScannerType : string {
  case TCP_IP_SOCKET = TcpIpSocket::class;
  case UNIX_SOCKET = UnixSocket::class;
  case LOCAL_EXECUTABLE = LocalExecutable::class;
}
