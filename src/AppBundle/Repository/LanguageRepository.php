<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Language;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 */
class LanguageRepository extends EntityRepository
{
    /**
     * Returns all languages for which translations exist
     *
     * @return array Language
     */
    public function getLanguagesWithTranslations()
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Language::class, 'l');

        $query = $entityManager->createNativeQuery("SELECT * from languages JOIN words on languages.id = words.IdLanguage where code='WelcomeToSignup' ORDER BY name ASC", $rsm);
        return $query->getResult();
    }
}
