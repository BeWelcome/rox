<?php

namespace App\Tests\TranslationLoader;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The translator loads a language's texts from the database (DatabaseLoader) only for the domains that have a file
 * for it in translations/ (messages.hy.db): without them, a language of the site shows English everywhere.
 */
#[Group('integration')]
class LocaleResourcesTest extends KernelTestCase
{
    public function testEveryLanguageOfTheSiteHasItsDatabaseResources(): void
    {
        self::bootKernel();
        $directory = static::getContainer()->getParameter('translator.default_path');

        $missing = [];
        foreach (static::getContainer()->getParameter('locales') as $locale) {
            foreach (['messages', 'messages+intl-icu'] as $domain) {
                if (!is_file(\sprintf('%s/%s.%s.db', $directory, $domain, $locale))) {
                    $missing[] = $domain . '.' . $locale . '.db';
                }
            }
        }

        self::assertSame([], $missing);
    }
}
