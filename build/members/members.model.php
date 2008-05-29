<?php


class MembersModel extends RoxModelBase
{
	
	private $profile_language = 0; //0 = 'en'
	
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
    
    
    /**
     * Not totally sure it belongs here - but better this
     * than member object? As it's more of a business of this
     * model to know about different states of the member 
     * object to be displayed..
     */
    public function set_profile_language($language) {
    	echo "language0: ".$language;
    	//TODO: check that 
    	//1) this is a language recognized by the bw system
    	//2) there's content for this member in this language
    	//else: use english = the default already set
    	
    	$language = $this->singleLookup("
SELECT id			
FROM languages
WHERE shortcode = '$language'
    			");

    	if ($language != null) {
    		
	    	$this->profile_language = $language->id;
    	}
    }
    
    
    public function get_profile_language() {
    	return $this->profile_language;
    }
        
}




class Member extends RoxEntityBase
{
	private $trads = null;
	private $address = null;
	
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
	  		$address = $this->__get("chat_".$m['network']);
	  		if(isset($address) && $address != 0) {
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


    
    
    public function count_comments() 
    {
    	//TODO: bulklookup a bit ugly for this... oh well ;) 
    	$positive = $this->bulkLookup(
            "
SELECT COUNT(*) as positive from comments 
WHERE IdToMember = ".$this->id."
AND Quality = 'Good'
		 	"
         );

    	$all = $this->bulkLookup(
            "
SELECT COUNT(*) as sum from comments 
WHERE IdToMember = ".$this->id
         );
         
         $r = array('positive' => $positive[0]->positive, 'all' => $all[0]->sum);
         return $r;
    }
    
    
    /**
     * automatically called by __get('group_memberships'),
     * when someone writes '$member->group_memberships'
     *
     * @return unknown
     */
    public function get_group_memberships()
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
            //$membership_trads = new stdClass();
        }
        return $groups_for_member;
        //return $trads;
    }
    
    
   
    /**
     * Member address lookup
     */
    protected function get_address() {
    	$sql = "SELECT SQL_CACHE a.*, ci.Name as CityName, r.Name as RegionName, co.Name as CountryName, co.isoalpha2 as CountryCode
FROM addresses as a, cities as ci, regions r, countries co
WHERE a.IdMember = ".$this->id."
AND a.IdCity = ci.id
AND ci.IdRegion = r.id
AND r.IdCountry = co.id";

         $a = $this->bulkLookup($sql);
        if($a != null && sizeof($a) > 0) {
        	$this->address = $a[0];
        }    		
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
     * 	and empty string if field has no content
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
    
	
		        
    /**
     * This needs to go someplace else, 
     * pending architectural attention
     */
	protected function get_crypted($crypted_id, $return_value) {
		
		//echo "id val: ".$crypted_id." ".$return_value;
		$rr = $this->bulkLookup
(
            "
SELECT * 
FROM cryptedfields
WHERE id = \"$crypted_id\"
            "
        );
		
		//echo "<br />RR :".$rr[0]->MemberCryptedValue;
		//print_r($rr[0]);
		if ($rr != NULL && sizeof($rr) > 0)
		{
			$rr = $rr[0];
			
			if ($rr->IsCrypted == "not crypted") {
				//echo "here1 ".$rr->MemberCryptedValue;
				return $rr->MemberCryptedValue;
			}
			if ($rr->MemberCryptedValue == "" || $rr->MemberCryptedValue == 0) {
				//echo "here2";
				return (""); // if empty no need to send crypted
				//return ($return_value);
			}
			if ($rr->IsCrypted == "crypted") {
				//echo "here3";
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

}


class GroupMembership extends RoxEntityBase
{
    public function construct($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    
}


?>