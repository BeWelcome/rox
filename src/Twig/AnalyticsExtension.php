<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Campaign tagging for links in emails (#540), so Plausible can attribute
 * visits, signups and donations to the email that brought them.
 */
class AnalyticsExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('utm_links', [$this, 'utmLinks'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Adds utm_source, utm_medium and utm_campaign to every link to bewelcome.org
     * (with or without www) in an HTML fragment. Links to other sites, and links
     * that already carry utm_ parameters, are left as they are.
     */
    public function utmLinks(string $html, string $campaign, string $source = 'newsletter', string $medium = 'email'): string
    {
        $parameters = http_build_query([
            'utm_source' => $source,
            'utm_medium' => $medium,
            'utm_campaign' => $campaign,
        ], '', '&amp;');

        return preg_replace_callback(
            '~href=(["\'])(https?://(?:www\.)?bewelcome\.org(?:[/?][^"\'#]*)?)(#[^"\']*)?\1~i',
            static function (array $match) use ($parameters): string {
                [$all, $quote, $url] = $match;
                $fragment = $match[3] ?? '';
                if (false !== stripos($url, 'utm_')) {
                    return $all;
                }
                $separator = false === strpos($url, '?') ? '?' : '&amp;';

                return 'href=' . $quote . $url . $separator . $parameters . $fragment . $quote;
            },
            $html
        );
    }
}
