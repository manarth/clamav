<?php

namespace Drupal\clamav;

/**
 * Structured representation of the 'SCAN SUMMARY' output provided by ClamAV.
 */
class ClamAvScanSummary {

  // The date format used in the scan summary output.
  const DATE_FORMAT = 'Y:m:d H:i:s';

  /**
   * Constructor.
   *
   * @param int $knownViruses
   *   The number of viruses known to the definition database.
   * @param string $engineVersion
   *   The semantic version of the scanner engine.
   * @param int $scannedDirectories
   *   The number of directories scanned.
   * @param int $scannedFiles
   *   The number of files scanned.
   * @param int $infectedFiles
   *   The number of files found to be infected.
   * @param float $dataScanned
   *   The volume of data scanned, in MB.
   * @param string $dataRead
   *   The volume of data read, in MB.
   * @param float $time
   *   The duration that the scan took, in seconds.
   * @param DateTimeImmutable $startDate
   *   The time that the scan started.
   * @param DateTimeImmutable $endDate
   *   The time that the scan completed.
   */
  public function __construct(
    public readonly int $knownViruses,
    public readonly string $engineVersion,
    public readonly int $scannedDirectories,
    public readonly int $scannedFiles,
    public readonly int $infectedFiles,
    public readonly float $dataScanned,
    public readonly float $dataRead,
    public readonly float $time,
    public readonly \DateTimeImmutable $startDate,
    public readonly \DateTimeImmutable $endDate
  ) {
  }

  /**
   * Create a summary from the raw string output.
   *
   * @param string $raw
   *   The output provided by the scan summary.
   *
   * @return \Drupal\clamav\ClamAvScanSummary
   *   A scan summary with the data populated, or NULL if the output can not be
   *   parsed.
   */
  public static function create(string $raw) : ?static {
    if (preg_match(self::getFormat(), $raw, $matches)) {
      [,
        $knownViruses,
        $engineVersion,
        $scannedDirectories,
        $scannedFiles,
        $infectedFiles,
        $dataScanned,
        $dataRead,
        $time,
        $startDate,
        $endDate,
      ] = $matches;

      return new static(
        knownViruses : $knownViruses,
        engineVersion : $engineVersion,
        scannedDirectories : $scannedDirectories,
        scannedFiles : $scannedFiles,
        infectedFiles : $infectedFiles,
        dataScanned : $dataScanned,
        dataRead : $dataRead,
        time : $time,
        startDate : \DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $startDate),
        endDate: \DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $endDate),
      );
    }
  }

  /**
   * Regex pattern to parse the output provided by the scan summary.
   *
   * @return string
   *   A regex pattern to use with `preg_match()`.
   */
  protected static function getFormat() {
    $lines = [];
    $lines[] = '----------- SCAN SUMMARY -----------';
    $lines[] = '  Known viruses: (\d+)';
    $lines[] = '  Engine version: (.+)';
    $lines[] = '  Scanned directories: (\d+)';
    $lines[] = '  Scanned files: (\d+)';
    $lines[] = '  Infected files: (\d+)';
    $lines[] = "  Data scanned: (\d+\.?\d*) MB";
    $lines[] = "  Data read: (\d+\.?\d*) MB .+";
    $lines[] = "  Time: (\d+\.?\d*) sec .+";
    $lines[] = '  Start Date: (.+)';
    $lines[] = '  End Date:   (.+)';

    $pattern = implode("\n", $lines);
    $pattern = '/' . $pattern . '/';
    return $pattern;
  }

}
