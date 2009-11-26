<?php

class ProfileVisit extends RoxEntityBase
{
    protected $_table_name = 'profilesvisits';

    /**
     * records a visit on one members profile from another member
     *
     * @param Member $visited - visited profile
     * @param Member $visitor - visiting member
     *
     * @access public
     * @return bool
     */
    public function recordVisit(Member $visited, Member $visitor)
    {
        if (!$visited->isLoaded() || !$visitor->isLoaded())
        {
            return false;
        }
        // todo: refactor pending implementation of replace method in Entity
        // todo: fix bad table model (created column is always updated when no value is set)
        $sql = "REPLACE INTO profilesvisits (IdMember, IdVisitor, updated) VALUES ({$visited->getPKValue()}, {$visitor->getPKValue()}, now())";
        $result = $this->dao->query($sql);
    }

    /**
     * returns all members, that have visited $members profile
     *
     * @param Member $member - profile to check visits for
     *
     * @access public
     * @return array
     */
    public function getVisitsForMember(Member $member)
    {
        if (!$member->isLoaded())
        {
            return array();
        }
        return $this->findByWhereMany("IdMember = $member->getPKValue() ORDER BY updated DESC");
    }
}
