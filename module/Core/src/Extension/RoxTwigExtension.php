<?php

namespace Rox\Core\Extension;

use Carbon\Carbon;
use Faker\Factory;
use Rox\I18n\Service\LanguageService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
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
            $lang = new \stdClass;
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
            'version'   => trim(file_get_contents('VERSION')),
            'version_dt' => Carbon::createFromTimestamp(filemtime('VERSION')),
            'title'     => 'BeWelcome',
            'faker' => class_exists(Factory::class) ? Factory::create() : null,
            'language'  => $defaultLanguage,
            'languages' => $langarr,
            'robots' => 'ALL',
        ];
    }
}
