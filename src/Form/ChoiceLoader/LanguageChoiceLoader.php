<?php

namespace App\Form\ChoiceLoader;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;
use Symfony\Component\OptionsResolver\Options;

class LanguageChoiceLoader extends AbstractChoiceLoader
{
    public function __construct(
        private readonly Options $options,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function loadChoices(): iterable
    {
        $languages = [];
        if ($this->options['written']) {
            $languages['languages.written'] = [];
        }
        if ($this->options['spoken']) {
            $languages['languages.spoken'] = [];
        }
        if ($this->options['signed']) {
            $languages['languages.signed'] = [];
        }

        $rawLanguages = $this->entityManager->getRepository(Language::class)->getAllLanguages();

        foreach ($rawLanguages as $language) {
            $shortCode = $language->getShortCode();
            if ($this->options['spoken'] && $language->isSpokenLanguage()) {
                $languages['languages.spoken'][$shortCode] = $language;
            } elseif ($this->options['signed'] && $language->isSignLanguage()) {
                $languages['languages.signed'][$shortCode] = $language;
            } elseif ($this->options['written'] && $language->isWrittenLanguage()) {
                $languages['languages.written'][$shortCode] = $language;
            }
        }

        return $languages;
    }
}
