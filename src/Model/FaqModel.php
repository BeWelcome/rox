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
     * Every FAQ of the category, the inactive ones too, in their order: the FAQ admin.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllFaqsForCategory(FaqCategory $faqCategory): array
    {
        return $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT f.* FROM faq f WHERE f.idCategory = ? ORDER BY f.SortOrder, f.id',
            [$faqCategory->getId()],
            [ParameterType::INTEGER],
        );
    }

    /**
     * Puts the FAQs of the category in the given order; ids of other categories are left out.
     *
     * @param list<int> $ids
     */
    public function sortFaqs(FaqCategory $faqCategory, array $ids): void
    {
        $faqs = $this->entityManager->getRepository(Faq::class)->findBy(['id' => $ids, 'category' => $faqCategory]);
        $positions = array_flip($ids);
        foreach ($faqs as $faq) {
            $faq->setSortOrder($positions[$faq->getId()]);
        }
        $this->entityManager->flush();
    }

    /**
     * Puts the FAQ categories in the given order.
     *
     * @param list<int> $ids
     */
    public function sortCategories(array $ids): void
    {
        $categories = $this->entityManager->getRepository(FaqCategory::class)->findBy(['id' => $ids]);
        $positions = array_flip($ids);
        foreach ($categories as $category) {
            $category->setSortOrder($positions[$category->getId()]);
        }
        $this->entityManager->flush();
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
