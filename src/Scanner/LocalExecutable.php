<?php

namespace Drupal\clamav\Scanner;

use Drupal\antivirus\ScanResultInterface;
use Drupal\clamav\ClamAvVersion;
use Drupal\clamav\Exception\ClamAvExceptionInterface;
use Drupal\clamav\Exception\ClamAvNotFoundException;
use Drupal\clamav\Parser\CommandResultParser;
use Drupal\clamav\Parser\LocalExecutableCommandResult;
use Drupal\file\FileInterface;

/**
 * Scan with a ClamAV daemon over a TCP/IP socket.
 */
class LocalExecutable implements ClamAvScannerInterface {

  /**
   * Constructor.
   *
   * @param string $path
   *   File path to the clamscan executable.
   * @param string $parameters
   *   (optional) Additional parameters to pass to the executable.
   */
  public function __construct(protected string $path = '',
                              protected string $parameters = '') {
  }

  /**
   * {@inheritdoc}
   */
  public function scan(FileInterface $file) : ScanResultInterface {
    $params = ($this->parameters)
      ? explode(' ', $this->parameters)
      : [];

    $stream = fopen($file->getFileUri(), 'r');
    $cliResult = $this->exec($params, $stream);
    fclose($stream);

    $parser = new CommandResultParser($cliResult);
    $outcome = $parser->parse();

    return $outcome;
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
    $result = $this->exec(['-V']);
    if ($result->success()) {
      return new ClamAvVersion($result->stdout);
    }
  }

  /**
   * Execute a command using the clamscan executable.
   *
   * @param array $parameters
   *   List of parameters to pass to the clamscan executable.
   * @param resource $stream
   *   (optional) Set to a stream resource if piping directly to `stdin`.
   *
   * @return \Drupal\clamav\Parser\LocalExecutableCommandResult
   *   The results of executing the given command.
   */
  protected function exec(array $parameters, $stream = NULL) : LocalExecutableCommandResult {
    if (!file_exists($this->path)) {
      throw new ClamAvNotFoundException('The clamscan executable was not found.');
    }

    if (!is_executable($this->path)) {
      throw new ClamAvNotFoundException('The clamscan executable does not allow execution. Please check permissions.');
    }

    // The stdin, stdout and stderr file descriptors.
    $descriptor_spec = [
      0 => ['pipe', 'r'],
      1 => ['pipe', 'w'],
      2 => ['pipe', 'w'],
    ];

    array_unshift($parameters, $this->path);

    if (!empty($stream)) {
      // The '-' parameter indicates to read from `stdin`.
      $parameters[] = '-';
    }

    $process = proc_open(
      command: $parameters,
      descriptor_spec: $descriptor_spec,
      pipes: $pipes
    );

    if (!empty($stream)) {
      stream_copy_to_stream($stream, $pipes[0]);
      fclose($pipes[0]);
    }

    return new LocalExecutableCommandResult(
      command: $parameters,
      stdout: $this->readResource($pipes[1]),
      stderr: $this->readResource($pipes[2]),
      exitCode: proc_close($process),
    );
  }

  /**
   * Read the data provided by a stream until it reaches the end of the stream.
   *
   * @param resource $resource
   *   An opened stream.
   *
   * @return string
   *   The contents of the stream (with leading and trailing whitespace and
   *   new-lines removed).
   */
  protected function readResource($resource) : string {
    $result = '';
    while ($data = fread($resource, 1024)) {
      $result .= $data;
    }

    // Remove leading and trailing whitespace and new-lines.
    return trim($result);
  }

}
