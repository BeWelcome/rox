<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shouts: short comments members left on gallery items, groups and trips (2008 to 2019). The feature is gone
 * from the site, but the rows stay, so "Download my data" still lists them (templates/private/shouts.html.twig).
 * Read-only: nothing writes shouts any more.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'shouts')]
#[ORM\Entity]
class Shout
{
    public const string GALLERY_ITEM = 'gallery_items';
    public const string GROUP = 'groups';
    public const string TRIP = 'trip';
    public const string GALLERY = 'gallery';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id_foreign', referencedColumnName: 'id', nullable: false)]
    private Member $member;

    // `table` is a reserved word in SQL: the backticks make Doctrine quote it
    #[ORM\Column(name: '`table`', type: 'string', length: 75)]
    private string $table;

    #[ORM\Column(name: 'table_id', type: 'integer')]
    private int $tableId;

    #[ORM\Column(name: 'created', type: 'datetime')]
    private DateTime $created;

    #[ORM\Column(name: 'title', type: 'string', length: 75)]
    private string $title;

    #[ORM\Column(name: 'text', type: 'text')]
    private string $text;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getTableId(): int
    {
        return $this->tableId;
    }
}
