<?php

/**
 * represents a single forum post
 *
 */
class Post extends RoxEntityBase
{

    protected $_table_name = 'forums_posts';


    public function __construct($post_id = false)
    {
        parent::__construct();
        if (intval($post_id))
        {
            $this->findByPostId(intval($post_id));
        }
    }


    /**
     * Uses an array of terms to create a create to search for groups with
     * simple or search on names for now
     *
     * @todo implement proper group search - this will wait on various db implementations
     * @param array $terms - array of strings to be used in search
     * @return mixed false or group of arrays that match any of the terms
     * @access public
     */
    public function findBySearchTerms($terms = array(), $page = 0)
    {
        if (empty($terms))
        {
            return $this->findAll($page, 10);
        }
        
        foreach ($terms as &$term)
        {
            if (is_string($term))
            {
                $term = "{$this->_table_name}.Name LIKE '%" . $this->dao->escape($term) . "%'";
            }
            else
            {
                unset($term);
            }
        }
        
        $clause = implode(' or ', $terms);

        return $this->findByWhereMany($clause, $page, 10);

    }

    /**
     * returns a count of how many posts a member has made
     *
     * @param object $member - Member entity
     * @access public
     * @return int
     */
    public function getMemberPostCount(Member $member)
    {
        if (!$member->isLoaded())
        {
            return 0;
        }
        return $this->countWhere("IdWriter = '{$member->getPKValue()}'");
    }
}
