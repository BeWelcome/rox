<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * Title and description of a poll in one language (Gedmo personal translation, as MemberTranslation).
 */
#[ORM\Table(name: 'poll_translation')]
#[ORM\UniqueConstraint(name: 'lookup_unique_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity]
class PollTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;

    public function __construct(string $locale, string $field, string $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}
