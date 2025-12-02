<?php

namespace App\Model;

use App\Entity\Feedback;
use App\Entity\FeedbackCategory;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getFilteredFeedback(array $categories, $page, $limit)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('f')
            ->from(Feedback::class, 'f');
        if (!empty($categories)) {
            $qb->where('f.category in (:categories)')
                ->setParameter('categories', $categories);
        }
        $qb
            ->orderBy('f.created', 'DESC');

        $adapter = new QueryAdapter($qb, true);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    public function getCategories()
    {
        $categoryRepository = $this->entityManager->getRepository(FeedbackCategory::class);

        $categories = $categoryRepository->findAll();
        $mapped = [];
        foreach ($categories as $category) {
            $mapped[$category->getName()] = $category->getId();
        }

        return $mapped;
    }
}
