<?php

namespace App\Tests\Service;

use App\Service\NativeLanguageNames;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

class NativeLanguageNamesTest extends TestCase
{
    private const array NAMES = ['en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français'];

    /** @var list<string> locales the translator was asked to translate into */
    private array $translatedLocales = [];

    public function testReturnsEachLanguageNameInItsOwnLocale(): void
    {
        $names = new NativeLanguageNames($this->translator(), new ArrayAdapter(), ['en', 'de', 'fr']);

        $this->assertSame('Deutsch', $names->get('de'));
        $this->assertSame(self::NAMES, $names->all());
    }

    public function testTranslatesOnlyOnceAcrossRequests(): void
    {
        $cache = new ArrayAdapter();
        $translator = $this->translator();

        // Two instances simulate two requests sharing the same cache pool.
        new NativeLanguageNames($translator, $cache, ['en', 'de', 'fr'])->get('de');
        new NativeLanguageNames($translator, $cache, ['en', 'de', 'fr'])->get('fr');

        $this->assertSame(['en', 'de', 'fr'], $this->translatedLocales);
    }

    public function testClearForcesRebuild(): void
    {
        $names = new NativeLanguageNames($this->translator(), new ArrayAdapter(), ['en', 'de']);
        $names->all();
        $names->clear();
        $names->all();

        $this->assertSame(['en', 'de', 'en', 'de'], $this->translatedLocales);
    }

    public function testChangedLocaleListIsNotServedFromOldCache(): void
    {
        $cache = new ArrayAdapter();
        new NativeLanguageNames($this->translator(), $cache, ['en'])->all();

        $names = new NativeLanguageNames($this->translator(), $cache, ['en', 'de']);

        $this->assertSame(['en' => 'English', 'de' => 'Deutsch'], $names->all());
    }

    public function testUnknownLocaleFallsBackToTranslator(): void
    {
        $names = new NativeLanguageNames($this->translator(), new ArrayAdapter(), ['en']);

        $this->assertSame('Français', $names->get('fr'));
    }

    private function translator(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(function (string $id, array $parameters, ?string $domain, ?string $locale): string {
            $this->assertSame('lang_' . $locale, $id);
            $this->translatedLocales[] = $locale;

            return self::NAMES[$locale] ?? $id;
        });

        return $translator;
    }
}
