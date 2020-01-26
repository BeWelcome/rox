<?php

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostsNotificationqueue
 *
 * @ORM\Table(name="posts_notificationqueue", indexes={@ORM\Index(name="IdxStatus", columns={"Status"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class PostNotificationQueue
{
    use LifecycleCallbacksTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'ToSend';

    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $member;

    /**
     * @var ForumPost
     *
     * @ORM\ManyToOne(targetEntity="ForumPost")
     * @ORM\JoinColumn(name="IdPost", referencedColumnName="id")
     */
    private $post;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'buggy';

    /**
     * @var int
     *
     * @ORM\Column(name="IdSubscription", type="integer", nullable=false)
     */
    private $subscription = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="TableSubscription", type="string", length=64, nullable=false)
     */
    private $tablesubscription = 'NotSet';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set status
     *
     * @param string $status
     *
     * @return PostNotificationQueue
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set idmember
     *
     * @param int $member
     *
     * @return PostNotificationQueue
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return int
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set idpost
     *
     * @param int $post
     *
     * @return PostNotificationQueue
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get idpost
     *
     * @return int
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return PostNotificationQueue
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return PostNotificationQueue
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return PostNotificationQueue
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set idsubscription
     *
     * @param int $subscription
     *
     * @return PostNotificationQueue
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get idsubscription
     *
     * @return int
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set tablesubscription
     *
     * @param string $tablesubscription
     *
     * @return PostNotificationQueue
     */
    public function setTablesubscription($tablesubscription)
    {
        $this->tablesubscription = $tablesubscription;

        return $this;
    }

    /**
     * Get tablesubscription
     *
     * @return string
     */
    public function getTablesubscription()
    {
        return $this->tablesubscription;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
