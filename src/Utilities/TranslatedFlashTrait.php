<?php

namespace App\Utilities;

/**
 * Trait TranslatedFlashTrait.
 *
 * Expects that TranslatorTrait is used as well
 */
trait TranslatedFlashTrait
{
    /**
     * @param $type
     * @param $message
     * @param mixed ...$params
     */
    protected function addTranslatedFlash($type, $message, ...$params)
    {
        $this->addFlash($type, $this->getTranslator()->trans($message, ...$params));
    }
}
