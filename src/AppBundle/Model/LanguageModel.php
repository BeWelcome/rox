<?php

namespace AppBundle\Model;

use AppBundle\Entity\Language;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class LanguageModel extends BaseModel
{
    /**
     * Returns all languages for which translations exist.
     *
     * @param mixed $locale
     *
     * @return array Language
     */
    public function getLanguagesWithTranslations($locale)
    {
        $entityManager = $this->em;

        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata(Language::class, 'l');
        // $rsm->addFieldResult('l', 'TranslatedName', 'translatedname');

        $query = $entityManager->createNativeQuery("SELECT 
    l.*, IFNULL(w2.Sentence, l.EnglishName) as TranslatedName 
FROM
    languages l
LEFT JOIN
    words w1 ON l.id = w1.IdLanguage
left JOIN
	words w2 ON w2.ShortCode = '{$locale}' and w2.Code = l.WordCode 
WHERE
    w1.code = 'WelcomeToSignup'
ORDER BY Name ASC", $rsm);

        $languages = $query->getResult('LanguageHydrator');
        $locales = array_map(function ($language) {
            return $language['ShortCode'];
        }, $languages);
        $merged = array_combine($locales, $languages);

        return $merged;
    }
}
