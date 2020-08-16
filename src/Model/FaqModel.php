<?php

namespace App\Model;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Repository\FaqRepository;
use App\Utilities\ManagerTrait;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;

class FaqModel
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
    public function getFilteredFaqs($page, $limit)
    {
        /** @var FaqRepository $repository */
        $repository = $this->getManager()->getRepository(Faq::class);

        return $repository->findLatest($page, $limit);
    }

    public function getFaqsForCategory(FaqCategory $faqCategory)
    {
        $connection = $this->getManager()->getConnection();
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

        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @return array
     */
    public function getFaqCategories()
    {
        /** @var EntityRepository $repository */
        $repository = $this->getManager()->getRepository(FaqCategory::class);

        return $repository->findBy([], ['sortOrder' => 'ASC']);
    }
}
