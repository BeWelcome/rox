<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Member;
use App\Entity\Newsletter;
use App\Repository\NewsletterRepository;

/**
 * The newsletters the member created, in each language they wrote it: their texts are words (broadcast_title_<name>,
 * broadcast_body_<name>, NewsletterRepository::getTranslations()), the newsletter entity has no translations.
 */
final class NewslettersExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): array
    {
        /** @var NewsletterRepository $repository */
        $repository = $this->getRepository(Newsletter::class);

        $written = [];
        /** @var Newsletter $newsletter */
        foreach ($repository->findBy(['createdBy' => $member], ['created' => 'DESC']) as $newsletter) {
            foreach ($repository->getTranslations($newsletter) as $translation) {
                if (($translation['author'] ?? null) !== $member) {
                    continue;
                }
                $written[] = [
                    'newsletter' => $newsletter,
                    'locale' => $translation['locale'],
                    'title' => $translation['title'] ?? null,
                    'body' => $translation['body'] ?? null,
                ];
            }
        }

        $this->writePersonalDataFile(['newsletters' => $written, 'member' => $member], 'newsletters', $tempDir . 'newsletters.html');

        return ['newsletters' => \count($written)];
    }
}
