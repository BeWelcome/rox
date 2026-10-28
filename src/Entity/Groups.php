<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * main's table of groups (`groups`), read only: develop keeps the groups in `group` (App\Entity\Group), filled from
 * this table by Version20261029100000. The table stays as long as main runs on the database.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'groups')]
#[ORM\Entity(readOnly: true)]
class Groups
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'HasMembers', type: 'string', length: 12, nullable: false, options: ['default' => 'HasMember'])]
    private string $hasMembers = 'HasMember';

    #[ORM\Column(name: 'Name', type: 'string', length: 50, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'Type', type: 'string', length: 14, nullable: false)]
    private string $type;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'NbChilds', type: 'integer', nullable: false, options: ['default' => 0])]
    private int $nbChilds = 0;

    #[ORM\Column(name: 'Picture', type: 'text', nullable: false)]
    private string $picture;

    #[ORM\Column(name: 'MoreInfo', type: 'text', nullable: false)]
    private string $moreInfo;

    #[ORM\Column(name: 'DisplayedOnProfile', type: 'string', length: 3, nullable: false, options: ['default' => 'Yes'])]
    private string $displayedOnProfile = 'Yes';

    /** The description: memberstrads.IdTrad. */
    #[ORM\Column(name: 'IdDescription', type: 'integer', nullable: true)]
    private ?int $idDescription = null;

    #[ORM\Column(name: 'VisiblePosts', type: 'string', length: 3, nullable: false, options: ['default' => 'yes'])]
    private string $visiblePosts = 'yes';

    #[ORM\Column(name: 'VisibleComments', type: 'string', length: 3, nullable: false, options: ['default' => 'no'])]
    private string $visibleComments = 'no';

    #[ORM\Column(name: 'approved', type: 'smallint', nullable: false)]
    private int $approved;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function getIdDescription(): ?int
    {
        return $this->idDescription;
    }

    public function getVisiblePosts(): string
    {
        return $this->visiblePosts;
    }

    public function getApproved(): int
    {
        return $this->approved;
    }
}
