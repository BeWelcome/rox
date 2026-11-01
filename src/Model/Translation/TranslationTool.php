<?php

namespace App\Model\Translation;

use App\Doctrine\TranslationAllowedType;
use App\Doctrine\TranslationFormat;
use App\Entity\Member;
use App\Entity\Word;
use App\Pagerfanta\WordListAdapter;
use App\Repository\WordRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The translation tool's pages (admin/translations/tool/): the texts of a language by view, its summary, the
 * languages a translator works on and the statistics of all languages.
 */
final readonly class TranslationTool
{
    /** States of a text in a language, as the list shows them */
    public const string MISSING = 'missing';
    public const string OUTDATED = 'update';
    public const string TRANSLATED = 'ok';
    public const string STAYS_ENGLISH = 'dnt';
    public const string ARCHIVED = 'archived';

    public const int PER_PAGE = 20;

    /**
     * @param list<string> $locales the languages of the site
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%locales%')]
        private array $locales,
    ) {
    }

    /**
     * The texts of a view (WordRepository::TOOL_VIEWS) in the language, a page of them.
     *
     * @return Pagerfanta<array<string, mixed>>
     */
    public function texts(string $view, string $locale, string $term, int $page): Pagerfanta
    {
        $texts = new Pagerfanta(new WordListAdapter($this->words()->toolList($view, $locale, $term)));
        $texts->setMaxPerPage(self::PER_PAGE);
        $texts->setCurrentPage(max(1, min($page, $texts->getNbPages())));

        return $texts;
    }

    /**
     * What the list shows of a row of WordRepository::toolList().
     *
     * @param array<string, mixed> $row
     *
     * @return array{code: string, english: string, description: string, format: TranslationFormat, translation: ?string,
     *     state: string, date: ?DateTimeInterface, change: ?array{before: string, after: string}, action: string}
     */
    public function text(array $row, string $view): array
    {
        $english = (string) $row['english'];
        $translation = null === $row['translation'] ? null : (string) $row['translation'];
        $written = $row['translationUpdated'] ?? $row['translationCreated'];
        $outdated = null !== $translation && $written instanceof DateTimeInterface && (
            ($row['majorUpdate'] instanceof DateTimeInterface && $row['majorUpdate'] > $written)
            || ($row['updated'] instanceof DateTimeInterface && $row['updated'] > $written)
        );

        $state = match (true) {
            'archived' === $view => self::ARCHIVED,
            TranslationAllowedType::TRANSLATION_NOT_ALLOWED === $row['translationAllowed'] => self::STAYS_ENGLISH,
            null === $translation => self::MISSING,
            $outdated => self::OUTDATED,
            default => self::TRANSLATED,
        };

        // the last change of the English text, if the translation was written before it
        $change = null;
        if (
            self::OUTDATED === $state && null !== $row['previousSentence']
            && $row['sentenceChanged'] instanceof DateTimeInterface && $written < $row['sentenceChanged']
        ) {
            $change = ['before' => self::plain((string) $row['previousSentence']), 'after' => self::plain($english)];
        }

        return [
            'code' => (string) $row['code'],
            // the list shows the texts without markup; the side panel has them as written
            'english' => self::plain($english),
            'description' => (string) $row['description'],
            'format' => $row['format'] ?? TranslationFormat::detect($english),
            'translation' => null === $translation ? null : self::plain($translation),
            'state' => $state,
            'date' => match ($state) {
                self::MISSING => $row['created'],
                self::OUTDATED => $row['sentenceChanged'] ?? $row['updated'],
                self::TRANSLATED => $written,
                self::ARCHIVED => $row['updated'],
                default => null,
            },
            'change' => $change,
            // what the translator does with it: translate a missing text, edit a translation, or nothing
            'action' => match ($state) {
                self::MISSING => 'add',
                self::OUTDATED, self::TRANSLATED => 'edit',
                default => '',
            },
        ];
    }

    /**
     * How many texts each view has in the language (WordRepository::TOOL_VIEWS), and the share translated.
     *
     * @return array{counts: array<string, int>, total: int, missing: int, outdated: int, upToDate: int, share: float}
     */
    public function summary(string $locale, string $term = ''): array
    {
        $counts = $this->words()->toolCounts($locale, $term);
        $total = $counts['all'];
        $missing = $counts['missing'];
        $outdated = $counts['update'];

        return [
            'counts' => $counts,
            'total' => $total,
            'missing' => $missing,
            'outdated' => $outdated,
            'upToDate' => max(0, $total - $missing - $outdated),
            'share' => 0 === $total ? 0.0 : ($total - $missing) / $total * 100,
        ];
    }

    /**
     * The languages the member translates, in the order of the site's languages; null for all of them.
     *
     * @return list<string>|null
     */
    public function languagesOf(Member $member): ?array
    {
        $languages = array_values(array_filter($this->locales, $member->hasRightsForLocale(...)));

        return \count($languages) === \count($this->locales) ? null : $languages;
    }

    /**
     * Every language of the site: the share translated, the texts missing and those to update, the last change.
     * English, the source, first; then by share.
     *
     * @return list<array{locale: string, share: float, upToDate: float, outdatedShare: float, missing: int,
     *     outdated: int, translator: ?Member, system: bool, date: ?DateTimeInterface}>
     */
    public function statistics(): array
    {
        $words = $this->words();
        $total = (int) $words->getTranslatableItemsCount('en');
        $counts = $words->toolLanguageCounts();
        $details = $words->getTranslationDetails($this->locales);

        $languages = [];
        foreach ($this->locales as $locale) {
            $translated = 'en' === $locale ? $total : min($total, $counts[$locale]['translated'] ?? 0);
            $outdated = 'en' === $locale ? 0 : min($translated, $counts[$locale]['outdated'] ?? 0);
            $languages[] = [
                'locale' => $locale,
                'share' => 0 === $total ? 0.0 : $translated / $total * 100,
                'upToDate' => 0 === $total ? 0.0 : ($translated - $outdated) / $total * 100,
                'outdatedShare' => 0 === $total ? 0.0 : $outdated / $total * 100,
                'missing' => $total - $translated,
                'outdated' => $outdated,
                'translator' => $details[$locale]['translator'] ?? null,
                'system' => $details[$locale]['system'] ?? false,
                'date' => $details[$locale]['date'] ?? null,
            ];
        }
        usort($languages, static fn (array $a, array $b): int => ['en' !== $a['locale'], -$a['share'], $a['locale']]
            <=> ['en' !== $b['locale'], -$b['share'], $b['locale']]);

        return $languages;
    }

    /**
     * A text as the page shows it, without markup: tags and line breaks as spaces, entities decoded.
     */
    public static function plain(string $text): string
    {
        $text = (string) preg_replace('~<br\s*/?>|</(?:p|li|div|h[1-6])>~i', ' ', $text);
        $text = html_entity_decode(strip_tags($text), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('~\s+~u', ' ', str_replace("\u{a0}", ' ', $text)));
    }

    private function words(): WordRepository
    {
        /** @var WordRepository $words */
        $words = $this->entityManager->getRepository(Word::class);

        return $words;
    }
}
