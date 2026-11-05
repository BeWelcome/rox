<?php

namespace App\Wiki;

use RuntimeException;

/**
 * Someone saved a newer version of the page while it was being edited.
 */
final class WikiConflictException extends RuntimeException
{
    public function __construct(
        public readonly int $latestVersion,
    ) {
        parent::__construct(\sprintf('The page has version %d by now.', $latestVersion));
    }
}
