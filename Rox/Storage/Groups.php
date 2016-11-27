<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groups
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity
 */
class Groups
{
    /**
     * @var string
     *
     * @ORM\Column(name="HasMembers", type="string", nullable=false)
     */
    private $hasmembers = 'HasMember';

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=40, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'Public';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="NbChilds", type="integer", nullable=false)
     */
    private $nbchilds = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Picture", type="text", length=65535, nullable=false)
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="MoreInfo", type="text", length=65535, nullable=false)
     */
    private $moreinfo;

    /**
     * @var string
     *
     * @ORM\Column(name="DisplayedOnProfile", type="string", nullable=false)
     */
    private $displayedonprofile = 'Yes';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdDescription", type="integer", nullable=true)
     */
    private $iddescription;

    /**
     * @var string
     *
     * @ORM\Column(name="VisiblePosts", type="string", nullable=false)
     */
    private $visibleposts = 'yes';

    /**
     * @var string
     *
     * @ORM\Column(name="VisibleComments", type="string", nullable=false)
     */
    private $visiblecomments = 'no';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set hasmembers
     *
     * @param string $hasmembers
     *
     * @return Groups
     */
    public function setHasmembers($hasmembers)
    {
        $this->hasmembers = $hasmembers;

        return $this;
    }

    /**
     * Get hasmembers
     *
     * @return string
     */
    public function getHasmembers()
    {
        return $this->hasmembers;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Groups
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Groups
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Groups
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
     * Set nbchilds
     *
     * @param integer $nbchilds
     *
     * @return Groups
     */
    public function setNbchilds($nbchilds)
    {
        $this->nbchilds = $nbchilds;

        return $this;
    }

    /**
     * Get nbchilds
     *
     * @return integer
     */
    public function getNbchilds()
    {
        return $this->nbchilds;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Groups
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set moreinfo
     *
     * @param string $moreinfo
     *
     * @return Groups
     */
    public function setMoreinfo($moreinfo)
    {
        $this->moreinfo = $moreinfo;

        return $this;
    }

    /**
     * Get moreinfo
     *
     * @return string
     */
    public function getMoreinfo()
    {
        return $this->moreinfo;
    }

    /**
     * Set displayedonprofile
     *
     * @param string $displayedonprofile
     *
     * @return Groups
     */
    public function setDisplayedonprofile($displayedonprofile)
    {
        $this->displayedonprofile = $displayedonprofile;

        return $this;
    }

    /**
     * Get displayedonprofile
     *
     * @return string
     */
    public function getDisplayedonprofile()
    {
        return $this->displayedonprofile;
    }

    /**
     * Set iddescription
     *
     * @param integer $iddescription
     *
     * @return Groups
     */
    public function setIddescription($iddescription)
    {
        $this->iddescription = $iddescription;

        return $this;
    }

    /**
     * Get iddescription
     *
     * @return integer
     */
    public function getIddescription()
    {
        return $this->iddescription;
    }

    /**
     * Set visibleposts
     *
     * @param string $visibleposts
     *
     * @return Groups
     */
    public function setVisibleposts($visibleposts)
    {
        $this->visibleposts = $visibleposts;

        return $this;
    }

    /**
     * Get visibleposts
     *
     * @return string
     */
    public function getVisibleposts()
    {
        return $this->visibleposts;
    }

    /**
     * Set visiblecomments
     *
     * @param string $visiblecomments
     *
     * @return Groups
     */
    public function setVisiblecomments($visiblecomments)
    {
        $this->visiblecomments = $visiblecomments;

        return $this;
    }

    /**
     * Get visiblecomments
     *
     * @return string
     */
    public function getVisiblecomments()
    {
        return $this->visiblecomments;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
