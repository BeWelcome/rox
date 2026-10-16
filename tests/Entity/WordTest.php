<?php

namespace App\Tests\Entity;

use App\Entity\Word;
use App\Form\CustomDataClass\Translation\EditTranslationRequest;
use PHPUnit\Framework\TestCase;

/**
 * Adding the first translation of a text builds the form from a new Word. Reading its sentence before it
 * was set made the add page fail with a 500 (route C, #519).
 */
class WordTest extends TestCase
{
    public function testNewWordHasAnEmptySentence(): void
    {
        $this->assertSame('', (new Word())->getSentence());
    }

    public function testAddFormForANewTranslationStartsEmpty(): void
    {
        $request = EditTranslationRequest::fromTranslations(
            $this->original('zztest.e2e.translation', 'End-to-end test text'),
            (new Word())->setCode('zztest.e2e.translation')->setShortCode('fr'),
        );

        $this->assertSame('End-to-end test text', $request->englishText);
        $this->assertSame('', $request->translatedText);
        $this->assertSame('fr', $request->locale);
    }

    public function testAddFormForANewBroadcastStartsWithTheEnglishText(): void
    {
        $request = EditTranslationRequest::fromTranslations(
            $this->original('broadcast_body_e2e', 'Dear member'),
            (new Word())->setCode('broadcast_body_e2e')->setShortCode('fr'),
        );

        $this->assertSame('Dear member', $request->translatedText);
    }

    private function original(string $code, string $sentence): Word
    {
        return (new Word())
            ->setCode($code)
            ->setDomain('messages')
            ->setSentence($sentence)
            ->setDescription('Used by the tests only.');
    }
}
