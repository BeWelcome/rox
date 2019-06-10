<?php

namespace App\Utilities;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorSingletonTrait
{
    /** @var TranslatorInterface */
    private $_translator;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setTranslatorSingleton()
    {
        $this->_translator = TranslatorSingleton::getTranslator();
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslatorSingleton()
    {
        return $this->_translator;
    }
}
