<?php

//todo: base group atom on different class

/**
 * represents a single group
 *
 */
class BlogData extends RoxEntityBase
{

    protected $_table_name = 'blog_data';

    public function __construct($blog_id = false)
    {
        parent::__construct();
        if (intval($blog_id))
        {
            $this->findById(intval($blog_id));
        }
    }

}

