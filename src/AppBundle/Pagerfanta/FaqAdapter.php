<?php

namespace AppBundle\Pagerfanta;

use AppBundle\Entity\Faq;
use AppBundle\Entity\FaqCategory;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Pagerfanta\Adapter\AdapterInterface;

class FaqAdapter implements AdapterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var integer
     */
    private $categoryId;

    /**
     * FaqAdapter constructor.
     *
     * @param EntityManager $entityManager
     * @param FaqCategory $faqCategory
     */
    public function __construct(EntityManager $entityManager, FaqCategory $faqCategory)
    {
        $this->em= $entityManager;
        $this->categoryId = $faqCategory->getId();
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults()
    {
        $nbResults = 0;
        try {
            $connection = $this->em->getConnection();
            $stmt = $connection->prepare("SELECT 
    COUNT(*) AS nbResults
FROM
    faq f
LEFT JOIN
    faqcategories fc ON f.idCategory = fc.id    
LEFT JOIN
    words a ON a.code = CONCAT('FaqA_', f.qanda) and a.ShortCode = 'en'
LEFT JOIN
	words q ON q.code = CONCAT('FaqQ_', f.qanda) and q.ShortCode = 'en'");
            $stmt->execute();
            $nbResults = $stmt->fetchColumn();
        } catch (DBALException $e) {
        }
        return $nbResults;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        $entityManager = $this->em;
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Faq::class, 'f');
        $rsm->addMetaResult('f', 'Question', 'question');
        $rsm->addMetaResult('f', 'Answer', 'answer');

        $query = $entityManager->createNativeQuery("SELECT 
    f.*, q.Sentence as question, a.Sentence as answer
FROM
    faq f    
LEFT JOIN
    faqcategories fc ON f.idCategory = fc.id    
LEFT JOIN
    words a ON a.code = CONCAT('FaqA_', f.qanda) and a.ShortCode = 'en'
LEFT JOIN
	words q ON q.code = CONCAT('FaqQ_', f.qanda) and q.ShortCode = 'en'
WHERE fc.id = {$this->categoryId}	
ORDER BY 
  fc.SortOrder, f.SortOrder
LIMIT $length OFFSET $offset", $rsm);

        return $query->getResult();
    }
}
