<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    /** @var Translator */
    private TranslatorInterface $translator;

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): Translator
    {
        /** @var Translator $translator */
        $translator = $this->translator;

        return $translator;
    }

    /**
     * Make sure to send the email notification in the preferred language of the user.
     */
    protected function setTranslatorLocale(Member $receiver): void
    {
        $language = $receiver->getPreferredLanguage();
        $this->translator->setLocale($language->getShortCode());
    }
}
