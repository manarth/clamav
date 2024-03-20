<?php

namespace Drupal\clamav\Exception;

/**
 * The connection to the Daemon address did not succeed.
 */
class DaemonUnreachableException extends \Exception implements ClamAvExceptionInterface {
}
