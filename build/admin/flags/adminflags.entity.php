<?php

/**
 * represents a single flag
 *
 */
class Flag extends RoxEntityBase
{
    protected $_table_name = 'flags';

    public function __construct($flagId = false)
    {
        parent::__construct();
        if ($flagId)
        {
            $this->findById($flagId);
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

    public function getFlagForMember(Member $member) {
        $query = "
            SELECT
                *
            FROM
                flagsmembers fm
            WHERE
                fm.IdFlag = " . $this->id . "
                AND fm.IdMember = " . $member->id . "
            ";
        return $this->singleLookup($query);
    }
}