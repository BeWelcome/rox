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
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdEnqueuer", referencedColumnName="id")
     * })
     */
    private $enqueuedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'ToApprove';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var Newsletter
     *
     * @ORM\OneToOne(targetEntity="Newsletter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdBroadcast", referencedColumnName="id")
     * })
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $newsletter;

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdReceiver", referencedColumnName="id")
     * })
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $receiver;

    /**
     * Set enqueuedBy.
     *
     * @param Member $enqueuedBy
     *
     * @return BroadcastMessage
     */
    public function setEnqueuedBy($enqueuedBy)
    {
        $this->enqueuedBy = $enqueuedBy;

        return $this;
    }

    /**
     * Get enqueuedBy.
     *
     * @return Member
     */
    public function getEnqueuedBy()
    {
        return $this->enqueuedBy;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return BroadcastMessage
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return BroadcastMessage
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return BroadcastMessage
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter.
     *
     * @return Newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set receiver.
     *
     * @param Member $receiver
     *
     * @return BroadcastMessage
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver.
     *
     * @return Member
     */
    public function getReceiver()
    {
        return $this->receiver;
    }
}
