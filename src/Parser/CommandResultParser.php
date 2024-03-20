<?php

namespace Drupal\clamav\Parser;

use Drupal\antivirus\ScanOutcome;
use Drupal\antivirus\ScanResult;
use Drupal\antivirus\ScanResultInterface;
use Drupal\clamav\ClamAvScanSummary;

/**
 * Parse the CLI output of performing a clamscan command.
 */
class CommandResultParser {

  /**
   * The scan summary results, if available.
   */
  public readonly ClamAvScanSummary $summary;

  /**
   * Constructor.
   *
   * @param \Drupal\clamav\Parser\LocalExecutableCommandResult $result
   *   The result of performing a clamscan command.
   */
  public function __construct(
    public readonly LocalExecutableCommandResult $result,
  ) {
    if (str_contains($this->result->stdout, 'SCAN SUMMARY')) {
      $raw = $this->getSummaryString($this->result->stdout);
      $this->summary = ClamAvScanSummary::create($raw);
    }
  }

  /**
   * The result determined after parsing the CLI results.
   *
   * @return \Drupal\antivirus\ScanResultInterface
   *   The scan result output.
   */
  public function parse() : ScanResultInterface {
    if ($this->result->success()) {
      return $this->parseSuccess();
    }

    if ($this->result->isInfected()) {
      return $this->parseInfected();
    }

    return $this->parseError();
  }

  /**
   * Parse the output of a successful scan.
   *
   * @return \Drupal\antivirus\ScanResultInterface
   *   The scan result output.
   */
  protected function parseSuccess() : ScanResultInterface {
    return new ScanResult(ScanOutcome::CLEAN);
  }

  /**
   * Parse the output of a infected file.
   *
   * @return \Drupal\antivirus\ScanResultInterface
   *   The scan result output.
   */
  protected function parseInfected() : ScanResultInterface {
    $result = new ScanResult(ScanOutcome::INFECTED);
    if (preg_match('/([^:]+): ([^\n]+) FOUND\n/', $this->result->stdout, $matches)) {
      $result->setFilename($matches[1]);
      $result->setVirusName($matches[2]);
    }
    return $result;
  }

  /**
   * Parse the output of a clamscan error.
   *
   * @return \Drupal\antivirus\ScanResultInterface
   *   The scan result output.
   */
  protected function parseError() : ScanResultInterface {
    $result = new ScanResult(ScanOutcome::UNKNOWN);
    $result->setReason($this->result->stderr);
    return $result;
  }

  /**
   * Get the scan summary string from the raw process output.
   *
   * @param string $raw
   *   The output provided to `stdout`.
   *
   * @return string
   *   The section of the output containing the scan summary.
   */
  protected function getSummaryString(string $raw) {
    $start = strpos($raw, '----------- SCAN SUMMARY -----------');
    $end = $start;
    // The scan summary comprises 11 lines of output, including the prelude.
    for ($i = 0; $i < 11; $i++) {
      $end = strpos($raw, "\n", $end + 1);
    }
    return trim(substr($raw, $start, $end));
  }

}
