<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus\ScanOutcome;
use Drupal\clamav\ClamAvVersion;
use Drupal\clamav\Exception\ClamAvException;
use Drupal\clamav\Exception\ClamAvNotFoundException;
use Drupal\antivirus\ScanResult;
use Drupal\antivirus\ScanResultInterface;
use Drupal\file\FileInterface;

/**
 * Base class for scanning using a ClamAV daemon.
 */
abstract class DaemonScanner {

  /**
   * Scan a file.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file to scan.
   *
   * @return \Drupal\clamav\ScanResultInterface
   *   The result of the scan.
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
    catch (ClamAvException $e) {
      return (new ScanResult(ScanOutcome::UNCHECKED))
        ->setReason($e->getMessage());
    }
  }

  /**
   * Test whether a connection to the ClamAV daemon can be made.
   *
   * @return bool
   *   TRUE if the ClamAV daemon is reachable.
   */
  public function isAvailable() : bool {
    try {
      $this->version();
      return TRUE;
    }
    catch (ClamAvException $e) {
      return FALSE;
    }
  }

  /**
   * Get the ClamAV version for the remote ClamAV daemon.
   *
   * @return \Drupal\clamav\ClamAvVersion
   *   The version definition.
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

    fwrite($scanner, "zINSTREAM\0");
    fwrite($scanner, pack("N", $file->getSize()));

    stream_copy_to_stream($fileStream, $scanner);

    // Send a zero-length block to indicate that we're done sending file data.
    fwrite($scanner, pack("N", 0));

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
