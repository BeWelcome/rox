<?php

namespace App\Utilities;

use Carbon\Carbon;

/**
 * Reads the short git revision and build timestamp from the project-root VERSION file.
 *
 * Lion (Deployer) and local dev still create this file via `make version`:
 *   git rev-parse --short HEAD > VERSION
 *   touch -d $(TIME_STAMP) VERSION
 *
 * Docker production images have no .git checkout, so CI bakes the same file at
 * image build time (see Dockerfile). Reading from %kernel.project_dir%/VERSION
 * works on both paths; the old ../VERSION relative path broke under FrankenPHP.
 */
final class VersionInfo
{
    public function __construct(private readonly string $projectDir)
    {
    }

    public function getShortHash(): string
    {
        $path = $this->getVersionFilePath();
        if (!is_readable($path)) {
            return '';
        }

        return trim((string) file_get_contents($path));
    }

    public function getBuiltAt(): Carbon
    {
        $path = $this->getVersionFilePath();
        if (!is_readable($path)) {
            return new Carbon();
        }

        return Carbon::createFromTimestamp((int) filemtime($path));
    }

    public function getFormattedForFeedback(): string
    {
        $hash = $this->getShortHash();
        if ('' === $hash) {
            return 'no version set';
        }

        return $hash . ' (' . $this->getBuiltAt() . ')';
    }

    private function getVersionFilePath(): string
    {
        return $this->projectDir . '/VERSION';
    }
}
