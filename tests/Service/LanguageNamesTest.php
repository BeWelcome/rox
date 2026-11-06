<?php

namespace App\Tests\Service;

use App\Service\LanguageNames;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LanguageNamesTest extends TestCase
{
    public function testTheOwnNameComesFromIntl(): void
    {
        $names = new LanguageNames($this->translator());

        self::assertSame('Deutsch', $names->native('de'));
        self::assertSame('Español', $names->native('es'));
        self::assertSame('Português (Brasil)', $names->native('pt-br'));
        self::assertSame('中文（繁體）', $names->native('zh-hant'));
    }

    public function testTheNameInAnotherLanguage(): void
    {
        $names = new LanguageNames($this->translator());

        self::assertSame('German', $names->in('de', 'en'));
        self::assertSame('Portugiesisch (Brasilien)', $names->in('pt-br', 'de'));
        self::assertSame('Allemand', $names->in('de', 'fr'));
    }

    public function testACodeIntlDoesNotKnowIsTranslated(): void
    {
        $names = new LanguageNames($this->translator());

        self::assertSame('Zztest language', $names->native('zz-test'));
    }

    private function translator(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => 'lang_zz-test' === $id ? 'zztest language ' : $id);

        return $translator;
    }
}
