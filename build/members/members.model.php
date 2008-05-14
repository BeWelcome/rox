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
    public function name() {
    	$name1 = $this->get_crypted($this->FirstName, "*");
    	$name2 = $this->get_crypted($this->SecondName, "*");
    	$name3 = $this->get_crypted($this->LastName, "*");
    	$name = $name1." " . $name2 . " " . $name3;
    	return $name;
    }
    
    
    public function messengers() {
	  	$messengers = array(
			array("network" => "GOOGLE", "nicename" => "Google Talk", "image" => "icon_gtalk.png"), 
			array("network" => "ICQ", "nicename" => "ICQ", "image" => ""), 
			array("network" => "AOL", "nicename" => "AOL", "image" => ""), 
			array("network" => "MSN", "nicename" => "MSN", "image" => ""), 
			array("network" => "YAHOO", "nicename" => "Yahoo", "image" => ""), 
			array("network" => "SKYPE", "nicename" => "Skype", "image" => "")
		);
	  	$r = array();
	  	foreach($messengers as $m) {
	  		$m_decrypted = $this->get_crypted("chat_".$m["network"], "hidden");
	  		$r[] = array("network" => $m["nicename"], "image" => $m["image"], "address" => $m_decrypted);
	  	}
	  	return $r;
    }
    
    public function age() {
    	$age = $this->get_crypted("age", "hidden");
    	return $age;
    }

    
    public function street() {
    	if(!isset($this->address)) {
    		$this->get_address();
    	}
	    return $this->get_crypted($this->address->StreetName, '* member doesn\'t want to display');
    }
    

    public function zip() {
    	if(!isset($this->address)) {
    		$this->get_address();
    	}
	    return $this->get_crypted($this->address->Zip, '* Zip is hidden in '.$this->address->CityName);    	
    }


    public function city() {
    	if(!isset($this->address)) {
    		$this->get_address();
    	}    	
    	return $this->address->CityName;
    }
    
    
    public function region() {
    	//echo "address: " . $this->address;
    	if(!isset($this->address)) {
    		$this->get_address();
    	}    	
    	//echo "address: " . $this->address;
    	return $this->address->RegionName;
    }


    public function country() {
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
    

    public function countrycode() {
    	//echo "address: " + $this->address;
    	if(!isset($this->address)) {
    		$this->get_address();
    	}    	
    	return $this->address->CountryCode;
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
    
    
   
    /**
     * Member address lookup
     */
    protected function get_address() {
    	
    	//echo "get_address";
    	
    	$sql = "SELECT SQL_CACHE a.*, ci.Name as CityName, r.Name as RegionName, co.Name as CountryName, co.isoalpha2 as CountryCode
FROM addresses as a, cities as ci, regions r, countries co
WHERE a.IdMember = ".$this->id."
AND a.IdCity = ci.id
AND ci.IdRegion = r.id
AND r.IdCountry = co.id";

		//$sql = "whateva";
		//echo "sql: " . $sql;
		//return;
         $a = $this->bulkLookup($sql);
         //$a = null;
        
        //echo "<pre>a: " . $a;
        //print_r($a);
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
		
		$rr = $this->bulkLookup
(
            "
SELECT * 
FROM cryptedfields
WHERE id = \"$crypted_id\"
            "
        );
		
		if ($rr != NULL && sizeof($rr) > 0)
		{
			$rr = $rr[0];
			if ($rr->IsCrypted == "not crypted") {
				return $rr->MemberCryptedValue;
			}
			if ($rr->MemberCryptedValue == "") {
				return (""); // if empty no need to send crypted
				//return ($return_value);
			}
			if ($rr->IsCrypted == "crypted") {
				return ($return_value);
			}			
		}	
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