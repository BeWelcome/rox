<?php

namespace App\Tests\Twig;

use App\Twig\AnalyticsExtension;
use PHPUnit\Framework\TestCase;

/**
 * Links to bewelcome.org in a newsletter carry the campaign, so Plausible can attribute visits to it (#540).
 */
class AnalyticsExtensionTest extends TestCase
{
    public function testLinksToBeWelcomeCarryTheCampaign(): void
    {
        $html = '<a href="https://www.bewelcome.org/members/x?tab=2#about">x</a> <a href=\'https://bewelcome.org\'>y</a>';

        self::assertSame(
            '<a href="https://www.bewelcome.org/members/x?tab=2&amp;utm_source=newsletter&amp;utm_medium=email'
            . '&amp;utm_campaign=newsletter-7#about">x</a> <a href=\'https://bewelcome.org?utm_source=newsletter'
            . '&amp;utm_medium=email&amp;utm_campaign=newsletter-7\'>y</a>',
            new AnalyticsExtension()->utmLinks($html, 'newsletter-7')
        );
    }

    public function testOtherLinksStayAsTheyAre(): void
    {
        $html = '<a href="https://example.org/">x</a> <a href="https://bewelcome.org/?utm_source=other">y</a>'
            . ' <a href="https://bewelcome.org.evil.example/">z</a>';

        self::assertSame($html, new AnalyticsExtension()->utmLinks($html, 'newsletter-7'));
    }
}
