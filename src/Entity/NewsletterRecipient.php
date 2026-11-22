<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member who gets a mass mail (a newsletter, a reminder, a notice): enqueued (ToApprove), triggered (ToSend), then
 * Sent or Failed by send:massmail (App\Model\Admin\MassmailModel, App\Command\SendMassmailCommand). A sent one stays:
 * a member isn't enqueued twice for the same mass mail, its unsubscribe key is what the mail's unsubscribe link
 * carries (App\Model\SubscriptionModel), and the member's own data lists the mass mails they got.
 *
 * In a table of its own, `newsletter_recipient`, filled from main's `broadcastmessages`
 * (App\Entity\BroadcastMessage), which stays as main uses it: its Status is an enum that doesn't know the 'Freeze'
 * develop marked a failed mail with, which was stored as an empty status there. Here a failed mail is 'Failed'.
 */
#[ORM\Table(name: 'newsletter_recipient')]
#[ORM\Index(name: 'newsletter_recipient_status_idx', columns: ['status', 'updated'])]
#[ORM\Index(name: 'newsletter_recipient_unsubscribe_key_idx', columns: ['unsubscribe_key'])]
#[ORM\Entity]
class NewsletterRecipient
{
    public const string STATUS_TO_APPROVE = 'ToApprove';
    public const string STATUS_TO_SEND = 'ToSend';
    public const string STATUS_SENT = 'Sent';
    public const string STATUS_FAILED = 'Failed';

    #[ORM\ManyToOne(targetEntity: Newsletter::class)]
    #[ORM\JoinColumn(name: 'newsletter_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private Newsletter $newsletter;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'receiver_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private Member $receiver;

    /** The volunteer who enqueued the member; none when that volunteer is gone. */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'enqueued_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Member $enqueuedBy = null;

    #[ORM\Column(name: 'status', type: 'string', length: 16, nullable: false)]
    private string $status = self::STATUS_TO_APPROVE;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private DateTime $updated;

    /** Set when the mail is sent: what its unsubscribe link carries. */
    #[ORM\Column(name: 'unsubscribe_key', type: 'string', length: 64, nullable: true)]
    private ?string $unsubscribeKey = null;

    public function setEnqueuedBy(?Member $enqueuedBy): self
    {
        $this->enqueuedBy = $enqueuedBy;

        return $this;
    }

    public function getEnqueuedBy(): ?Member
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

    public function setReceiver(Member $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function getUnsubscribeKey(): ?string
    {
        return $this->unsubscribeKey;
    }

    public function setUnsubscribeKey(?string $unsubscribeKey): self
    {
        $this->unsubscribeKey = $unsubscribeKey;

        return $this;
    }
}
