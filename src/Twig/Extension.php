<?php

namespace App\Twig;

use App\Model\LanguageModel;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Registry;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var Registry
     */
    protected $registry;

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
     * @param SessionInterface $session
     * @param Registry $registry
     * @param TranslatorInterface $translator
     * @param $dataDirectory
     */
    public function __construct(SessionInterface $session, Registry $registry, TranslatorInterface $translator, $dataDirectory)
    {
        $this->session = $session;
        $this->registry = $registry;
        $this->translator = $translator;
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
     * @throws \HtmlTruncator\InvalidHtmlException
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
        $locale = $this->session->get('locale', 'en');
        $languageModel = new LanguageModel($this->registry);
        $languages = $languageModel->getLanguagesWithTranslations($locale);

        return [
            'version' => trim(file_get_contents('../VERSION')),
            'version_dt' => Carbon::createFromTimestamp(filemtime('../VERSION')),
            'title' => 'BeWelcome',
            'languages' => $languages,
            'robots' => 'ALL',
            'locale' => $locale,
        ];
    }
}
