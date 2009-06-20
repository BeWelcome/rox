<?php

/**
 * represents a single forum thread
 *
 */
class Thread extends RoxEntityBase
{

    protected $_table_name = 'forums_threads';


    public function __construct($thread_id = false)
    {
        parent::__construct();
        if (intval($thread_id))
        {
            $this->findById(intval($thread_id));
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


}
