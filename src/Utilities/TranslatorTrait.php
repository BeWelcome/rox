<?php

namespace App\Utilities;

use App\Entity\Language;
use App\Entity\Member;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    /** @var TranslatorInterface */
    private $translator1;

    /**
     * @Required
     *
     * @param TranslatorInterface $translator
     */
    public function humpdidumpdi(TranslatorInterface $translator)
    {
        $this->translator1 = $translator;
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->translator1;
    }

    /**
     * Make sure to sent the email notification in the preferred language of the user.
     *
     * @param Member $receiver
     */
    protected function setTranslatorLocale(Member $receiver)
    {
        $language = $receiver->getPreferredLanguage();
        $this->translator1->setLocale($language->getShortcode());
    }
}
