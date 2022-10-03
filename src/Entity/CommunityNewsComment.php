<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Community News Comments.
 *
 * @ORM\Table(name="community_news_comment")
 * @ORM\Entity(repositoryClass="App\Repository\CommunityNewsCommentRepository")
 * @ORM\HasLifecycleCallbacks
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class CommunityNewsComment
{
    /**
     * @var communityNews
     *
     * A news has many comments
     * @ORM\ManyToOne(targetEntity="CommunityNews", inversedBy="comments")
     */
    private $communityNews;

    /**
     * @var Member
     *
     * A comment has one author
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return CommunityNewsComment
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text.
     *
     * @param string $text
     *
     * @return CommunityNewsComment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CommunityNews
     */
    public function getCommunityNews()
    {
        return $this->communityNews;
    }

    /**
     * @param CommunityNews $communityNews
     *
     * @return CommunityNewsComment
     */
    public function setCommunityNews($communityNews)
    {
        $this->communityNews = $communityNews;

        return $this;
    }

    public function getAuthor(): Member
    {
        return $this->author;
    }

    /**
     * @return CommunityNewsComment
     */
    public function setAuthor(Member $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }
}
