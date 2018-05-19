<?php

namespace AppBundle\Model;

use AppBundle\Entity\Faq;
use AppBundle\Entity\FaqCategory;
use AppBundle\Pagerfanta\FaqAdapter;
use AppBundle\Repository\FaqRepository;
use AppBundle\Repository\LogRepository;
use Doctrine\DBAL\DBALException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use PDO;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class FaqModel extends BaseModel
{
    /**
     * Returns a Pagerfanta object that contains the currently selected logs.
     *
     * @param array $types
     * @param $member
     * @param $ipAddress
     * @param int $page
     * @param int $limit
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredFaqs($page, $limit)
    {
        /** @var FaqRepository $repository */
        $repository = $this->em->getRepository(Faq::class);

        return $repository->findLatest($page, $limit);
    }

    public function getFaqs(FaqCategory $faqCategory, $page, $limit)
    {
        $paginator = new Pagerfanta(new FaqAdapter($this->em, $faqCategory));
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return array
     */
    public function getFaqCategories()
    {
        $categories = [];
        try {
            $connection = $this->em->getConnection();
            $stmt = $connection->prepare('
                SELECT 
                    `type`
                FROM
                  logs
                ORDER BY `type`
            ');
            $stmt->execute();
            $types = array_keys($stmt->fetchAll(PDO::FETCH_NUM | PDO::FETCH_UNIQUE));
            // Satisfy ChoiceType
            $types = array_combine($types, $types);
        } catch (DBALException $e) {
            // Return empty types array in case of DB problem.
        }

        return $categories;
    }
}
