<?php

namespace App\Twig;

use App\Model\LanguageModel;
use Carbon\Carbon;
use HtmlTruncator\InvalidHtmlException;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
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
     * @var LanguageModel
     */
    protected $languageModel;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    /**
     * @var string location of the data directory (for purifier output)
     */
    private $dataDirectory;

    /**
     * Extension constructor.
     *
     * @param $dataDirectory
     */
    public function __construct(SessionInterface $session, TranslatorInterface $translator, LanguageModel $languageModel, $dataDirectory)
    {
        $this->session = $session;
        $this->translator = $translator;
        $this->languageModel = $languageModel;
        $this->dataDirectory = $dataDirectory;
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
            new TwigFunction('dump_it', [$this, 'dumpIt'], [
                'is_safe' => ['html'],
            ]),
        ];
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
    public function truncate($text, $length = 100, $ellipsis = '&#8230;')
    {
        $truncator = new Truncator();
        $truncated = $truncator->truncate($text, $length, [
            'length_in_chars' => true,
            'ellipsis' => $ellipsis,
        ]);

        return $truncated;
    }

    public function dumpIt($variable)
    {
        return highlight_string(var_export($variable, true), true);
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

    /**
     * @return array
     */
    public function getGlobals()
    {
        $version = '';
        $versionCreated = new Carbon();

        $locale = $this->session->get('locale', 'en');
        $languages = $this->languageModel->getLanguagesWithTranslations($locale);

        if (file_exists('../VERSION')) {
            $version = trim(file_get_contents('../VERSION'));
            $versionCreated = Carbon::createFromTimestamp(filemtime('../VERSION'));
        }

        return [
            'version' => $version,
            'version_dt' => $versionCreated,
            'title' => 'BeWelcome',
            'languages' => $languages,
            'robots' => 'ALL',
            'locale' => $locale,
        ];
    }
}
