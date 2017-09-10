<?php

namespace AppBundle\Twig;

use AppBundle\Model\LanguageModel;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Registry;
use HTMLPurifier_Config;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class Extension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        SessionInterface $session,
        Registry $registry
    ) {
        $this->session = $session;
        $this->registry = $registry;
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
            new Twig_SimpleFilter(
                'purify',
                [$this, 'purify'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    /**
     * Truncates a string up to a number of characters while preserving whole words and HTML tags.
     *
     * @param string $text string to truncate
     * @param int $length length of returned string, including ellipsis
     * @param string $ellipsis
     *
     * @return string truncated string
     * @throws \HtmlTruncator\InvalidHtmlException
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
     * Uses the HTMLPurifier to ensure safe HTML or display of messages and other user provided information.
     *
     * @param string $text string to truncate
     *
     * @return string purified string
     */
    public function purify($text)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.AutoParagraph', true);
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify(trim($text));
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
        ];
    }
}
