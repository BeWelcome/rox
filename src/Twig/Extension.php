<?php

namespace App\Twig;

use Carbon\Carbon;
use HTMLPurifier;
use HTMLPurifier_HTML5Config;
use HtmlTruncator\InvalidHtmlException;
use HtmlTruncator\Truncator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\DataCollector\TranslationDataCollector;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Extension constructor.
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        protected TranslatorInterface $translator,
        private readonly EntrypointLookupInterface $entrypointLookup,
        private readonly LoggerInterface $logger,
        /** @var false|string[] */
        private readonly array $locales,
        private readonly string $publicDirectory
    ) {
    }

    #[AsTwigFunction('language_name', isSafe: ['html'])]
    public function languageName(string $locale): string
    {
        return $this->translator->trans(strtolower('lang_' . $locale), [], null, $locale);
    }

    #[AsTwigFunction('language_name_translated', isSafe: ['html'],)]
    public function languageNameTranslated(string $locale, string $display): string
    {
        return $this->translator->trans(strtolower('lang_' . $locale), [], null, $display);
    }

    #[AsTwigFunction('ago')]
    public function ago(Carbon $carbon): string
    {
        return $carbon->diffForHumans();
    }

    #[AsTwigFunction('encore_entry_css_source')]
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

    #[AsTwigFunction('get_translations')]
    public function getTranslations(): array
    {
        $collector = new TranslationDataCollector($this->translator);
        $collector->lateCollect();

        return [
            'collector' => $collector,
        ];
    }

    #[AsTwigFunction('distance')]
    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radiantLat = deg2rad($lat2 - $lat1);
        $radiantLng = deg2rad($lng2 - $lng1);

        $a = sin($radiantLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($radiantLng / 2) ** 2;

        return 12742 * asin(sqrt($a));
    }

    #[AsTwigFilter('privacy', isSafe: ['html'])]
    public function privacy(string $isoDate): string
    {
        $date = Carbon::createFromFormat('Y-m-d', $isoDate);
        if ($date->diffInDays() <=  7) {
            return $this->translator->trans('lastloginprivacy');
        } else {
            return $date->diffForHumans();
        }
    }

    /**
     * Truncates a string up to a number of characters while preserving whole words and HTML tags.
     *
     * @throws InvalidHtmlException
     */
    #[AsTwigFilter('truncate', isSafe: ['html'])]
    public function truncate(string $text, int $length = 100, string $ellipsis = '&#8230;'): string
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
     */
    #[AsTwigFilter('url_update', isSafe: ['html'])]
    public function urlUpdate(string $text): string
    {
        return preg_replace(
            '/(src|href)="http[s]?:\/\/(www|beta)\.bewelcome\.org\//i',
            '$1="/',
            $text
        );
    }


    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    #[AsTwigFilter('prepare_newsletter', isSafe: ['html'])]
    public function prepareNewsletter(string $text, bool $website = false): string
    {
        $config = HTMLPurifier_HTML5Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p,b,a[href],br,hr,i,u,strong,em,ol,ul,li,dl,dt,dd,img[src|alt|width|height],blockquote,del,'
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
        if ($website) {
            $centerOpen = '';
            $centerClose = '';
            $style = ' style="display: block; margin-left: auto; margin-right: auto; width: 60%;"';
        } else {
            $centerOpen = '<center>';
            $centerClose = '</center>';
            $style = '';
        }
        $result = preg_replace(
            '%<figure.*?><img.*?src="(.*?)".*?><figcaption>(.*?)</figcaption></figure>%',
            $centerOpen . '<img src="\1" alt="\2"' . $style . '>' . $centerClose,
            $text
        );

        // Use the string 'embedded image' in case of no figcaption
        $embeddedImage = $this->translator->trans('newsletter.embedded.image');
        $result = preg_replace(
            '%<figure.*?><img.*?src="(.*?)".*?>%',
            $centerOpen . '<img src="\1" alt="' . htmlentities($embeddedImage) . '"' . $style . '>' . $centerClose,
            (string) $result
        );

        $this->logger->info($text);
        $this->logger->info($result);

        return $result;
    }

    public function getName(): string
    {
        return 'LayoutKit';
    }

    public function getGlobals(): array
    {
        $version = '';
        $locale = 'en';
        $versionCreated = new Carbon();

        if (null !== $this->requestStack->getCurrentRequest()) {
            $locale = $this->requestStack->getSession()->get('locale', 'en');
        }

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
