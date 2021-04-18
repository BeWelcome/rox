<?php

namespace App\Twig;

use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_HTML5Config;
use HTMLPurifier_TagTransform_Simple;
use HtmlTruncator\InvalidHtmlException;
use HtmlTruncator\Truncator;
use Psr\Log\LoggerInterface;
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
    protected SessionInterface $session;

    protected DataCollectorTranslator $translator;

    private string $publicDirectory;

    private EntrypointLookupInterface $entrypointLookup;
    private LoggerInterface $logger;

    /** @var false|string[] */
    private $locales;

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
        LoggerInterface $logger,
        string $locales,
        $publicDirectory
    ) {
        $this->session = $session;
        $this->translator = $translator;
        $this->locales = explode(',', $locales);
        $this->entrypointLookup = $entrypointLookup;
        $this->publicDirectory = $publicDirectory;
        $this->logger = $logger;
    }

    public function getFunctions(): array
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

    public function ago(Carbon $carbon): string
    {
        return $carbon->diffForHumans();
    }

    public function getFilters(): array
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
                [$this, 'urlUpdate'],
                [
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFilter(
                'prepare_newsletter',
                [$this, 'prepareNewsletter'],
                [
                    'is_safe' => ['html']
                ]
            ),
        ];
    }

    /**
     * Truncates a string up to a number of characters while preserving whole words and HTML tags.
     *
     * @param string $text     string to truncate
     * @param int    $length   length of returned string, including ellipsis
     * @param string $ellipsis the used ellipsis defaults to …
     *
     * @throws InvalidHtmlException
     */
    public function truncate(string $text, $length = 100, $ellipsis = '&#8230;'): string
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
     * @param string $text string to update
     */
    public function urlUpdate(string $text): string
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

    public function dumpIt($variable): string
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

    public function getTranslations(): array
    {
        $collector = new TranslationDataCollector($this->translator);
        $collector->lateCollect();

        return [
            'collector' => $collector,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function prepareNewsletter(string $text): string
    {
        $config = HTMLPurifier_HTML5Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p,b,a[href],br,i,u,strong,em,ol,ul,li,dl,dt,dd,img[src|alt|width|height],blockquote,del,'
            . 'figure[class],figcaption'
        );
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.Linkify', true);

        $purifier = new HTMLPurifier($config);
        $text = $purifier->purify($text);

        // now turn any figure/figcaption entries into <img>
        $result = preg_replace(
            '%<figure.*?><img.*?src="(.*?)".*?><figcaption>(.*?)</figcaption></figure>%',
            '<img width="480" height="320" src="\1" alt="\2">',
            $text
        );

        // Use the string 'embedded image' in case of no figcaption
        $embeddedImage = $this->translator->trans('newsletter.embedded.image');
        $result = preg_replace(
            '%<figure.*?><img.*?src="(.*?)".*?>%',
            '<img width="480" height="320" src="\1" alt="' . htmlentities($embeddedImage) . '">',
            $result
        );

        $this->logger->info($text);
        $this->logger->info($result);

        return $result;
    }

    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName(): string
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
