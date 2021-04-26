<?php

namespace App\Entity;

use App\Entity\Member as Member;
use App\Entity\Newsletter as Newsletter;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Broadcastmessages.
 *
 * @ORM\Table(name="broadcastmessages")
 * @ORM\Entity
 */
class BroadcastMessage
{
    /**
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdEnqueuer", referencedColumnName="id")
     * })
     */
    private Member $enqueuedBy;

    /**
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private string $status = 'ToApprove';

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private DateTime $updated;

    /**
     * @ORM\Column(name="unsubscribe_key", type="string", length=64, nullable=true)
     */
    private ?string $unsubscribeKey;

    /**
     * @ORM\OneToOne(targetEntity="Newsletter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdBroadcast", referencedColumnName="id")
     * })
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private Newsletter $newsletter;

    /**
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdReceiver", referencedColumnName="id")
     * })
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private Member $receiver;

    public function setEnqueuedBy(Member $enqueuedBy): self
    {
        $this->enqueuedBy = $enqueuedBy;

        return $this;
    }

    public function getEnqueuedBy(): Member
    {
        return $this->enqueuedBy;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function setReceiver(Member $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function getUnsubscribeKey(): string
    {
        return $this->unsubscribeKey;
    }

    public function setUnsubscribeKey(?string $unsubscribeKey): self
    {
        $this->unsubscribeKey = $unsubscribeKey;

        return $this;
    }
}
