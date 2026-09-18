<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    private TranslatorInterface $translator;

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): TranslatorInterface
    {
        $translator = $this->translator;

        return $translator;
    }

    /**
     * Make sure to send the email notification in the preferred language of the user.
     */
    protected function setTranslatorLocale(Member $receiver): void
    {
        $language = $receiver->getPreferredLanguage();

        /** @var Translator $translator */
        $translator = $this->getTranslator();
        $translator->setLocale($language->getShortCode());

        $this->translator = $translator;
    }
}
