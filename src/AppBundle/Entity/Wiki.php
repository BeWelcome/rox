<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ewiki.
 *
 * @ORM\Table(name="ewiki")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WikiRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Wiki
{
    /**
     * @var int
     *
     * @ORM\Column(name="flags", type="integer", nullable=true)
     */
    private $flags = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=16777215, nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=100, nullable=true)
     */
    private $author = 'ewiki';

    /**
     * @var int
     *
     * @ORM\Column(name="created", type="integer", nullable=true)
     */
    private $created = '1168175948';

    /**
     * @var int
     *
     * @ORM\Column(name="lastmodified", type="integer", nullable=true)
     */
    private $lastmodified = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="refs", type="text", length=16777215, nullable=true)
     */
    private $refs;

    /**
     * @var string
     *
     * @ORM\Column(name="meta", type="text", length=16777215, nullable=true)
     */
    private $meta;

    /**
     * @var int
     *
     * @ORM\Column(name="hits", type="integer", nullable=true)
     */
    private $hits = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="pagename", type="string", length=160)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pagename;

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $version;

    /**
     * Set flags.
     *
     * @param int $flags
     *
     * @return Wiki
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Wiki
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set author.
     *
     * @param string $author
     *
     * @return Wiki
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return Wiki
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastmodified.
     *
     * @param int $lastmodified
     *
     * @return Wiki
     */
    public function setLastmodified($lastmodified)
    {
        $this->lastmodified = $lastmodified;

        return $this;
    }

    /**
     * Get lastmodified.
     *
     * @return int
     */
    public function getLastmodified()
    {
        return $this->lastmodified;
    }

    /**
     * Set refs.
     *
     * @param string $refs
     *
     * @return Wiki
     */
    public function setRefs($refs)
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * Get refs.
     *
     * @return string
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * Set meta.
     *
     * @param string $meta
     *
     * @return Wiki
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta.
     *
     * @return string
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set hits.
     *
     * @param int $hits
     *
     * @return Wiki
     */
    public function setHits($hits)
    {
        $this->hits = $hits;

        return $this;
    }

    /**
     * Get hits.
     *
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Set pagename.
     *
     * @param string $pagename
     *
     * @return Wiki
     */
    public function setPagename($pagename)
    {
        $this->pagename = $pagename;

        return $this;
    }

    /**
     * Get pagename.
     *
     * @return string
     */
    public function getPagename()
    {
        return $this->pagename;
    }

    /**
     * Set version.
     *
     * @param int $version
     *
     * @return Wiki
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
