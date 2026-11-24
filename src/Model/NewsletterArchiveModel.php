<?php

namespace App\Model;

use App\Entity\Member;
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

    /**
     * @param list<string> $locales the languages of the site
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly array $locales,
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
     * A draft: a mass mail nobody got yet (NewsletterRepository::findDrafts). It is no public page.
     */
    public function isDraft(Newsletter $newsletter): bool
    {
        return $this->repository()->isDraft($newsletter);
    }

    /**
     * Who reads a draft in a language: the mass mail volunteers in any, as they wrote it (the link of a test mail
     * leads to it), a translator in the languages they translate. Nobody else.
     */
    public function readsDraftIn(string $language, ?Member $translator, bool $massMail): bool
    {
        return $massMail || (null !== $translator && $translator->hasRightsForLocale($language));
    }

    /**
     * The drafts for the volunteer, newest first, for the archive's first page. Each has the languages the volunteer
     * reads it in and links to the volunteer's own language; a translator who doesn't translate that one gets the
     * first language they do: of the draft's, else of the site's.
     *
     * @return list<Entry>
     */
    public function getDrafts(string $locale, ?Member $translator, bool $massMail): array
    {
        if (null === $translator && !$massMail) {
            return [];
        }

        $repository = $this->repository();
        $drafts = $repository->findDrafts();
        $languages = $repository->findLanguagesByName(array_map(static fn (Newsletter $n) => $n->getName(), $drafts));
        $reads = fn (string $language): bool => $this->readsDraftIn($language, $translator, $massMail);

        $entries = [];
        foreach ($drafts as $draft) {
            $codes = array_values(array_filter($languages[strtolower($draft->getName())] ?? [], $reads));
            $link = array_find([$locale, ...$codes, ...$this->locales], $reads);
            if (null === $link) {
                continue;
            }
            $entries[] = [
                'id' => $draft->getId(),
                'name' => $draft->getName(),
                'created' => $draft->getCreated(),
                'languages' => $codes,
                'link' => $link,
            ];
        }

        return $entries;
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
