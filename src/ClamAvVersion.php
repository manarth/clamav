<?php

namespace Drupal\clamav;

/**
 * ClamAV version information.
 */
final class ClamAvVersion {

  // Example version string:
  // ClamAV 1.3.0/27189/Sun Feb 18 09:23:57 2024
  const VERSION_PATTERN = '@^ClamAV (\d+\.\d+\.\d+)/(\d+)/(.+)$@';

  // The date-time format used in the version signature.
  // For example: Sun Feb 18 09:23:57 2024
  const DATE_TIME_FORMAT = 'D M d H:i:s Y';

  /**
   * The version of the ClamAV application.
   *
   * @var string
   */
  protected string $version;

  /**
   * The version number of the ClamAV virus signatures
   *
   * @var string
   */
  protected string $signatureVersion;

  /**
   * The date when the virus signatures were compiled.
   *
   * @var \DateTimeInterface
   */
  protected \DateTimeInterface $signatureDate;

  /**
   * Constructor.
   *
   * @param string $versionRaw
   *   The raw version string provided by a ClamAV service.
   */
  public function __construct(public readonly string $versionRaw) {
    if (preg_match(self::VERSION_PATTERN, $versionRaw, $matches)) {
      $this->version          = $matches[1];
      $this->signatureVersion = $matches[2];
      // @todo Supply the timezone.
      $this->signatureDate    = \DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $matches[3]);
    }
  }

  /**
   * The version of the ClamAV application. This follows semantic versioning.
   *
   * @return string
   *   The version of the ClamAV application.
   */
  public function version() : ?string {
    return $this->version;
  }

  /**
   * The version number of the ClamAV virus signatures.
   *
   * @return string
   *   The version of the ClamAV virus signatures.
   */
  public function signatureVersion() : ?string {
    return $this->signatureVersion;
  }

  /**
   * The date when the virus signatures were compiled.
   *
   * @return \DateTimeInterface
   *   The date when the virus signatures were compiled.
   */
  public function signatureDate() : ?\DateTimeInterface {
    return $this->signatureDate;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->versionRaw;
  }

}
