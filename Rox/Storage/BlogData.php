<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BlogData
 *
 * @ORM\Table(name="blog_data", indexes={@ORM\Index(name="blog_title", columns={"blog_title"}), @ORM\Index(name="blog_text", columns={"blog_text"})})
 * @ORM\Entity
 */
class BlogData
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="edited", type="datetime", nullable=true)
     */
    private $edited;

    /**
     * @var string
     *
     * @ORM\Column(name="blog_title", type="string", length=255, nullable=false)
     */
    private $blogTitle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="blog_text", type="text", nullable=false)
     */
    private $blogText;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="blog_start", type="datetime", nullable=true)
     */
    private $blogStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="blog_end", type="datetime", nullable=true)
     */
    private $blogEnd;

    /**
     * @var float
     *
     * @ORM\Column(name="blog_latitude", type="float", precision=10, scale=0, nullable=false)
     */
    private $blogLatitude = '0';

    /**
     * @var float
     *
     * @ORM\Column(name="blog_longitude", type="float", precision=10, scale=0, nullable=false)
     */
    private $blogLongitude = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_geonameid", type="integer", nullable=true)
     */
    private $blogGeonameid;

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_display_order", type="integer", nullable=false)
     */
    private $blogDisplayOrder = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blogId;



    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return BlogData
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * Set blogTitle
     *
     * @param string $blogTitle
     *
     * @return BlogData
     */
    public function setBlogTitle($blogTitle)
    {
        $this->blogTitle = $blogTitle;

        return $this;
    }

    /**
     * Get blogTitle
     *
     * @return string
     */
    public function getBlogTitle()
    {
        return $this->blogTitle;
    }

    /**
     * Set blogText
     *
     * @param string $blogText
     *
     * @return BlogData
     */
    public function setBlogText($blogText)
    {
        $this->blogText = $blogText;

        return $this;
    }

    /**
     * Get blogText
     *
     * @return string
     */
    public function getBlogText()
    {
        return $this->blogText;
    }

    /**
     * Set blogStart
     *
     * @param \DateTime $blogStart
     *
     * @return BlogData
     */
    public function setBlogStart($blogStart)
    {
        $this->blogStart = $blogStart;

        return $this;
    }

    /**
     * Get blogStart
     *
     * @return \DateTime
     */
    public function getBlogStart()
    {
        return $this->blogStart;
    }

    /**
     * Set blogEnd
     *
     * @param \DateTime $blogEnd
     *
     * @return BlogData
     */
    public function setBlogEnd($blogEnd)
    {
        $this->blogEnd = $blogEnd;

        return $this;
    }

    /**
     * Get blogEnd
     *
     * @return \DateTime
     */
    public function getBlogEnd()
    {
        return $this->blogEnd;
    }

    /**
     * Set blogLatitude
     *
     * @param float $blogLatitude
     *
     * @return BlogData
     */
    public function setBlogLatitude($blogLatitude)
    {
        $this->blogLatitude = $blogLatitude;

        return $this;
    }

    /**
     * Get blogLatitude
     *
     * @return float
     */
    public function getBlogLatitude()
    {
        return $this->blogLatitude;
    }

    /**
     * Set blogLongitude
     *
     * @param float $blogLongitude
     *
     * @return BlogData
     */
    public function setBlogLongitude($blogLongitude)
    {
        $this->blogLongitude = $blogLongitude;

        return $this;
    }

    /**
     * Get blogLongitude
     *
     * @return float
     */
    public function getBlogLongitude()
    {
        return $this->blogLongitude;
    }

    /**
     * Set blogGeonameid
     *
     * @param integer $blogGeonameid
     *
     * @return BlogData
     */
    public function setBlogGeonameid($blogGeonameid)
    {
        $this->blogGeonameid = $blogGeonameid;

        return $this;
    }

    /**
     * Get blogGeonameid
     *
     * @return integer
     */
    public function getBlogGeonameid()
    {
        return $this->blogGeonameid;
    }

    /**
     * Set blogDisplayOrder
     *
     * @param integer $blogDisplayOrder
     *
     * @return BlogData
     */
    public function setBlogDisplayOrder($blogDisplayOrder)
    {
        $this->blogDisplayOrder = $blogDisplayOrder;

        return $this;
    }

    /**
     * Get blogDisplayOrder
     *
     * @return integer
     */
    public function getBlogDisplayOrder()
    {
        return $this->blogDisplayOrder;
    }

    /**
     * Get blogId
     *
     * @return integer
     */
    public function getBlogId()
    {
        return $this->blogId;
    }
}
