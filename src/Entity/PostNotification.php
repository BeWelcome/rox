<?php

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * PostNotification
 *
 * @ORM\Table(name="posts_notificationqueue", indexes={@ORM\Index(name="IdxStatus", columns={"Status"})})
 * @ORM\Entity(repositoryClass="App\Repository\PostNotificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PostNotification
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
    private $receiver;

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
     * @return PostNotification
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
     * @param int $receiver
     *
     * @return PostNotification
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get member
     *
     * @return Member
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set post
     *
     * @param ForumPost $post
     *
     * @return PostNotification
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return ForumPost
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return PostNotification
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
     * @return PostNotification
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
     * @return PostNotification
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
