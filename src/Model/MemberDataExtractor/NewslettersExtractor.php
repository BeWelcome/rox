<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The newsletter texts that are the member's: the English version of each newsletter they created, and every version,
 * of any newsletter, they were the last to update. The texts are words (broadcast_title_<name>, broadcast_body_<name>,
 * one per language); a version's last editor is the author of the more recently updated of its two.
 */
final class NewslettersExtractor extends AbstractExtractor implements ExtractorInterface
{
    private const string TITLE = 'broadcast_title_';
    private const string BODY = 'broadcast_body_';
    private const string ENGLISH = 'en';

    public function extract(Member $member, string $tempDir): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->registry->getManagerForClass(Word::class);

        // the newsletters with a text of the member's: created by them, or with a version they updated
        $newsletters = [];
        foreach ($entityManager->getRepository(Newsletter::class)->findBy(['createdBy' => $member]) as $newsletter) {
            $newsletters[mb_strtolower($newsletter->getName())] = $newsletter;
        }
        $names = [];
        foreach ($this->wordsBy($entityManager, $member) as $code) {
            $names[] = $this->nameOf($code);
        }
        $missing = array_diff(array_unique(array_filter($names)), array_keys($newsletters));
        if ([] !== $missing) {
            foreach ($entityManager->getRepository(Newsletter::class)->findBy(['name' => array_values($missing)]) as $newsletter) {
                $newsletters[mb_strtolower($newsletter->getName())] = $newsletter;
            }
        }

        $written = [];
        foreach ($newsletters as $name => $newsletter) {
            $mine = $newsletter->getCreatedBy() === $member;
            foreach ($this->versions($entityManager, $newsletter) as $locale => $version) {
                if (($mine && self::ENGLISH === $locale) || $version['editor'] === $member) {
                    $written[] = ['newsletter' => $newsletter, 'locale' => $locale, 'title' => $version['title'], 'body' => $version['body']];
                }
            }
        }
        // newest newsletter first, each in English first
        usort($written, static fn (array $a, array $b): int => [$b['newsletter']->getCreated(), self::ENGLISH !== $a['locale'], $a['locale']]
            <=> [$a['newsletter']->getCreated(), self::ENGLISH !== $b['locale'], $b['locale']]);

        $this->writePersonalDataFile(['newsletters' => $written, 'member' => $member], 'newsletters', $tempDir . 'newsletters.html');

        return ['newsletters' => \count($written)];
    }

    /**
     * @return list<string> the codes of the newsletter texts the member is the author of
     */
    private function wordsBy(EntityManagerInterface $entityManager, Member $member): array
    {
        return $entityManager->createQueryBuilder()
            ->select('w.code')
            ->from(Word::class, 'w')
            ->where('w.author = :member')
            ->andWhere('w.code LIKE :title OR w.code LIKE :body')
            ->setParameter('member', $member)
            ->setParameter('title', self::TITLE . '%')
            ->setParameter('body', self::BODY . '%')
            ->getQuery()
            ->getSingleColumnResult();
    }

    private function nameOf(string $code): string
    {
        foreach ([self::TITLE, self::BODY] as $prefix) {
            if (0 === stripos($code, $prefix)) {
                return mb_strtolower(substr($code, \strlen($prefix)));
            }
        }

        return '';
    }

    /**
     * @return array<string, array{title: ?string, body: ?string, editor: ?Member}> per language: the texts and who
     *                                                                              updated them last
     */
    private function versions(EntityManagerInterface $entityManager, Newsletter $newsletter): array
    {
        /** @var list<Word> $words */
        $words = $entityManager->getRepository(Word::class)->findBy([
            'code' => [self::TITLE . $newsletter->getName(), self::BODY . $newsletter->getName()],
        ]);

        $versions = [];
        $updated = [];
        foreach ($words as $word) {
            $locale = $word->getLanguage()->getShortCode();
            $versions[$locale] ??= ['title' => null, 'body' => null, 'editor' => null];
            $versions[$locale][0 === stripos($word->getCode(), self::TITLE) ? 'title' : 'body'] = $word->getSentence();
            $time = $word->getUpdated();
            if (!isset($updated[$locale]) || $time > $updated[$locale]) {
                $updated[$locale] = $time;
                $versions[$locale]['editor'] = $word->getAuthor();
            }
        }

        return $versions;
    }
}
