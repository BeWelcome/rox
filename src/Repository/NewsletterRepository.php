<?php

namespace App\Repository;

use App\Entity\Newsletter;
use App\Entity\Word;
use Doctrine\ORM\EntityRepository;

class NewsletterRepository extends EntityRepository
{
    public function findAllPublished()
    {
        $qb = $this->createQueryBuilder('n')
            ->where("n.status = 'Triggered'")
            ->andWhere('n.type = :type')
            ->setParameter('type', Newsletter::REGULAR_NEWSLETTER)
            ->orderBy('n.created', 'DESC')
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /*
     * Translated post content is only provided on explicit call to avoid long load times
     */
    public function getTranslations(Newsletter $newsletter): array
    {
        $name = $newsletter->getName();
        $translationRepository = $this->getEntityManager()->getRepository(Word::class);
        $translatedNews = $translationRepository->findBy([
            'code' => [
                'Broadcast_body_' . $name,
                'Broadcast_title_' . $name,
            ],
        ]);

        $newsletters = [];
        /** @var Word $item */
        foreach ($translatedNews as $item) {
            if (!isset($newsletters[$item->getLanguage()->getShortCode()])) {
                $newsletter = [];
            } else {
                $newsletter = $newsletters[$item->getLanguage()->getShortCode()];
            }
            // Determine if this is the title or the body of the newsletter (code is broadcast_title|body_$name)
            $part = str_ireplace('Broadcast_', '', str_ireplace('_' . $name, '', $item->getCode()));
            $newsletter[$part] = $item->getSentence();
            $newsletter['author'] = $item->getAuthor();
            $newsletter['locale'] = $item->getShortCode();
            $newsletters[$item->getLanguage()->getShortCode()] = $newsletter;
        }

        return $newsletters;
    }
}
