<?php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

#[ORM\Table(name: 'geo__names_translations')]
#[ORM\Index(name: 'geo__names_translation_idx', columns: ['locale', 'object_class', 'field', 'foreign_key'])]
#[ORM\Entity(repositoryClass: \Gedmo\Translatable\Entity\Repository\TranslationRepository::class)]
class NewLocationTranslation extends AbstractTranslation
{
    /*
     * All required columns are mapped through inherited superclass
     */
}
