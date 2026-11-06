<?php

namespace App\Service;

use Symfony\Component\Intl\Locales;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The names of the site's languages from the intl locale data (Symfony Intl): a language's own name ("Deutsch",
 * "Español", "Português (Brasil)") and its name in another language ("Deutsch" in English: "German"). The site's
 * codes become intl locales (pt-br → pt_BR, zh-hant → zh_Hant); a code intl doesn't know falls back to its
 * translation (lang_xx). The first letter is upper case, as in a list of languages.
 */
class LanguageNames
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /** the language's name in itself */
    public function native(string $locale): string
    {
        return $this->name($locale, $locale);
    }

    /** the language's name in the display language */
    public function in(string $locale, string $display): string
    {
        return $this->name($locale, $display);
    }

    private function name(string $locale, string $display): string
    {
        $code = $this->intlLocale($locale);
        $displayCode = $this->intlLocale($display);
        if (Locales::exists($code)) {
            $name = Locales::getName($code, Locales::exists($displayCode) ? $displayCode : 'en');
        } else {
            // trimmed: some language names were stored with a space at the end
            $name = trim($this->translator->trans(strtolower('lang_' . $locale), [], null, $display));
        }

        return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
    }

    /** pt-br → pt_BR, zh-hant → zh_Hant, de → de */
    private function intlLocale(string $locale): string
    {
        $parts = explode('-', str_replace('_', '-', $locale), 2);
        if (!isset($parts[1])) {
            return strtolower($parts[0]);
        }

        return strtolower($parts[0]) . '_' . (4 === \strlen($parts[1]) ? ucfirst(strtolower($parts[1])) : strtoupper($parts[1]));
    }
}
