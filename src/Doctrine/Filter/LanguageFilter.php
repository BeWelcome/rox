<?php

namespace App\Doctrine\Filter;

use App\Doctrine\Annotation\LanguageAware;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class LanguageFilter extends SQLFilter
{
    /** @var Reader */
    protected $reader;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (empty($this->reader)) {
            return '';
        }

        // Only apply for entities marked as language aware through the matching annotation
        $languageAware = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(),
            LanguageAware::class
        );

        if (!$languageAware) {
            return '';
        }

        $fieldName = $languageAware->language;

        try {
            // Don't worry, getParameter automatically quotes parameters
            $language = $this->getParameter('language');
        } catch (\InvalidArgumentException $e) {
            // No language has been defined
            return '';
        }

        if (empty($fieldName) || empty($language)) {
            return '';
        }

        $query = sprintf('%s.%s = %s', $targetTableAlias, $fieldName, $language);

        return $query;
    }

    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }
}
