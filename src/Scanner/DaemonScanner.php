<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus_core\ScanOutcome;
use Drupal\antivirus_core\ScanResult;
use Drupal\antivirus_core\ScanResultInterface;
use Drupal\clamav\ClamAvVersion;
use Drupal\clamav\Exception\ClamAvExceptionInterface;
use Drupal\clamav\Exception\ClamAvNotFoundException;
use Drupal\file\FileInterface;

/**
 * Base class for scanning using a ClamAV daemon.
 */
abstract class DaemonScanner {

  /**
   * ClamAV commands prefixed by 'z' are terminated by a null (\0) character.
   *
   * ClamAV commands prefixed by 'n' are terminated by a new line character,
   * which is not used in this module.
   *
   * @var string
   */
  const NULL_TERMINATED = 'z';

  /**
   * Provide a chunk data-size as a 32-bit unsigned integer.
   *
   * This must match ClamAV's expectation as an INSTREAM command parameter.
   *
   * @var string
   */
  const CHUNK_SIZE_32_BIT_UNSIGNED_INT = 'N';

  /**
   * ClamAV command to start streaming data.
   *
   * @var string
   */
  const START_INSTREAM = self::NULL_TERMINATED . 'INSTREAM' . "\0";

  /**
   * {@inheritdoc}
   */
  public function scan(FileInterface $file) : ScanResultInterface {
    try {
      $response = $this->doScan($file);
      if ($response === 'stream: OK') {
        return new ScanResult(ScanOutcome::CLEAN);
      }

      if (preg_match('/^stream: (.*) FOUND$/', $response, $matches)) {
        return (new ScanResult(ScanOutcome::INFECTED))
          ->setVirusName($matches[1]);
      }

      if (preg_match('/^stream: (.*) ERROR$/', $response, $matches)) {
        return (new ScanResult(ScanOutcome::UNCHECKED))
          ->setReason($matches[1]);
      }

      return (new ScanResult(ScanOutcome::UNKNOWN))
        ->setReason('Could not parse the scan result');
    }
    catch (ClamAvExceptionInterface $e) {
      return (new ScanResult(ScanOutcome::UNCHECKED))
        ->setReason($e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() : bool {
    try {
      $this->version();
      return TRUE;
    }
    catch (ClamAvExceptionInterface $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function version() : ClamAvVersion {
    $connection = $this->getConnection();
    fwrite($connection, "VERSION\n");
    $version = trim(fgets($connection));
    fclose($connection);
    if (empty($version)) {
      throw new ClamAvNotFoundException('ClamAV daemon not found.');
    }
    return new ClamAvVersion($version);
  }

  /**
   * Perform a scan.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to scan.
   *
   * @return string
   *   The text response from the ClamAV scanner.
   */
  protected function doScan(FileInterface $file) : string {
    $scanner = $this->getConnection();
    $fileStream = fopen($file->getFileUri(), 'r');

    fwrite($scanner, self::START_INSTREAM);
    fwrite($scanner, pack(self::CHUNK_SIZE_32_BIT_UNSIGNED_INT, $file->getSize()));

    stream_copy_to_stream($fileStream, $scanner);

    // Send a zero-length block to indicate that we're done sending file data.
    fwrite($scanner, pack(self::CHUNK_SIZE_32_BIT_UNSIGNED_INT, 0));

    $response = trim(fgets($scanner));
    fclose($scanner);
    fclose($fileStream);
    return $response;
  }

  /**
   * Open a stream connection to the ClamAV daemon.
   *
   * @return resource
   *   A stream resource connected to the ClamAV daemon.
   */
  abstract protected function getConnection();

}
