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

    /**
     * The languages each newsletter has a text in, in one query.
     *
     * @param list<string> $names
     *
     * @return array<string, list<string>> language codes by lower-cased newsletter name, in the database's order
     */
    public function findLanguagesByName(array $names): array
    {
        if ([] === $names) {
            return [];
        }

        $codes = [];
        foreach ($names as $name) {
            $codes[strtolower('broadcast_body_' . $name)] = strtolower($name);
        }

        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('w.code', 'IDENTITY(w.language) AS language')
            ->from(Word::class, 'w')
            ->where('w.code IN (:codes)')
            ->setParameter('codes', array_keys($codes))
            ->orderBy('w.code')
            ->addOrderBy('w.language')
            ->getQuery()
            ->getArrayResult();

        $languages = array_fill_keys(array_values($codes), []);
        foreach ($rows as $row) {
            $name = $codes[strtolower($row['code'])] ?? null;
            if (null !== $name) {
                $languages[$name][] = $row['language'];
            }
        }

        return $languages;
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
            $shortCode = $item->getLanguage()->getShortCode();
            if (!isset($newsletters[$shortCode])) {
                $newsletter = [];
            } else {
                $newsletter = $newsletters[$shortCode];
            }
            // Determine if this is the title or the body of the newsletter (code is broadcast_title|body_$name)
            $part = str_ireplace('Broadcast_', '', str_ireplace('_' . $name, '', $item->getCode()));
            $newsletter[$part] = $item->getSentence();
            $newsletter['author'] = $item->getAuthor();
            $newsletter['locale'] = $shortCode;
            $newsletters[$shortCode] = $newsletter;
        }

        return $newsletters;
    }
}
