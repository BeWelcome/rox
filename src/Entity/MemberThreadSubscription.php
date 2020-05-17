<?php

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * MembersThreadsSubscribed
 *
 * @ORM\Table(name="members_threads_subscribed")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MemberThreadSubscription
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdSubscriber", referencedColumnName="id", nullable=false)
     */
    private $subscriber;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $subscribed;

    /**
     * @var ForumThread
     *
     * @ORM\ManyToOne(targetEntity="ForumThread")
     * @ORM\JoinColumn(name="IdThread", referencedColumnName="id", nullable=false)
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="ActionToWatch", type="string", nullable=false)
     */
    private $actionToWatch = 'replies';

    /**
     * @var string
     *
     * @ORM\Column(name="UnSubscribeKey", type="string", length=20, nullable=false)
     */
    private $unsubscribeKey;

    /**
     * @var bool
     *
     * @ORM\Column(name="notificationsEnabled", type="boolean", nullable=false)
     */
    private $notificationsEnabled = '1';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set subscriber
     *
     * @param Member $subscriber
     *
     * @return MemberThreadSubscription
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Get subscriber
     *
     * @return Member
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Set thread
     *
     * @param ForumThread $thread
     *
     * @return MemberThreadSubscription
     */
    public function setThread($thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get idthread
     *
     * @return ForumThread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set action to watch
     *
     * @param string $actionToWatch
     *
     * @return MemberThreadSubscription
     */
    public function setActionToWatch($actionToWatch)
    {
        $this->actionToWatch = $actionToWatch;

        return $this;
    }

    /**
     * Get actiontowatch
     *
     * @return string
     */
    public function getActionToWatch()
    {
        return $this->actionToWatch;
    }

    /**
     * Set unsubscribe key
     *
     * @param string $unsubscribeKey
     *
     * @return MemberThreadSubscription
     */
    public function setUnsubscribeKey($unsubscribeKey)
    {
        $this->unsubscribeKey = $unsubscribeKey;

        return $this;
    }

    /**
     * Get unsubscribe key
     *
     * @return string
     */
    public function getUnsubscribeKey()
    {
        return $this->unsubscribeKey;
    }

    /**
     * Set notifications enabled
     *
     * @param bool $notificationsEnabled
     *
     * @return MemberThreadSubscription
     */
    public function setNotificationsEnabled($notificationsEnabled)
    {
        $this->notificationsEnabled = $notificationsEnabled;

        return $this;
    }

    /**
     * Get notifications enabled
     *
     * @return bool
     */
    public function getNotificationsEnabled()
    {
        return $this->notificationsEnabled;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getSubscribed(): DateTime
    {
        return $this->subscribed;
    }

    /**
     * @param DateTime $subscribed
     */
    public function setSubscribed(DateTime $subscribed): void
    {
        $this->subscribed = $subscribed;
    }
}
