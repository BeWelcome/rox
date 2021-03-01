<?php

namespace App\Twig;

use Carbon\Carbon;
use HtmlTruncator\InvalidHtmlException;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    /**
     * @var array list of all enabled locales
     */
    private $locales;

    /**
     * @var string
     */
    private $publicDirectory;

    /**
     * @var EntrypointLookupInterface
     */
    private $entrypointLookup;

    /**
     * Extension constructor.
     *
     * @param $locales
     * @param $dataDirectory
     * @param $publicDirectory
     */
    public function __construct(
        SessionInterface $session,
        TranslatorInterface $translator,
        EntrypointLookupInterface $entrypointLookup,
        $locales,
        $publicDirectory
    ) {
        $this->session = $session;
        $this->translator = $translator;
        $this->locales = explode(',', $locales);
        $this->entrypointLookup = $entrypointLookup;
        $this->publicDirectory = $publicDirectory;
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('ago', [$this, 'ago']),
            new TwigFunction('getTranslations', [$this, 'getTranslations']),
            new TwigFunction(
                'dump_it',
                [
                    $this,
                    'dumpIt',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'language_name',
                [
                    $this,
                    'languageName',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'language_name_translated',
                [
                    $this,
                    'languageNameTranslated',
                ],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function languageName(string $locale): string
    {
        $current = $this->translator->getLocale();
        $this->translator->setLocale($locale);
        $languageName = $this->translator->trans(strtolower('lang_' . $locale));
        $this->translator->setLocale($current);

        return $languageName;
    }

    public function languageNameTranslated(string $locale, string $display): string
    {
        $current = $this->translator->getLocale();
        $this->translator->setLocale($display);
        $languageName = $this->translator->trans(strtolower('lang_' . $locale));
        $this->translator->setLocale($current);

        return $languageName;
    }

    public function ago(Carbon $carbon)
    {
        return $carbon->diffForHumans();
    }

    public function getFilters()
    {
        return [
            new TwigFilter(
                'truncate',
                [$this, 'truncate'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFilter(
                'url_update',
                [$this, 'url_update'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    /**
     * Truncates a string up to a number of characters while preserving whole words and HTML tags.
     *
     * @param string $text     string to truncate
     * @param int    $length   length of returned string, including ellipsis
     * @param string $ellipsis
     *
     * @throws InvalidHtmlException
     *
     * @return string truncated string
     */
    public function truncate(string $text, $length = 100, $ellipsis = '&#8230;')
    {
        $truncator = new Truncator();
        $truncated = $truncator->truncate($text, $length, [
            'length_in_chars' => true,
            'ellipsis' => $ellipsis,
        ]);

        return $truncated;
    }


    /**
     * Removes domain name from all bewelcome links (www|beta|api) so that links work on all sub domains.
     *
     * @param string $text     string to truncate
     *
     * @return string updated string
     */
    public function url_update(string $text)
    {
        $text = preg_replace(
            '/src="http[s]?:\/\/[^\/]*?bewelcome\.org\//i',
            'src="/',
            $text
        );
        return preg_replace(
            '/href="http[s]?:\/\/[^\/]*?bewelcome\.org\//i',
            'href="/',
            $text
        );
    }

    public function dumpIt($variable)
    {
        return highlight_string(var_export($variable, true), true);
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $this->entrypointLookup->reset();
        $files = $this->entrypointLookup
            ->getCssFiles($entryName);
        $source = '';
        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDirectory . '/' . $file);
        }

        return $source;
    }

    public function getTranslations()
    {
        $collector = new TranslationDataCollector($this->translator);
        $collector->lateCollect();

        return [
            'collector' => $collector,
        ];
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

    public function getGlobals(): array
    {
        $version = '';
        $versionCreated = new Carbon();

        $locale = $this->session->get('locale', 'en');

        if (file_exists('../VERSION')) {
            $version = trim(file_get_contents('../VERSION'));
            $versionCreated = Carbon::createFromTimestamp(filemtime('../VERSION'));
        }

        return [
            'version' => $version,
            'version_dt' => $versionCreated,
            'title' => 'BeWelcome',
            'locales' => $this->locales,
            'robots' => 'ALL',
            'locale' => $locale,
        ];
    }
}
