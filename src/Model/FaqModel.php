<?php

namespace App\Model;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Repository\FaqRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;

class FaqModel
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
    public function getFilteredFaqs($page, $limit)
    {
        /** @var FaqRepository $repository */
        $repository = $this->entityManager->getRepository(Faq::class);

        return $repository->findLatest($page, $limit);
    }

    public function getFaqsForCategory(FaqCategory $faqCategory): mixed
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare(
            "SELECT
    f.*
FROM
    faq f
LEFT JOIN
    faqcategories fc ON f.idCategory = fc.id
WHERE
    fc.id = :categoryId
    AND f.Active = 'Active'
ORDER BY
    f.SortOrder"
        );
        $stmt->bindValue(':categoryId', $faqCategory->getId(), ParameterType::INTEGER);

        $results = $stmt->executeQuery()->fetchAllAssociative();

        return $results;
    }

    /**
     * @return array
     */
    public function getFaqCategories()
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(FaqCategory::class);

        return $repository->findBy([], ['sortOrder' => 'ASC']);
    }
}
