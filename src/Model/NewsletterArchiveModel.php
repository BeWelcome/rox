<?php

namespace App\Model;

use App\Entity\Newsletter;
use App\Repository\NewsletterRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * The public newsletter archive: the published newsletters, newest first, with the languages they have a text in.
 *
 * @phpstan-type Entry array{id: int, name: string, created: DateTimeInterface, languages: list<string>, link: string}
 */
class NewsletterArchiveModel
{
    public const int PER_PAGE = 10;
    public const string FALLBACK_LANGUAGE = 'en';

    /** @var list<Entry>|null */
    private ?array $entries = null;
    private ?string $entriesLocale = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * One archive page; each newsletter links to the member's locale, or its first language without that.
     */
    public function getPage(int $page, string $locale): Pagerfanta
    {
        $pager = new Pagerfanta(new ArrayAdapter($this->entries($locale)));
        $pager->setMaxPerPage(self::PER_PAGE);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @return array{total: int, since: ?int} the number of published newsletters and the year of the oldest
     */
    public function getSummary(string $locale): array
    {
        $entries = $this->entries($locale);
        $oldest = end($entries);

        return [
            'total' => \count($entries),
            'since' => false === $oldest ? null : (int) $oldest['created']->format('Y'),
        ];
    }

    /**
     * @return list<string>
     */
    public function getLanguages(Newsletter $newsletter): array
    {
        return $this->repository()->findLanguagesByName([$newsletter->getName()])[strtolower($newsletter->getName())]
            ?? [];
    }

    /**
     * The language to show a newsletter in when it has no text in the requested one: English, or its first language.
     * Null when the requested language is fine, or when the newsletter has no text at all.
     *
     * @param list<string> $languages
     */
    public function getRedirectLanguage(array $languages, string $language): ?string
    {
        if ([] === $languages || \in_array($language, $languages, true)) {
            return null;
        }

        return $this->pick($languages, [self::FALLBACK_LANGUAGE]);
    }

    /**
     * The older and newer published newsletter, each in the language read now if it has it, else in the member's
     * locale or English, and the archive page the newsletter is on. A newsletter that isn't in the archive (not sent
     * or not a regular one) has no neighbours and leads back to the first page.
     *
     * @return array{older: ?Entry, newer: ?Entry, page: int}
     */
    public function getNeighbours(Newsletter $newsletter, string $language, string $locale): array
    {
        $entries = $this->entries($locale);
        $index = null;
        foreach ($entries as $position => $entry) {
            if ($entry['id'] === $newsletter->getId()) {
                $index = $position;
                break;
            }
        }

        if (null === $index) {
            return ['older' => null, 'newer' => null, 'page' => 1];
        }

        $neighbour = function (int $position) use ($entries, $language, $locale): ?array {
            if (!isset($entries[$position])) {
                return null;
            }
            $entry = $entries[$position];
            $entry['link'] = $this->pick($entry['languages'], [$language, $locale, self::FALLBACK_LANGUAGE]);

            return $entry;
        };

        return [
            'older' => $neighbour($index + 1),
            'newer' => $neighbour($index - 1),
            'page' => intdiv($index, self::PER_PAGE) + 1,
        ];
    }

    /**
     * @return list<Entry>
     */
    private function entries(string $locale): array
    {
        if (null !== $this->entries && $this->entriesLocale === $locale) {
            return $this->entries;
        }

        $repository = $this->repository();
        /** @var list<Newsletter> $newsletters */
        $newsletters = $repository->findAllPublished();
        $languages = $repository->findLanguagesByName(array_map(static fn (Newsletter $n) => $n->getName(), $newsletters));

        $this->entries = [];
        foreach ($newsletters as $newsletter) {
            $codes = $languages[strtolower($newsletter->getName())] ?? [];
            $this->entries[] = [
                'id' => $newsletter->getId(),
                'name' => $newsletter->getName(),
                'created' => $newsletter->getCreated(),
                'languages' => $codes,
                'link' => $this->pick($codes, [$locale]),
            ];
        }
        $this->entriesLocale = $locale;

        return $this->entries;
    }

    /**
     * The first wanted language the newsletter has, else its first language; the first wanted one without any.
     *
     * @param list<string> $languages
     * @param list<string> $wanted
     */
    private function pick(array $languages, array $wanted): string
    {
        foreach ($wanted as $language) {
            if (\in_array($language, $languages, true)) {
                return $language;
            }
        }

        return $languages[0] ?? $wanted[0];
    }

    private function repository(): NewsletterRepository
    {
        /** @var NewsletterRepository $repository */
        $repository = $this->entityManager->getRepository(Newsletter::class);

        return $repository;
    }
}
