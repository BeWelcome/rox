<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    /** @var TranslatorInterface */
    private $translator;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Make sure to sent the email notification in the preferred language of the user.
     */
    protected function setTranslatorLocale(Member $receiver): void
    {
        $language = $receiver->getPreferredLanguage();
        $this->translator->setLocale($language->getShortCode());
    }
}
