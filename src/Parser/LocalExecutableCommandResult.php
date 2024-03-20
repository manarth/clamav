<?php

namespace Drupal\clamav\Parser;

/**
 * Result of running a command using a local executable.
 */
class LocalExecutableCommandResult {

  /**
   * Constructor.
   *
   * @param array $command
   *   The command and parameters used to invoke the process.
   * @param string $stdout
   *   The data provided by the scanner in `stdout`.
   * @param string $stderr
   *   The data provided by the scanner in `stderr`.
   * @param int $exitCode
   *   The exit code provided after running the process.
   */
  public function __construct(
    public readonly array $command,
    public readonly string $stdout,
    public readonly string $stderr,
    public readonly int $exitCode,
  ) {
  }

  /**
   * Check if the command succeeded.
   *
   * @return bool
   *   TRUE if the command was successful.
   */
  public function success() : bool {
    return $this->exitCode === 0;
  }

  /**
   * Check whether the file scanned is infected.
   *
   * @return bool
   *   TRUE if the file is infected.
   */
  public function isInfected() : bool {
    return $this->exitCode === 1;
  }

}
