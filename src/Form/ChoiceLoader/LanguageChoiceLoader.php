<?php

namespace App\Form\ChoiceLoader;

use App\Entity\Language;
use App\Service\LanguageNames;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;
use Symfony\Component\OptionsResolver\Options;

class LanguageChoiceLoader extends AbstractChoiceLoader
{
    public function __construct(
        private readonly Options $options,
        private readonly EntityManagerInterface $entityManager,
        private readonly LanguageNames $languageNames,
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

        // each group by the languages' own names, which the field shows (LanguageType's choice_label)
        foreach ($languages as $group => $groupLanguages) {
            uasort($groupLanguages, fn (Language $a, Language $b): int => $this->languageNames->native($a->getShortCode())
                <=> $this->languageNames->native($b->getShortCode()));
            $languages[$group] = $groupLanguages;
        }

        return $languages;
    }
}
