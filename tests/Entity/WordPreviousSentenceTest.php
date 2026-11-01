<?php

namespace App\Tests\Entity;

use App\Entity\Language;
use App\Entity\Word;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * An English text keeps its text before the last change, so that translators see what changed.
 */
class WordPreviousSentenceTest extends TestCase
{
    public function testAChangeOfAnEnglishTextKeepsTheTextBefore(): void
    {
        $word = $this->word('en', 'Welcome');

        $word->setSentence('Welcome home');
        self::assertSame('Welcome', $word->getPreviousSentence());
        self::assertNotNull($word->getSentenceChanged());

        $word->setSentence('Welcome back');
        self::assertSame('Welcome home', $word->getPreviousSentence());
    }

    public function testTheSameTextAndTheFirstTextAreNoChange(): void
    {
        $word = $this->word('en', 'Welcome');
        $word->setSentence('Welcome');

        self::assertNull($word->getPreviousSentence());
        self::assertNull($word->getSentenceChanged());
    }

    public function testTranslationsKeepNothing(): void
    {
        $word = $this->word('de', 'Willkommen');
        $word->setSentence('Herzlich willkommen');

        self::assertNull($word->getPreviousSentence());
    }

    public function testATranslationWrittenBeforeTheChangeIsOlder(): void
    {
        $translation = $this->word('de', 'Willkommen');
        $translation->setUpdated(new DateTime('-1 day'));
        $original = $this->word('en', 'Welcome');
        self::assertFalse($translation->isOlderThanTheChangeOf($original), 'the English text never changed');

        $original->setSentence('Welcome home');
        self::assertTrue($translation->isOlderThanTheChangeOf($original));

        $translation->setUpdated(new DateTime('+1 minute'));
        self::assertFalse($translation->isOlderThanTheChangeOf($original));
    }

    private function word(string $locale, string $sentence): Word
    {
        $word = new Word();
        $word->setLanguage(new Language()->setShortCode($locale));
        $word->setSentence($sentence);

        return $word;
    }
}
