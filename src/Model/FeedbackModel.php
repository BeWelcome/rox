<?php

namespace App\Model;

use App\Entity\FeedbackCategory;
use App\Utilities\ManagerTrait;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackModel
{
    use ManagerTrait;

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
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('f')
            ->from('App:Feedback', 'f');
        if (!empty($categories)) {
            $qb->where('f.category in (:categories)')
                ->setParameter('categories', $categories);
        }
        $qb
            ->orderBy('f.created', 'DESC');

        $adapter = new DoctrineORMAdapter($qb, true);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    public function getCategories()
    {
        $categoryRepository = $this->getManager()->getRepository(FeedbackCategory::class);

        $categories = $categoryRepository->findAll();
        $mapped = [];
        foreach ($categories as $category) {
            $mapped[$category->getName()] = $category->getId();
        }

        return $mapped;
    }
}
