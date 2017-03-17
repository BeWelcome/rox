<?php

namespace AppBundle\Model;

use AppBundle\Entity\Language;
use AppBundle\Repository\LanguageRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Pagerfanta\Pagerfanta;

class LanguageModel extends BaseModel
{
    /**
     * Returns all languages for which translations exist
     *
     * @return array Language
     */
    public function getLanguagesWithTranslations( $locale )
    {
        $entityManager = $this->em;

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Language::class, 'l');
//         $rsm->addFieldResult('l', 'Sentence', 'translatedname');

        $query = $entityManager->createNativeQuery("SELECT 
    l.*, w2.Sentence 
FROM
    languages l
LEFT JOIN
    words w1 ON l.id = w1.IdLanguage
left JOIN
	words w2 ON w2.ShortCode = '{$locale}' and w2.Code = l.WordCode 
WHERE
    w1.code = 'WelcomeToSignup'
ORDER BY name ASC", $rsm);

        $languages = $query->getResult();
        return $languages;
    }
}