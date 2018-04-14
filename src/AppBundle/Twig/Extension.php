<?php

namespace AppBundle\Twig;

use AppBundle\Model\LanguageModel;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Registry;
use HTMLPurifier_Config;
use HtmlTruncator\Truncator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Component\Translation\DataCollectorTranslator;
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

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    public function __construct(
        SessionInterface $session,
        Registry $registry,
        DataCollectorTranslator $translator
    ) {
        $this->session = $session;
        $this->registry = $registry;
        $this->translator = $translator;
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
            new Twig_SimpleFunction('getTranslations', [$this, 'getTranslations']),
            new Twig_SimpleFunction('dump_it', [$this, 'dumpIt'], [
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

    /**
     * Uses the HTMLPurifier to ensure safe HTML or display of messages and other user provided information.
     *
     * @param string $text string to truncate
     *
     * @return string purified string
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function purify($text)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.AutoParagraph', true);
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify(trim($text));
    }

    /*    private function extractMessageTypes($messages)
        {
            $messageTypes = [
                'defined' => [],
                'missing' => [],
                'fallback' => []
            ];
    
            foreach($messages as $message) {
                if ($message['domain'] === 'messages') {
                    $value = [
                        'id' => $message['id'],
                        'original' => $this->translator->trans($message['id'], $message['parameters'], 'messages', 'en'),
                        'locale' => $message['locale'],
                        'translation' => $message['translation'],
                        ];
    
                    $state = 'unknown';
                    switch ($message['state']) {
                        case DataCollectorTranslator::MESSAGE_DEFINED:
                            $state = 'defined';
                            break;
                        case DataCollectorTranslator::MESSAGE_MISSING:
                            $state = 'missing';
                            break;
                        case DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK:
                            $state = 'fallback';
                            break;
                    }
    
                    $messageTypes[$state][] = $value;
                }
            }
    
            return $messageTypes;
        }*/

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
        ];
    }
}
