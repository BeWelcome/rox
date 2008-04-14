<?php


class MembersModel extends RoxModelBase
{
    public function getMemberWithUsername($username)
    {
        $values = $this->singleLookup_assoc(
            "
SELECT *
FROM members
WHERE Username = \"$username\"
            "
        );
        return new Member($values, $this->dao);
    }
    
    public function getMemberWithId($id)
    {
        $values = $this->singleLookup_assoc(
            "
SELECT *
FROM members
WHERE id = \"$id\"
            "
        );
        return new Member($values, $this);
    }
}




class Member extends RoxEntityBase
{
    public function construct($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    
    /**
     * automatically called by __get('trads'),
     * when someone writes '$member->trads'
     *
     * @return unknown
     */
    protected function get_trads()
    {
        $trads_for_member = $this->bulkLookup(
            "
SELECT SQL_CACHE *
FROM memberstrads
WHERE IdOwner = $this->id 
            "
        );
        
        $trads_by_tradid = array();
        foreach ($trads_for_member as $trad) {
            if (!isset($trads_by_tradid[$trad->IdTrad])) {
                $trads_by_tradid[$trad->IdTrad] = array();
            }
            $trads_by_tradid[$trad->IdTrad][$trad->IdLanguage] = $trad;
        }
        
        $field_names = array(
            'ILiveWith',
            'MaxLenghtOfStay',
            'MotivationForHospitality',
            'Offer',
            'Organizations',
            'AdditionalAccomodationInfo',
            'InformationToGuest',
            'Hobbies',
            'Books',
            'Music',
            'Movies',
            'PleaseBring',
            'OfferGuests',
            'OfferHosts',
            'PublicTransport',
            'PastTrips',
            'PlannedTrips'
        );
        
        $trads_by_fieldname = new stdClass();
        foreach ($field_names as $name) {
            if (!$trad_id = $this->$name) {
                // whatever
            } else if (!isset($trads_by_tradid[$trad_id])) {
                $trads_by_fieldname->$name = array();
            } else {
                $trads_by_fieldname->$name = $trads_by_tradid[$trad_id];
            }
        }
        return $trads_by_fieldname;
    }
    
    
    /**
     * automatically called by __get('group_memberships'),
     * when someone writes '$member->group_memberships'
     *
     * @return unknown
     */
    protected function get_group_memberships()
    {
        $groups_for_member = $this->bulkLookup(
            "
SELECT SQL_CACHE membersgroups.*, groups.*
FROM membersgroups, groups
WHERE membersgroups.IdMember = $this->id
AND membersgroups.IdGroup = groups.id
            "
        );
        
        foreach ($groups_for_member as $group) {
            $membership_trads = new stdClass();
        }
        return $trads;
    }
}


class GroupMembership extends RoxEntityBase
{
    public function construct($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    
}


?>