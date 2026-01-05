<?php

namespace App\Utilities;

use InvalidArgumentException;

/**
 * Trait TranslatedFlashTrait.
 *
 * Expects that TranslatorTrait is used as well
 */
trait TranslatedFlashTrait
{
    protected function addTranslatedFlash($type, $message, ...$params): void
    {
        if (method_exists($this, 'getTranslator')) {
            $this->addFlash($type, $this->getTranslator()->trans($message, ...$params));
        } elseif (property_exists($this, 'translator')) {
            $this->addFlash($type, $this->translator->trans($message, ...$params));
        } else {
            throw new InvalidArgumentException('getTranslator method does not exist');
        }
    }
}
