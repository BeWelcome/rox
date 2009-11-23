<?php

class VolunteerBoard extends RoxEntityBase
{
    protected $_table_name = 'volunteer_boards';

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id)
        {
            $this->findById($id);
        }
    }

    /**
     * fetches a board by name
     *
     * @param string $name - name of board
     *
     * @access public
     * @return false|VolunteerBoard
     */
    public function findByName($name)
    {
        return $this->findByWhere("name = '{$this->dao->escape($name)}'");
    }
}
