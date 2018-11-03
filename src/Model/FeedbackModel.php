<?php

namespace App\Model;

use App\Entity\FeedbackCategory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class FeedbackModel extends BaseModel
{
    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param array $categories
     * @param int   $page
     * @param int   $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredFeedback(array $categories, $page, $limit)
    {
        $qb = $this->em->createQueryBuilder();
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
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($limit);
        $pagerFanta->setCurrentPage($page);

        return $pagerFanta;
    }

    public function getCategories()
    {
        $categoryRepository = $this->em->getRepository(FeedbackCategory::class);

        $categories = $categoryRepository->findAll();
        $mapped = [];
        foreach ($categories as $category) {
            $mapped[$category->getName()] = $category->getId();
        }

        return $mapped;
    }
}
