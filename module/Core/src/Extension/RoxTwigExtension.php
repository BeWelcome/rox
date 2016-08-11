<?php

namespace Rox\Core\Extension;

use Carbon\Carbon;
use Faker\Factory;
use HtmlTruncator\Truncator;
use Rox\I18n\Service\LanguageService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class RoxTwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var LanguageService
     */
    protected $languageService;

    public function __construct(
        SessionInterface $session,
        LanguageService $languageService
    ) {
        $this->session = $session;
        $this->languageService = $languageService;
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('ago', [$this, 'ago']),
        ];
    }

    public function ago(Carbon $carbon)
    {
        return $carbon->diffForHumans();
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter(
                'truncate',
                [$this, 'truncate'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    /**
     * Truncates a string up to a number of characters while preserving whole words and HTML tags
     *
     * @param string  $text         String to truncate.
     * @param integer $length       Length of returned string, including ellipsis.
     * @param string  $ending       Ending to be appended to the trimmed string.
     *
     * @return string Truncated string.
     */
    public function truncate($text, $length = 100, $ellipsis = '&#8230;')
    {
        $truncator = new Truncator();
        $truncated = $truncator->truncate($text, $length, [
            'length_in_chars' => true,
            'ellipsis' => $ellipsis,
        ]);
        return $truncated;
    }
    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'LayoutKit';
    }

    public function getGlobals()
    {
        $words = new \MOD_words();
        $languages = $this->languageService->getAvailableLanguages();
        $langarr = [];
        foreach ($languages as $language) {
            $lang = new \stdClass();
            $lang->NativeName = $language->Name;
            $lang->TranslatedName = $words->getSilent($language->WordCode);
            $lang->ShortCode = $language->ShortCode;
            $langarr[$language->ShortCode] = $lang;
        }
        $defaultLanguage = $langarr[$this->session->get('lang', 'en')];
        usort($langarr, function ($a, $b) {
            if ($a->TranslatedName === $b->TranslatedName) {
                return 0;
            }

            return (strtolower($a->TranslatedName) < strtolower($b->TranslatedName)) ? -1 : 1;
        });

        return [
            'version' => trim(file_get_contents('VERSION')),
            'version_dt' => Carbon::createFromTimestamp(filemtime('VERSION')),
            'title' => 'BeWelcome',
            'faker' => class_exists(Factory::class) ? Factory::create() : null,
            'language' => $defaultLanguage,
            'languages' => $langarr,
            'robots' => 'ALL',
        ];
    }
}
