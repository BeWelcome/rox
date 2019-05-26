<?php

namespace App\Utilities;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    /** @var TranslatorInterface */
    public $_translator;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setTranslator()
    {
        $this->_translator = TranslatorSingleton::getTranslator();
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->_translator;
    }
}
