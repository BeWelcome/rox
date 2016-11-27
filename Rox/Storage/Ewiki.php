<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ewiki
 *
 * @ORM\Table(name="ewiki")
 * @ORM\Entity
 */
class Ewiki
{
    /**
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="created", type="integer", nullable=true)
     */
    private $created = '1168175948';

    /**
     * @var integer
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
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $version;



    /**
     * Set flags
     *
     * @param integer $flags
     *
     * @return Ewiki
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Ewiki
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set author
     *
     * @param string $author
     *
     * @return Ewiki
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return Ewiki
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastmodified
     *
     * @param integer $lastmodified
     *
     * @return Ewiki
     */
    public function setLastmodified($lastmodified)
    {
        $this->lastmodified = $lastmodified;

        return $this;
    }

    /**
     * Get lastmodified
     *
     * @return integer
     */
    public function getLastmodified()
    {
        return $this->lastmodified;
    }

    /**
     * Set refs
     *
     * @param string $refs
     *
     * @return Ewiki
     */
    public function setRefs($refs)
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * Get refs
     *
     * @return string
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * Set meta
     *
     * @param string $meta
     *
     * @return Ewiki
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta
     *
     * @return string
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set hits
     *
     * @param integer $hits
     *
     * @return Ewiki
     */
    public function setHits($hits)
    {
        $this->hits = $hits;

        return $this;
    }

    /**
     * Get hits
     *
     * @return integer
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Set pagename
     *
     * @param string $pagename
     *
     * @return Ewiki
     */
    public function setPagename($pagename)
    {
        $this->pagename = $pagename;

        return $this;
    }

    /**
     * Get pagename
     *
     * @return string
     */
    public function getPagename()
    {
        return $this->pagename;
    }

    /**
     * Set version
     *
     * @param integer $version
     *
     * @return Ewiki
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }
}
