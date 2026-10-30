<?php

namespace App\Tests\Templates;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * The word table holds its codes in lower case and translation ids are case sensitive, so a key like 'ForumReply'
 * shows up untranslated. The forum and group templates use the codes as the table has them.
 */
final class ForumTranslationKeysTest extends TestCase
{
    /** Codes the word table has in mixed case. */
    private const array MIXED_CASE_CODES = ['ThreadNotVisibleAbstract', 'ThreadNotVisibleHeadline'];

    public function testTheTemplatesUseTheCodesOfTheWordTable(): void
    {
        $finder = new Finder()->files()->name('*.twig')
            ->in([__DIR__ . '/../../templates/forum', __DIR__ . '/../../templates/group']);
        $mixedCase = [];
        foreach ($finder as $file) {
            // a literal key that is translated, given to a button or put in front of a number
            preg_match_all("/'([A-Za-z0-9_.]+)'\s*(?:\|\s*trans|~|,\s*'btn)/", $file->getContents(), $matches);
            foreach ($matches[1] as $key) {
                if ($key !== strtolower($key) && !\in_array($key, self::MIXED_CASE_CODES, true)) {
                    $mixedCase[] = $file->getRelativePathname() . ': ' . $key;
                }
            }
        }

        self::assertSame([], $mixedCase);
    }
}
