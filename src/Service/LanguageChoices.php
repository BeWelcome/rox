<?php

namespace App\Service;

use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * The languages of a language field (LanguageType) for the pages' own pickers (finish setup, spoken languages of the
 * profile): each with its own name (intl, "Français"), its name in the member's language for the search (lang_fr,
 * "French") and its group, spoken languages first and then sign languages, each by name. Not language.Name: some
 * of its native names are still HTML entities from the old site.
 */
final readonly class LanguageChoices
{
    public const string SPOKEN = 'spoken';
    public const string SIGNED = 'signed';

    public function __construct(
        private LanguageNames $languageNames,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<int|string, ChoiceView|ChoiceGroupView> $choices the field's choices (form.field.vars.choices)
     *
     * @return list<array{code: string, name: string, alt: string, group: string}>
     */
    #[AsTwigFunction('language_choices')]
    public function languageChoices(array $choices): array
    {
        $languages = [];
        foreach ($choices as $choice) {
            // the groups of LanguageChoiceLoader: languages.spoken, languages.signed
            $group = $choice instanceof ChoiceGroupView && 'languages.signed' === $choice->label ? self::SIGNED : self::SPOKEN;
            foreach ($choice instanceof ChoiceGroupView ? $choice->choices : [$choice] as $view) {
                if (!$view instanceof ChoiceView) {
                    continue;
                }
                $code = $view->value;
                $languages[] = [
                    'code' => $code,
                    'name' => $this->languageNames->native($code),
                    'alt' => $this->translator->trans(strtolower('lang_' . $code)),
                    'group' => $group,
                ];
            }
        }

        usort($languages, static fn (array $a, array $b): int => [self::SIGNED === $a['group'], $a['name']]
            <=> [self::SIGNED === $b['group'], $b['name']]);

        return $languages;
    }

    /**
     * The headings of the two groups in the search results.
     *
     * @return array{spoken: string, signed: string}
     */
    #[AsTwigFunction('language_groups')]
    public function languageGroups(): array
    {
        return [
            self::SPOKEN => strip_tags($this->translator->trans('languages.spoken')),
            self::SIGNED => strip_tags($this->translator->trans('languages.signed')),
        ];
    }
}
