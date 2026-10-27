<?php

namespace App\Tests\Model;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Word;
use App\Model\TranslationModel;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The lists of the translation tool (/admin/translations/{type}/{locale}), read from word (not main's words table).
 */
#[Group('integration')]
class TranslationListsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TranslationModel $translationModel;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->translationModel = static::getContainer()->get(TranslationModel::class);
    }

    public function testAllShowsTheTextInTheLocaleOrElseInEnglish(): void
    {
        $this->word('zztest.list.both', 'en', 'English text');
        $this->word('zztest.list.both', 'fr', 'Texte français');
        $this->word('zztest.list.english', 'en', 'Only in English');

        $rows = $this->rows('all', 'fr', 'zztest.list.');

        self::assertSame(
            ['zztest.list.both' => ['fr', 'Texte français'], 'zztest.list.english' => ['en', 'Only in English']],
            $this->bySentence($rows)
        );
    }

    public function testMissingListsEnglishTextsWithoutTranslation(): void
    {
        $this->word('zztest.list.missing', 'en', 'Translate me');
        $this->word('zztest.list.done', 'en', 'Done already');
        $this->word('zztest.list.done', 'fr', 'Déjà fait');
        $this->word('zztest.list.archived', 'en', 'Old', archived: true);
        $this->word('zztest.list.fixed', 'en', 'BeWelcome', allowed: false);

        self::assertSame(['zztest.list.missing'], $this->codes($this->rows('missing', 'fr', 'zztest.list.')));
    }

    public function testUpdateListsTranslationsOlderThanTheirEnglishText(): void
    {
        $this->word('zztest.list.changed', 'en', 'Changed text');
        $this->word('zztest.list.changed', 'fr', 'Texte ancien');
        $this->word('zztest.list.major', 'en', 'Rewritten text');
        $this->word('zztest.list.major', 'fr', 'Texte ancien');
        $this->word('zztest.list.current', 'en', 'Current text');
        $this->word('zztest.list.current', 'fr', 'Texte à jour');
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement(
            "UPDATE word SET updated = NOW() - INTERVAL 2 DAY, majorupdate = NOW() - INTERVAL 2 DAY WHERE code LIKE 'zztest.list.%'"
        );
        $connection->executeStatement(
            "UPDATE word SET updated = NOW() WHERE code IN ('zztest.list.changed', 'zztest.list.major') AND ShortCode = 'en'"
        );
        $connection->executeStatement(
            "UPDATE word SET majorupdate = NOW() WHERE code = 'zztest.list.major' AND ShortCode = 'en'"
        );

        $rows = array_filter(
            $this->rows('update', 'fr'),
            static fn (array $row): bool => str_starts_with($row['code'], 'zztest.list.')
        );

        self::assertSame(
            ['zztest.list.major' => 1, 'zztest.list.changed' => 0],
            array_column(array_values($rows), 'majorUpdate', 'code')
        );
        self::assertSame(['fr'], array_values(array_unique(array_column($rows, 'shortCode'))));
    }

    public function testArchivedAndDoNotTranslateListTheirEnglishTexts(): void
    {
        $this->word('zztest.list.archived', 'en', 'Old', archived: true);
        $this->word('zztest.list.fixed', 'en', 'BeWelcome', allowed: false);

        self::assertContains('zztest.list.archived', $this->codes($this->rows('archived', 'en')));
        self::assertNotContains('zztest.list.fixed', $this->codes($this->rows('archived', 'en')));
        self::assertContains('zztest.list.fixed', $this->codes($this->rows('donottranslate', 'en')));
        self::assertNotContains('zztest.list.archived', $this->codes($this->rows('donottranslate', 'en')));
    }

    public function testPagesCountEachCodeOnce(): void
    {
        foreach (['a', 'b', 'c'] as $suffix) {
            $this->word('zztest.list.page.' . $suffix, 'en', 'English ' . $suffix);
            $this->word('zztest.list.page.' . $suffix, 'fr', 'Français ' . $suffix);
        }

        $pages = new Pagerfanta($this->translationModel->getAdapter('all', 'fr', 'zztest.list.page.'));
        $pages->setMaxPerPage(2);

        self::assertSame(3, $pages->getNbResults());
        self::assertSame(2, $pages->getNbPages());
        self::assertCount(2, iterator_to_array($pages->getCurrentPageResults()));
    }

    private function word(string $code, string $locale, string $sentence, bool $archived = false, bool $allowed = true): void
    {
        $word = new Word()
            ->setCode($code)
            ->setLanguage($this->entityManager->getReference(Language::class, $locale))
            ->setSentence($sentence)
            ->setDomain('messages')
            ->setDescription('end-to-end list test')
            ->setIsArchived($archived)
            ->setTranslationAllowed($allowed ? TranslationAllowedType::TRANSLATION_ALLOWED : TranslationAllowedType::TRANSLATION_NOT_ALLOWED);
        $this->entityManager->persist($word);
        $this->entityManager->flush();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(string $type, string $locale, string $code = ''): array
    {
        $adapter = $this->translationModel->getAdapter($type, $locale, $code);

        return array_values(iterator_to_array($adapter->getSlice(0, 1000)));
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return array<string, array{string, string}>
     */
    private function bySentence(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = [$row['shortCode'], $row['sentence']];
        }
        ksort($result);

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<string>
     */
    private function codes(array $rows): array
    {
        return array_column($rows, 'code');
    }
}
