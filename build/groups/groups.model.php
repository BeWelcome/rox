<?php


class GroupsModel extends PAppModel
{
    private $_group_list = 0;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function findGroup($group_id)
    {
        $group = new Group($group_id);
        if ($group->getData()) return $group;
        else return 0;
    }
    
    public function getGroups()
    {
        if ($this->_group_list != 0) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT *
FROM groups
            "
        )) {
            // db query went wrong
        } else {
            $this->_group_list = array();
            while ($group_data = $result->fetch(PDB::FETCH_OBJ)) {
                $this->_group_list[] = $group_data;
            }
        }
        return $this->_group_list;
    }
    
    public function getMyGroups()
    {
        if (!isset($_SESSION['IdMember'])) {
            return array();
        } else {
            return $this->getGroupsForMember($_SESSION['IdMember']);
        }
    }
    
    public function getGroupsForMember($member_id)
    {
        if (!$result = $this->dao->query(
            "
SELECT groups.*
FROM groups, membersgroups
WHERE membersgroups.IdGroup = groups.id
AND membersgroups.IdMember = $member_id
            "
        )) {
            // db query went wrong
            return 0;
        } else {
            $group_list = array();
            while ($group_data = $result->fetch(PDB::FETCH_OBJ)) {
                $group_list[] = $group_data;
            }
            return $group_list;
        }
    }
    
    
    /**
     * remember the last visited groups, so 
     *
     * @param int $now_group_id id of the group you are visiting now
     */
    public function setGroupVisit($group_id)
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            $group_visits = array();
        }
        $group_visits[$group_id] = microtime(true);
        
        // sort by value, while preserving the keys
        asort($group_visits);
        $_SESSION['my_group_visits'] = serialize(array_slice($group_visits, 0, 5));
        // unset($_SESSION['my_group_visits']);
    }
    
    public function getLastVisited()
    {
        if (
            (!isset($_SESSION['my_group_visits'])) ||
            (!$group_visits = unserialize($_SESSION['my_group_visits'])) ||
            (!is_array($group_visits))
        ) {
            return array();
        } else {
            $groups = array();
            foreach($group_visits as $id => $time) {
                $groups[] = $this->findGroup($id);
            }
            return $groups;
        } 
    }
}

/**
 * represents a single group
 *
 */
class Group extends PAppModel
{
    private $_group_id;
    private $_group_data = false;
    private $_group_memberships = 0;
    
    public function __construct($group_id)
    {
        parent::__construct();
        $this->_group_id = $group_id;
    }
    
    
    public function getData()
    {
        if ($this->_group_data) {
            // do nothing
        } else if (!$result = $this->dao->query(
            "
SELECT *
FROM groups
WHERE id = $this->_group_id
            "
        )) {
            // db query went wrong
        } else if (!$group_data = $result->fetch(PDB::FETCH_OBJ)) {
            // group not found
        } else {
            $this->_group_data = $group_data;
        }
        return $this->_group_data;
    }
    
    
    public function getMembers()
    {
        return $this->getMemberships(30);  //why 30?
    }
    
    public function getMemberships($max_count)
    {
        $members = array();
        
        // TODO: check the $max_count argument
        if ($this->_group_memberships != 0) {
            // nothing to be done
        } else if (!$result = $this->dao->query(
            "
SELECT members.Username, membersgroups.*
FROM membersgroups, members
WHERE membersgroups.IdGroup = $this->_group_id
AND members.id = membersgroups.IdMember
            "
        )) {
            // something went wrong with db query
        } else {
            $memberships = array();
            while ($member = $result->fetch(PDB::FETCH_OBJ)) {
                // TODO: check for $max_count
                $memberships[] = $member;
            }
            $this->_group_memberships = $memberships;
        }
        return $this->_group_memberships;
    }
    
    public function isMember($member_id) {
        $group_id = $this->getData()->id;
        if (!$result = $this->dao->query(
            "
SELECT *
FROM membersgroups
WHERE IdGroup = $group_id
AND IdMember = $member_id
            "
        )) {
            return false;
        } else if (!$member = $result->fetch(PDB::FETCH_OBJ)) {
            return false;
        } else {
            return true;
        }
        
    }

