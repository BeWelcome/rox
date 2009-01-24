<?php


class Member extends RoxEntityBase
{
    private $trads = null;
    private $trads_by_tradid = null;
    private $address = null;
    private $profile_languages = null;

    public function __construct($ini_data, $member_id = false)
    {
        parent::__construct($ini_data);
        if ($member_id)
        {
            $this->findById($member_id);
        }
    }
    
    public function init($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    
    /**
     * Checks which languages profile has been translated into
     */
    public function get_profile_languages() {
        if(!isset($this->trads)) {
            $this->trads = $this->get_trads();
        }
        return $this->profile_languages;
    }
    

    /**
     * Get languages spoken by member
     */
    public function get_languages_spoken() {

        $TLanguages = array();
        $str = "SELECT SQL_CACHE memberslanguageslevel.IdLanguage AS IdLanguage,languages.Name AS Name, " .
          "memberslanguageslevel.Level AS Level FROM memberslanguageslevel,languages " .
          "WHERE memberslanguageslevel.IdMember=" . $this->id . 
          " AND memberslanguageslevel.IdLanguage=languages.id AND memberslanguageslevel.Level != 'DontKnow' order by memberslanguageslevel.Level asc";
        
        $qry = mysql_query($str);
        while ($rr = mysql_fetch_object($qry)) {
            //$rr->Level = ("LanguageLevel_".$rr->Level);   
            array_push($TLanguages, $rr);
        }
        return $TLanguages;
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
SELECT SQL_CACHE
    *
FROM
    memberstrads
WHERE
    IdOwner = $this->id 
            "
        );
        
        $language_data = $this->bulkLookup(
            "
SELECT SQL_CACHE
    id,
    ShortCode
FROM
    languages 
            ", 
            "id"
        );        
        $trads_by_tradid = array();
        $this->profile_languages = array();
        foreach ($trads_for_member as $trad) {
            if (!isset($trads_by_tradid[$trad->IdTrad])) {
                $trads_by_tradid[$trad->IdTrad] = array();
            }
            $trads_by_tradid[$trad->IdTrad][$trad->IdLanguage] = $trad;
            //keeping track of which translations of the profile texts have been encountered
            $language_id = $trad->IdLanguage;
            $this->profile_languages[$language_id] = $language_data[$language_id]->ShortCode;
        }
        $this->trads_by_tradid= $trads_by_tradid;
        
        $field_names = array(
            'ILiveWith',
            'MaxLenghtOfStay',
            'MotivationForHospitality',
            'Offer',
            'Organizations',
            'AdditionalAccomodationInfo',
            'OtherRestrictions',
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
            'PlannedTrips',
            'ProfileSummary'
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
     * TODO: get name from crypted fields in an architecturally sane place (to be determined)
     */    
    public function get_name() {
        $name1 = $this->get_crypted($this->FirstName, "*");
        $name2 = $this->get_crypted($this->SecondName, "*");
        $name3 = $this->get_crypted($this->LastName, "*");
        $name = $name1." " . $name2 . " " . $name3;
        return $name;
    }
    
    
    public function get_messengers() {
          $messengers = array(
            array("network" => "GOOGLE", "nicename" => "Google Talk", "image" => "icon_gtalk.png"), 
            array("network" => "ICQ", "nicename" => "ICQ", "image" => "icon_icq.jpg"), 
            array("network" => "AOL", "nicename" => "AOL", "image" => "icon_aim.png"), 
            array("network" => "MSN", "nicename" => "MSN", "image" => "icon_msn.png"), 
            array("network" => "YAHOO", "nicename" => "Yahoo", "image" => "icon_yahoo.png"), 
            array("network" => "SKYPE", "nicename" => "Skype", "image" => "icon_skype.png")
        );
          $r = array();
          foreach($messengers as $m) {
              $address_id = $this->__get("chat_".$m['network']);
              $address = $this->get_crypted($address_id, "*");
              if(isset($address) && $address != "*") {
                  $r[] = array("network" => $m["nicename"], "image" => $m["image"], "address" => $address);
              }
          }
          if(sizeof($r) == 0)
              return null;
          return $r;
    }
    
    
    public function get_age() {
        $age = $this->get_crypted("age", "hidden");
        return $age;
    }

    
    public function get_street() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->get_crypted($this->address->StreetName, '* member doesn\'t want to display');
    }
    

    public function get_zip() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->get_crypted($this->address->Zip, '* Zip is hidden in '.$this->address->CityName);        
    }


