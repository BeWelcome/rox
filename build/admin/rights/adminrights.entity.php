<?php

/**
 * represents a single right
 *
 */
class Right extends RoxEntityBase
{
    protected $_table_name = 'rights';

    public function __construct($rightId = false)
    {
        parent::__construct();
        if ($rightId)
        {
            $this->findById($rightId);
        }
    }

    /**
     * overloads RoxEntityBase::loadEntity to load related data
     *
     * @param array $data
     *
     * @access protected
     * @return bool
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
        }
        return $status;
    }

    /**
     * get all members with $this right
     *
     * @access public
     * @return list of Activities
     */
    public function getMembersForRight() {
        $activities = array();
        $activities = $this->findBySQLMany('');
        return $activities;
    }
}