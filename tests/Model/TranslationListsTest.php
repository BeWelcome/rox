<?php

namespace App\Tests\Model;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Language;
use App\Entity\Word;
use App\Model\Translation\TranslationTool;
use App\Pagerfanta\WordListAdapter;
use App\Repository\WordRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The views of the translation tool's list (/admin/translations/{type}/{locale}), read from word (not main's words
 * table): WordRepository::toolList() and what TranslationTool makes of its rows.
 */
#[Group('integration')]
class TranslationListsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TranslationTool $translationTool;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->translationTool = static::getContainer()->get(TranslationTool::class);
    }

    public function testAllShowsTheTranslationOrThatItIsMissing(): void
    {
        $this->word('zztest.list.both', 'en', 'English text');
        $this->word('zztest.list.both', 'fr', 'Texte français');
        $this->word('zztest.list.english', 'en', 'Only in English');

        $texts = $this->texts('all', 'fr', 'zztest.list.');
        ksort($texts);

        self::assertSame(
            ['zztest.list.both' => ['ok', 'Texte français'], 'zztest.list.english' => ['missing', null]],
            array_map(static fn (array $text): array => [$text['state'], $text['translation']], $texts)
        );
    }

    public function testMissingListsEnglishTextsWithoutTranslation(): void
    {
        $this->word('zztest.list.missing', 'en', 'Translate me');
        $this->word('zztest.list.done', 'en', 'Done already');
        $this->word('zztest.list.done', 'fr', 'Déjà fait');
        $this->word('zztest.list.archived', 'en', 'Old', archived: true);
        $this->word('zztest.list.fixed', 'en', 'BeWelcome', allowed: false);

        self::assertSame(['zztest.list.missing'], array_keys($this->texts('missing', 'fr', 'zztest.list.')));
    }

    public function testUpdateListsTranslationsOlderThanTheirEnglishTextMajorChangesFirst(): void
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

        $texts = $this->texts('update', 'fr', 'zztest.list.');

        self::assertSame(['zztest.list.major', 'zztest.list.changed'], array_keys($texts));
        self::assertSame(['update'], array_values(array_unique(array_column($texts, 'state'))));
    }

    public function testArchivedAndDoNotTranslateListTheirEnglishTexts(): void
    {
        $this->word('zztest.list.archived', 'en', 'Old', archived: true);
        $this->word('zztest.list.fixed', 'en', 'BeWelcome', allowed: false);

        self::assertSame(['zztest.list.archived'], array_keys($this->texts('archived', 'fr', 'zztest.list.')));
        self::assertSame(['zztest.list.fixed'], array_keys($this->texts('donottranslate', 'fr', 'zztest.list.')));
        self::assertSame(['archived'], array_column($this->texts('archived', 'fr', 'zztest.list.'), 'state'));
        self::assertSame(['dnt'], array_column($this->texts('donottranslate', 'fr', 'zztest.list.'), 'state'));
    }

    public function testTheCountsOfTheViews(): void
    {
        $this->word('zztest.list.missing', 'en', 'Translate me');
        $this->word('zztest.list.done', 'en', 'Done already');
        $this->word('zztest.list.done', 'fr', 'Déjà fait');
        $this->word('zztest.list.archived', 'en', 'Old', archived: true);

        $summary = $this->translationTool->summary('fr', 'zztest.list.');

        self::assertSame(['missing' => 1, 'update' => 0, 'all' => 2, 'donottranslate' => 0, 'archived' => 1], $summary['counts']);
        self::assertSame(50.0, $summary['share']);
    }

    public function testPagesCountEachCodeOnce(): void
    {
        foreach (['a', 'b', 'c'] as $suffix) {
            $this->word('zztest.list.page.' . $suffix, 'en', 'English ' . $suffix);
            $this->word('zztest.list.page.' . $suffix, 'fr', 'Français ' . $suffix);
        }

        /** @var WordRepository $words */
        $words = $this->entityManager->getRepository(Word::class);
        $pages = new Pagerfanta(new WordListAdapter($words->toolList('all', 'fr', 'zztest.list.page.')));
        $pages->setMaxPerPage(2);

        self::assertSame(3, $pages->getNbResults());
        self::assertSame(2, $pages->getNbPages());
        self::assertCount(2, iterator_to_array($pages->getCurrentPageResults()));
    }

    public function testTheListShowsTextsWithoutMarkup(): void
    {
        self::assertSame('Read the rules now. Fish & chips', TranslationTool::plain("<p>Read the <a href=\"/r\">rules</a>\nnow.</p><p>Fish &amp;&nbsp;chips</p>"));
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
     * The texts of a view as the list shows them, by code, in the list's order.
     *
     * @return array<string, array<string, mixed>>
     */
    private function texts(string $view, string $locale, string $code): array
    {
        $texts = [];
        foreach ($this->translationTool->texts($view, $locale, $code, 1)->getCurrentPageResults() as $row) {
            $text = $this->translationTool->text($row, $view);
            $texts[$text['code']] = $text;
        }

        return $texts;
    }
}
