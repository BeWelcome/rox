<?php

namespace App\Exception;

use RuntimeException;

/**
 * Thrown when a newsletter has no translation of its subject or text for the receiver's language.
 */
class UntranslatedNewsletterException extends RuntimeException
{
}
