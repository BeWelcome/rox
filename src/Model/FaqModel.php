<?php

namespace App\Model;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use App\Repository\FaqRepository;
use App\Utilities\ManagerTrait;
use Doctrine\DBAL\DBALException;
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
        try {
            $connection = $this->getManager()->getConnection();
            $stmt = $connection->prepare(
                "SELECT 
    f.*, q.Sentence as Question, a.Sentence as Answer
FROM
    faq f    
LEFT JOIN
    faqcategories fc ON f.idCategory = fc.id    
LEFT JOIN
    words a ON a.code = CONCAT('FaqA_', f.qanda) and a.ShortCode = 'en'
LEFT JOIN
	words q ON q.code = CONCAT('FaqQ_', f.qanda) and q.ShortCode = 'en'
WHERE fc.id = :categoryId	
ORDER BY 
    fc.SortOrder, f.SortOrder"
            );
            $stmt->bindValue(':categoryId', $faqCategory->getId(), ParameterType::INTEGER);

            $stmt->execute();
            $results = $stmt->fetchAll();
        } catch (DBALException $e) {
            $results = [];
        }

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
