<?php


/**
 * represents a single blog post
 *
 */
class Blog extends RoxEntityBase
{

    protected $_table_name = 'blog';

    public function __construct($blog_id = false)
    {
        parent::__construct();
        if (intval($blog_id))
        {
            $this->findById(intval($blog_id));
        }
    }

    /**
     * returns a blogdata entity representing the additional blog data like geonameid etc.
     *
     * @return mixed - member entity or false
     * @access public
     */
    public function getBlogData()
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        return $this->createEntity('BlogData', $this->id);
    }

}