    public function get_city() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->address->CityName;
    }
    
    
    public function get_region() {
        //echo "address: " . $this->address;
        if(!isset($this->address)) {
            $this->get_address();
        }        
        //echo "address: " . $this->address;
        return $this->address->RegionName;
    }


    public function get_country() {
        //echo "address: " + $this->address;
        //return "" 
        
        if(!isset($this->address)) {
            //echo "No address set, getting it!";
            $this->get_address();
        }        
        $r = $this->address->CountryName;
        //echo "r: " + $r;
        return $r;
    }
    

    public function get_countrycode() {
        //echo "address: " + $this->address;
        if(!isset($this->address)) {
            $this->get_address();
        }        
        return $this->address->CountryCode;
    }


    
    public function get_photo() {
        $photos = $this->bulkLookup(
            "
SELECT * FROM membersphotos        
WHERE IdMember = ".$this->id    
        );
        
        return $photos;
    }
    
    
    
    public function get_previous_photo($photorank) {
        $photorank--;
        
        if($photorank < 0) {
            $photos = $this->bulkLookup(
                "
SELECT * FROM membersphotos        
WHERE IdMember = $this->id
ORDER BY SortOrder DESC LIMIT 1"    
            );
        }
        
    }
/*
$photorank=GetParam("photorank",0);
switch (GetParam("action")) {
    case "previouspicture" :
        $photorank--;
        if ($photorank < 0) {
              $rr=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " order by SortOrder desc limit 1");
            if (isset($rr->SortOrder)) $photorank = $rr->SortOrder;
            else $photorank=0;
        }
        break;
    case "nextpicture" :
        $photorank++;
        break;
    case "logout" :
        Logout();
        exit (0);
}
 */    
    public function count_comments() 
    {
        $positive = $this->bulkLookup(
            "
SELECT COUNT(*) AS positive
FROM comments 
WHERE IdToMember = ".$this->id."
AND Quality = 'Good'
             "
         );

        $all = $this->bulkLookup(
            "
SELECT COUNT(*) AS sum
FROM comments 
WHERE IdToMember = ".$this->id
         );
         
         $r = array('positive' => $positive[0]->positive, 'all' => $all[0]->sum);
         return $r;
    }
    

    /**
     * return an array of group entities that the member is in
     *
     * @access public
     * @return array
     */
    public function getGroups()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        
        return $this->_entity_factory->create('GroupMembership')->getMemberGroups($this);
    }


    /**
     * automatically called by __get('group_memberships'),
     * when someone writes '$member->group_memberships'
     *
     * @return unknown
     */
    public function get_group_memberships()
    {
                $TGroups=array() ;
        $query = "select SQL_CACHE membersgroups.id as IdMemberShip, membersgroups.Comment as Comment,groups.Name as Name,groups.id as IdGroup from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=" .$this->id;
        $s = $this->dao->query($query);

        if( !$s) {
            throw new PException('Could not retrieve Groups!');
        }
        $TGroups = array();
        while( $rr = $s->fetch(PDB::FETCH_OBJ)) {
            //$TGroups[$row->id] = $row->name;
            $rr->Location="" ;
            $str="select IdLocation,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName from groups_locations ";
            $str.=" left join  countries on countries.id=IdLocation" ;
            $str.=" left join  regions on regions.id=IdLocation" ;
            $str.=" left join  cities on cities.id=IdLocation" ;
            $str=   $str."  where IdGroupMemberShip=".$rr->IdMemberShip ;
            $qry_rLocation=$this->dao->query($str) ;
            while( $rrLocation = $qry_rLocation->fetch(PDB::FETCH_OBJ)) {
                if ($rr->Location=="") {
                    $rr->Location="(" ;
                }
                else {
                    $rr->Location.="," ;
                }
                if (isset($rrLocation->CountryName)) {
                    $rr->Location=$rr->Location.$rrLocation->CountryName ;
                }
                else if (isset($rrLocation->RegionName)) {
                    $rr->Location=$rr->Location.$rrLocation->RegionName ;
                }
                else if (isset($rrLocation->CityName)) {
                    $rr->Location=$rr->Location.$rrLocation->CityName ;
                }
            }
            if ($rr->Location!="") {
                $rr->Location.=")" ;
            }
            
      array_push($TGroups, $rr);
        }
        return $TGroups;

} // end of get_group_memberships
    
    
   
    /**
     * Member address lookup
     */
    protected function get_address() {
        $sql =
           "
SELECT
    SQL_CACHE a.*,
    ci.Name      AS CityName,
    co.Name      AS CountryName,
    co.isoalpha2 AS CountryCode
FROM
    addresses    AS a,
    cities       AS ci,
    countries    AS co
WHERE
    a.IdMember  = $this->id  AND
    a.IdCity    = ci.id      AND
    ci.IdCountry = co.id
            "
        ;
        $a = $this->bulkLookup($sql);
        if($a != null && sizeof($a) > 0) {
            $Geo = new GeoModel();
            $IdRegion = $Geo->getDataById($a[0]->IdCity)->parentAdm1Id;
            $a[0]->RegionName = $Geo->getDataById($IdRegion)->name;
            $this->address = $a[0];
        }            
    }
    
        
      public function get_relations() {
          $sql = " 
SELECT
    members.Username
FROM
    specialrelations,
    members          
WHERE
    specialrelations.IdOwner = $this->id  AND
    specialrelations.IdRelation = members.Id                  
          ";
          return $this->bulkLookup($sql);
      }
      
      public function get_preferences() {
          $sql = " 
SELECT
    preferences.*,
    Value
FROM
    preferences
LEFT JOIN
    memberspreferences ON
    memberspreferences.IdPreference = preferences.id AND
    memberspreferences.IdMember = $this->id
WHERE
    preferences.Status != 'Inactive'
          ";
          return $this->bulkLookup($sql);
      }

  
      public function get_visitors() {
          $sql = " 
SELECT
    members.BirthDate,
    members.HideBirthDate,
    members.Accomodation,
    members.Username,
    geonames_cache.name AS city 
FROM
    profilesvisits,
    members,
    geonames_cache
WHERE
    profilesvisits.IdMember  = $this->id  AND
    profilesvisits.IdVisitor = members.Id AND
    geonames_cache.geonameid = members.IdCity
          ";
          return $this->bulkLookup($sql);
      }
      
      
      
      public function get_comments() {
          $sql = " 
SELECT *,
    comments.Quality AS comQuality
FROM
    comments,
    members          
WHERE
    comments.IdToMember   = $this->id  AND
    comments.IdFromMember = members.Id                  
          ";
          
          
          //echo $sql;
          //print_r($r);
          return $this->bulkLookup($sql);
          
      }
      
      public function get_comments_commenter($id) {
        $id = (int)$id;
          $sql = " 
SELECT *,
    comments.Quality AS comQuality
FROM
    comments,
    members          
WHERE
    comments.IdToMember   = $this->id  AND
    comments.IdFromMember = ".$id."                 
          ";
          
          
          //echo $sql;
          //print_r($r);
          return $this->bulkLookup($sql);
          
      }
      
        
    /**
     * Fetches translation of specific field in user profile. 
     * Initializes instance variable $trads if it hasn't been 
     * initialized already.
     * 
     * @param fieldname name of the profile field
     * @param language required translation 
     * 
     * @return text of $fieldname if available, English otherwise, 
     *     and empty string if field has no content
     */
    public function get_trad($fieldname, $language) {
        if(!isset($this->trads)) {
            $this->trads = $this->get_trads();
        }
        
        if(!isset($this->trads->$fieldname)) 
            return "";
        else {
            $field = $this->trads->$fieldname;
            if(!array_key_exists($language, $field)) {
                //echo "Not translated";
                if($language != 0)
                    return $field[0]->Sentence;
                else return "";
            }
            else {
                return $field[$language]->Sentence;
            }
        }
    }
    
    
    public function get_trad_by_tradid($tradid, $language) {
        if(!isset($this->trads)) {
            $this->get_trads();
        }    
        
        if(!isset($this->trads_by_tradid[$tradid])) 
            return "";
        else {
            $trad = $this->trads_by_tradid[$tradid];
            if(!array_key_exists($language, $trad)) {
                //echo "Not translated";
                if($language != 0)
                    return $trad[0]->Sentence;
                else return "";
            }
            else {
                return $trad[$language]->Sentence;
            }
        }            
    }
            
                
    /**
     * This needs to go someplace else, 
     * pending architectural attention
     */
    protected function get_crypted($crypted_id, $return_value)
    {
        $crypted_id = (int)$crypted_id;
        $rr = $this->bulkLookup(
            "
SELECT * 
FROM cryptedfields
WHERE id = $crypted_id
            "
        );
        
        if ($rr != NULL && sizeof($rr) > 0)
        {
            $rr = $rr[0];
            if ($rr->IsCrypted == "not crypted") {
                return $rr->MemberCryptedValue;
            }
            if ($rr->MemberCryptedValue == "" || $rr->MemberCryptedValue == 0) {
                return (""); // if empty no need to send crypted
            }
            if ($rr->IsCrypted == "crypted") {
                return ($return_value);
            }            
        }    
        /*elseif(sizeof($rr) > 0) {
            return ("");
        }*/
        else { 
            return ($return_value);
        }
    }
    
    
    /**
     * Should fetch male & female dummy pics when the member doesn't
     * have any photos uploaded. membersphotos.id for those images = ??
     */
    public function getProfilePictureID() {
        $q = "
SELECT id FROM membersphotos WHERE IdMember = ".$this->id. " ORDER BY SortOrder ASC LIMIT 1 
                ";
        $id = $this->singleLookup_assoc($q);
        if($id) {
            return $id['id'];
        }
        return null;                
    }
    
    /**
     * attempts to load a member entity using username
     *
     * @param string $username - Username to search for
     * @access public
     * @return object
     */
    public function findByUsername ($username)
    {
        $username = $this->dao->escape($username);
        
        $where = "Username = '{$username}'";
        return $this->findByWhere($where);
    }
    
    /**
     * finds a GroupMembership object for the member for a given group
     *
     * @param object $group - the Group to look for membership for
     * @access public
     * @return object
     */
    public function getGroupMembership($group)
    {
        if (!is_object($group))
        {
            return false;
        }

        return $this->_entity_factory->create('GroupMembership')->getMembership($group, $this);
    }

    /**
     * returns an array of roles for the member
     *
     * @access public
     * @return array an array of role entities
     */
    public function getRoles()
    {
        if (!$this->isPKSet())
        {
            return false;
        }

        return $this->_entity_factory->create('MemberRole')->getMemberRoles($this);
    }

    /**
     * checks if member has a specific role assigned to it
     *
     * @param object $role - the role to check if the member has
     * @access public
     * @return bool
     */
    public function hasRole($role)
    {
        if (!$role->isPKSet() || !$this->isPKSet())
        {
            return false;
        }

        return $this->_entity_factory->create('MemberRole')->memberHasRole($this, $role);
    }

    /**
     * tests if a member has a given privilege, optionally for a given object
     * if the object is not given, the test is for global privilege
     *
     * @param string $controller - the controller to test access for
     * @param string $method - the method to test access for. Default is global
     * @param mixed $object - an entity or '*' which signifies global
     * @access public
     * @return bool
     */
    public function hasPrivilege($controller, $method = '*', $object = '*')
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $controller = $this->dao->escape($controller);
        $method = $this->dao->escape($method);

        // search for an applicable privilege
        if (!($privilege = $this->_entity_factory->create('Privilege')->findNamedPrivilege($controller, $method)) && !($privilege = $this->_entity_factory->create('Privilege')->findNamedPrivilege($controller)) && !($privilege = $this->_entity_factory->create('Privilege')->findNamedPrivilege('*', '*')))
        {
            return false;
        }


        if (!($roles = $this->getRoles()))
        {
            return false;
        }

        // TODO: check for complex primary keys
        $object_id = ((is_object($object)) ? $object->getPKValue() : '*');
        $return = false;
        $priv_scope = $this->_entity_factory->create('PrivilegeScope');
        foreach ($roles as $role)
        {
            if ($priv = $role->getEquivalentPrivilege($privilege))
            {
                if ($priv_scope->checkForEquivalentScope($this, $role, $priv, $object_id))
                {
                    $return = true;
                    break;
                }
            }
        }
        return $return;
    }
}

?>
