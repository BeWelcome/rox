<?php

/**
 * represents a single message
 *
 */
class Message extends RoxEntityBase
{


    /**
     * overrides the __get method of Component
     * in order fix the output
     *
     * @param string $key - variable to get
     * @return mixed
     * @access public
     */
    public function __get($key)
    {
        $result = parent::__get($key);
        if (is_scalar($result))
        {
            $result = stripslashes($result);
        }
        return $result;
    }

    /**
     * overrides the parent constructor and calls it afterwards
     *
     * @param array $ini_data
     * @param int $message_id
     * @access public
     */
    public function __construct($ini_data, $message_id = false)
    {
        parent::__construct($ini_data);
        if (intval($message_id))
        {
            $this->findById(intval($message_id));
        }
    }


    /**
     * Uses an array of terms to create a create to search for messages with
     * simple or search on names for now
     *
     * @todo implement proper search and refactor entities - this will wait on various db implementations
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