    public function memberJoin($member_id) {
        if (!$this->isMember($member_id)) { 
            
            //echo '<pre>';
            //var_dump($this);
            // how to get the type?
            if ($this->getData()->Type == "NeedAcceptance") {  
                $status = "WantToBeIn"; // case this is a group with an admin
                // Notfiy the group accepter
                $this->NotifyGroupAccepter($this, $member_id, isset($_GET['Comment']) ? $_GET['Comment'] : '');
            }
            else {
                $status = "In";
            }

            $group_id = $this->getData()->id;
            $this->dao->query('
INSERT INTO membersgroups 
(IdMember, IdGroup, Created, Status)
VALUES 
(' . $member_id . ', ' . $group_id . ', NOW(), "' . $status . '")
');  // TO ADD: Comment
        }
    }

    public function memberLeave($member_id) {
        $group_id = $this->getData()->id;
        $this->dao->query('
DELETE FROM membersgroups 
WHERE  IdMember = ' . $member_id . '
  AND  IdGroup  = ' . $group_id . '
');
    }
    
   /**
     * Look if the information in $input is ok to send.
     * If yes, send and return a confirmation.
     * Otherwise, return an array that tells what is missing.
     * 
     * required information in $input:
     * sender_id, receiver_id, text
     * 
     * optional fields in $input:
     * reply_to_id, draft_id
     *
     * @param unknown_type $input
     */
    public function createGroupSendOrComplain($input)
    {
        // check fields
        
        $problems = array();
        
        if (empty($input['Group_'])) {
            // name is not set:
            $problems['Group_'] = 'You must choose a name for this group';
        }
        
        if (empty($input['GroupDesc_'])) {
            // Description is not set.
            $problems['GroupDesc_'] = 'You must give a description for this group.';
        }
        
        if (!isset($input['Type'])) {
            $problems['Type'] = 'Something went wrong. Please select the degree of openness for your group';
        } elseif ($input['Type'] == 'Closed') {
            $input['HasMembers'] = 'HasNotMember';
            $input['Type'] = 'Public';
        } else {
            $input['HasMembers'] = 'HasMember';
            if ($input['Type'] == 'Approved') {
                $input['Type'] = 'NeedAcceptance';
            } elseif ($input['Type'] == 'Invited') {
                $input['Type'] = 'NeedAcceptance';
            } elseif ($input['Type'] == 'Public') {
                $input['Type'] = 'Public';
            } else {
                $problems['Type'] = 'Something went wrong. Please select the degree of openness for your group';
            }
        }
        
        $input['status'] = 'ToSend';
        
        if (!empty($problems)) {
            $group_id = false;
        } else if (!isset($input['group_id'])) {
            // this was a group creation
            $group_id = $this->_createGroup($input);
        } else if (!$this->getData($this->_group_data = $input['group_id'])) {
            // draft id says this is a draft, but it doesn't exist in database.
            // this means, something stinks.
            // Anyway, we insert a new message.
            $group_id = $this->_createGroup($input);
        } else {
            // this was a draft, so we only have to change the status in DB
            $this->_updateGroup($group_id, $input);
            $group_id = $draft_id;
        }
        
        return array(
            'problems' => $problems,
            'group_id' => $group_id
        );
    }
    
    private function _createGroup($input) {
        return $this->dao->query(
            "
INSERT INTO groups
SET
    created = NOW(),
    Name = '".mysql_real_escape_string($fields['Name'])."',
    HasMembers = ".mysql_real_escape_string($fields['receiver_id']).",
    HasMembers = ".$fields['HasMembers'].",
    Type = ".$fields['HasMembers'].",
    InFolder = 'Normal',
    Status = '".$fields['status']."',
    JoinMemberPict = '".(isset($fields['attach_picture']) ? ($fields['attach_picture'] ? 'yes' : 'no') : 'no')."'
            "
        )->insertId();
    }

    /*  THIS IS POSSIBLY DEFINITELY NOT WORKING YET 
    // This function notify immediately by mail the accepter in charge of a group $TGroup
    // than there is one more pending member to accept 
    */
    function NotifyGroupAccepter($TGroup,$IdMember,$Comment) {
        function wwinlang($val, $lang) {
            return $val;  //needs to do something better
        }
        $rMember = $this->dao->query("Select members.*,cities.Name as CityName,countries.Name as CountryName from members,cities,countries where cities.id=members.IdCity and countries.id=cities.IdCountry and members.id=".$IdMember);
        $text="" ;
        var_dump($rMember);
        $subj="New Member ".$rMember->Username." to accept in group ".wwinlang("Group_".$TGroup->Name,0) ;
        
        $query = "SELECT `rightsvolunteers`.`IdMember`,`members`.`Username` from `members`,`rightsvolunteers` WHERE `rightsvolunteers`.`IdRight`=8 and (`rightsvolunteers`.`Scope` like  '%\"All\"%' or `rightsvolunteers`.`Scope` like '%\"".$TGroup->Name."\"%') and Level>0 and `rightsvolunteers`.`IdMember`=`members`.`id` and (`members`.`Status`='Active' or `members`.`Status`='ActiveHidden')" ;
        $qry = sql_query($query);
        while ($rr = mysql_fetch_object($qry)) {
            $text=" hello, ".$rr->Username." member ".LinkWithUsername($rMember->Username)." from (".$rMember->CountryName."/".$rMember->CityName.") wants to join group <b>".wwinlang("Group_".$TGroup->Name,0)."</b></br>" ;
            $text=$text." he wrote :<p>".stripslashes($Comment)."</p><br /> to accept this membership click on <a href=\"http://www.bewelcome.org/bw/admin/admingroups.php\">AdminGroup</a> (do not forget to log before !)" ;
            bw_mail(GetEmail($rr->IdMember), $subj, $text, "", "noreply@bewelcome.org", 0, "html", "", "");
        }
    } // end of NotifyGroupAccepter
    

}


?>