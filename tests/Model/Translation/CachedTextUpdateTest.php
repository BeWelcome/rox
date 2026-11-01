<?php

namespace App\Tests\Model\Translation;

use App\Doctrine\DomainType;
use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Word;
use App\Model\TranslationModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * A saved text updates only its entry in the cached catalogues (TranslationModel::updateCachedText()), and the next
 * request shows it as the catalogues built from the database would: English as every language's fallback, a
 * translation in its language, English again after a major change of the English text, the new domain.
 */
#[Group('integration')]
class CachedTextUpdateTest extends KernelTestCase
{
    private const string CODE = 'zztest.cached.text';

    private EntityManagerInterface $entityManager;
    private TranslationModel $translationModel;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->translationModel = static::getContainer()->get(TranslationModel::class);
        // the catalogues are cached
        foreach (['en', 'de', 'fr'] as $locale) {
            static::getContainer()->get('translator')->getCatalogue($locale);
        }
    }

    protected function tearDown(): void
    {
        // the database is rolled back, the cache isn't: the test text goes from it too
        $this->entityManager->createQuery('DELETE FROM ' . Word::class . ' w WHERE w.code = :code')
            ->setParameter('code', self::CODE)->execute();
        $this->translationModel->updateCachedText(self::CODE, 'en');
        parent::tearDown();
    }

    public function testANewEnglishTextIsEveryLanguagesFallback(): void
    {
        $this->word('en', 'Stay with hosts');
        $this->translationModel->updateCachedText(self::CODE, 'en');

        self::assertSame(['en' => 'Stay with hosts', 'de' => 'Stay with hosts', 'fr' => 'Stay with hosts'], $this->shown());
    }

    public function testATranslationIsShownInItsLanguageOnly(): void
    {
        $this->word('en', 'Stay with hosts');
        $this->translationModel->updateCachedText(self::CODE, 'en');
        $this->word('de', 'Bleib bei Gastgebern');
        $this->translationModel->updateCachedText(self::CODE, 'de');

        self::assertSame(['en' => 'Stay with hosts', 'de' => 'Bleib bei Gastgebern', 'fr' => 'Stay with hosts'], $this->shown());

        $this->stored('de')->setSentence('Übernachte bei Gastgebern')->setUpdated(new DateTime());
        $this->entityManager->flush();
        $this->translationModel->updateCachedText(self::CODE, 'de');

        self::assertSame('Übernachte bei Gastgebern', $this->shown()['de']);
    }

    public function testAMajorChangeOfTheEnglishTextShowsItInsteadOfTheOlderTranslation(): void
    {
        $this->word('en', 'Stay with hosts', updated: '-2 days');
        $this->word('de', 'Bleib bei Gastgebern', updated: '-1 day');
        $this->translationModel->updateCachedText(self::CODE, 'en');
        self::assertSame('Bleib bei Gastgebern', $this->shown()['de']);

        $this->stored('en')->setSentence('Stay with hosts for free')->setUpdated(new DateTime())->setMajorUpdate(new DateTime());
        $this->entityManager->flush();
        $this->translationModel->updateCachedText(self::CODE, 'en');

        self::assertSame(['en' => 'Stay with hosts for free', 'de' => 'Stay with hosts for free', 'fr' => 'Stay with hosts for free'], $this->shown());
    }

    public function testANewDomainMovesTheText(): void
    {
        $english = $this->word('en', 'Stay with hosts');
        $this->translationModel->updateCachedText(self::CODE, 'en');

        $english->setSentence('{count, plural, one {# host} other {# hosts}}')->setDomain(DomainType::ICU_MESSAGES);
        $this->entityManager->flush();
        $this->translationModel->updateCachedText(self::CODE, 'en');

        $catalogue = $this->freshTranslator()->getCatalogue('de');
        self::assertFalse($catalogue->defines(self::CODE, DomainType::MESSAGES), 'not in the old domain');
        self::assertSame('2 hosts', $this->freshTranslator()->trans(self::CODE, ['count' => 2], DomainType::MESSAGES, 'de'));
    }

    public function testAnArchivedTextIsRemoved(): void
    {
        $english = $this->word('en', 'Stay with hosts');
        $this->translationModel->updateCachedText(self::CODE, 'en');

        $english->setIsArchived(true);
        $this->entityManager->flush();
        $this->translationModel->updateCachedText(self::CODE, 'en');

        self::assertSame(['en' => self::CODE, 'de' => self::CODE, 'fr' => self::CODE], $this->shown());
    }

    /**
     * @return array<string, string> what the next request shows, by locale
     */
    private function shown(): array
    {
        $translator = $this->freshTranslator();
        $shown = [];
        foreach (['en', 'de', 'fr'] as $locale) {
            $shown[$locale] = $translator->trans(self::CODE, [], DomainType::MESSAGES, $locale);
        }

        return $shown;
    }

    /**
     * The translator of the next request: it reads the cached catalogues.
     */
    private function freshTranslator(): TranslatorInterface&TranslatorBagInterface
    {
        self::ensureKernelShutdown();
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->translationModel = static::getContainer()->get(TranslationModel::class);

        return static::getContainer()->get('translator');
    }

    /**
     * The stored text (the kernel of the next request has its own entity manager).
     */
    private function stored(string $locale): Word
    {
        return $this->entityManager->getRepository(Word::class)->findOneBy(['code' => self::CODE, 'language' => $locale]);
    }

    private function word(string $locale, string $sentence, string $updated = 'now'): Word
    {
        $word = new Word()
            ->setCode(self::CODE)
            ->setLanguage($this->entityManager->getReference(Language::class, $locale))
            ->setSentence($sentence)
            ->setDomain(DomainType::MESSAGES)
            ->setDescription('cached text test')
            ->setTranslationAllowed(TranslationAllowedType::TRANSLATION_ALLOWED)
            ->setUpdated(new DateTime($updated));
        $this->entityManager->persist($word);
        $this->entityManager->flush();

        return $word;
    }
}
