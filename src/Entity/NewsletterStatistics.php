<?php

namespace App\Entity;

use App\Repository\NewsletterStatisticsRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * How many recipients of a mass mail are enqueued, triggered, sent and failed. The pages read these counts instead
 * of counting the messages; they are changed with the messages (NewsletterStatisticsRepository::add()).
 */
#[ORM\Table(name: 'broadcast_statistics')]
#[ORM\Entity(repositoryClass: NewsletterStatisticsRepository::class)]
class NewsletterStatistics
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Newsletter::class)]
    #[ORM\JoinColumn(name: 'broadcast_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Newsletter $newsletter;

    #[ORM\Column(name: 'enqueued', type: 'integer', options: ['default' => 0, 'unsigned' => true])]
    private int $enqueued = 0;

    #[ORM\Column(name: 'triggered', type: 'integer', options: ['default' => 0, 'unsigned' => true])]
    private int $triggered = 0;

    #[ORM\Column(name: 'sent', type: 'integer', options: ['default' => 0, 'unsigned' => true])]
    private int $sent = 0;

    #[ORM\Column(name: 'failed', type: 'integer', options: ['default' => 0, 'unsigned' => true])]
    private int $failed = 0;

    #[ORM\Column(name: 'updated', type: 'datetime')]
    private DateTime $updated;

    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
        $this->updated = new DateTime();
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function getEnqueued(): int
    {
        return $this->enqueued;
    }

    public function getTriggered(): int
    {
        return $this->triggered;
    }

    public function getSent(): int
    {
        return $this->sent;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated;
    }
}
