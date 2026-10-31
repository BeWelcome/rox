<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Override;

/**
 * The name of a group category in one language (Gedmo personal translation, as GroupTranslation).
 */
#[ORM\Table(name: 'group_category_translation')]
#[ORM\UniqueConstraint(name: 'lookup_unique_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity]
class GroupCategoryTranslation extends AbstractPersonalTranslation
{
    public const string NAME = 'name';

    #[ORM\ManyToOne(targetEntity: GroupCategory::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Override]
    protected $object;

    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
