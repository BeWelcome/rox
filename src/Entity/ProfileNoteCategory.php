<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A category of a member's private note (App\Entity\ProfileNote): a note has one or more, each a name the member
 * chose (the ones they used before are offered again).
 */
#[ORM\Table(name: 'note_category')]
#[ORM\UniqueConstraint(name: 'note_category_unique', columns: ['note_id', 'name'])]
#[ORM\Index(name: 'note_category_name_idx', columns: ['name'])]
#[ORM\Entity]
class ProfileNoteCategory
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(
        #[ORM\JoinColumn(name: 'note_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne(targetEntity: ProfileNote::class, inversedBy: 'categories')]
        private ProfileNote $note,
        #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
        private string $name,
    ) {
    }

    public function getNote(): ProfileNote
    {
        return $this->note;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
