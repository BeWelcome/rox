<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @author Lemon-Head
     * @author Fake51
     * @author jeanyves
     */

    /**
     * ORM for members table
     *
     * @package Apps
     * @subpackage Entities
     */
class Member extends RoxEntityBase
{
    protected $_table_name = 'members';

    private $trads = null;
    private $trads_by_tradid = null;
    public  $address = null;
    private $profile_languages = null;
    private $edit_mode = false;
    private $trad_by_tradid_inlang ; // Used to cache mTrad values

    public function __construct($member_id = false)
    {
        parent::__construct();
        if ($member_id)
        {
            $this->findById($member_id);
        }
        $this->words=new MOD_words ;
    }

    public function init($values, $dao)
    {
        parent::init($values, $dao);
    }


    /**
     * Get the member's TB user id
     */
    public function get_userid() {
        if(!isset($this->userId)) {
            $s = $this->singleLookup(
                "
    SELECT SQL_CACHE
        user.id
    FROM
        user
    WHERE
        handle = '$this->Username'
                "
            );
            if ($s) $this->userId = $s->id;
            else return false;
        }
        return $this->userId;
    }

    /**
     * Set user's Zip field in addresses table
     * @param integer $cryptId ID of field in crypted table
     * @return boolean Result of database query execution
     */
    public function setCryptedZip($cryptId) {
        $query = 'UPDATE addresses SET Zip = ' . intval($cryptId)
            . ' WHERE IdMember = ' . $this->id . ' LIMIT 1';
        return $this->dao->exec($query);
    }

    /**
     * Set user's HouseNumber field in addresses table
     * @param integer $cryptId ID of field in crypted table
     * @return boolean Result of database query execution
     */
    public function setCryptedHouseNumber($cryptId) {
        $query = 'UPDATE addresses SET HouseNumber = ' . intval($cryptId)
            . ' WHERE IdMember = ' . $this->id . ' LIMIT 1';
        return $this->dao->exec($query);
    }

    /**
     * Set user's StreetName field in addresses table
     * @param integer $cryptId ID of field in crypted table
     * @return boolean Result of database query execution
     */
    public function setCryptedStreetName($cryptId) {
        $query = 'UPDATE addresses SET StreetName = ' . intval($cryptId)
            . ' WHERE IdMember = ' . $this->id . ' LIMIT 1';
        return $this->dao->exec($query);
    } 
    
    
    /**
     * Checks which languages profile has been translated into
     */
    public function get_profile_languages() {
        if(!isset($this->profile_languages)) {
            $this->set_profile_languages();
        }
        return $this->profile_languages;
    }


    /**
     * Get languages spoken by member
     */
    public function get_languages_spoken() {

        $TLanguages = array();
        $str = "SELECT SQL_CACHE memberslanguageslevel.IdLanguage AS IdLanguage,languages.Name,languages.ShortCode AS ShortCode, " .
          "memberslanguageslevel.Level AS Level,WordCode FROM memberslanguageslevel,languages " .
          "WHERE memberslanguageslevel.IdMember=" . $this->id .
          " AND memberslanguageslevel.IdLanguage=languages.id AND memberslanguageslevel.Level != 'DontKnow' order by memberslanguageslevel.Level asc";
        $qry = mysql_query($str);
        while ($rr = mysql_fetch_object($qry)) {
            //if (isset($rr->Level)) $rr->Level = ("LanguageLevel_".$rr->Level);
            array_push($TLanguages, $rr);
        }
        return $TLanguages;
    }

    /**
     * Get all available languages
     */
    public function get_languages_all() {

        $AllLanguages = array();
        $str =
            "
SELECT SQL_CACHE
    languages.Name AS Name,
    languages.ShortCode AS ShortCode,
    languages.id AS id
FROM
    languages
ORDER BY languages.id asc
            ";
        $s = $this->dao->query($str);
        while ($rr = $s->fetch(PDB::FETCH_OBJ)) {
            //if (isset($rr->Level)) $rr->Level = ("LanguageLevel_".$rr->Level);
            array_push($AllLanguages, $rr);
        }
        return $AllLanguages;
    }

    /**
     * Get language code from preferences.
     * @return string language ShortCode (2 to 4 letters), 'en' if no preference was found.
     */
    public function getLanguagePreference() {
        $id = $this->getLanguagePreferenceId();
        $allLanguages = $this->get_languages_all();

        // set default
        // TODO: read from config
        $shortCode = 'en';

        foreach ($allLanguages as $language) {
            if ($language->id == $id) {
                $shortCode = $language->ShortCode;
                break;
            }
        }
        return $shortCode;
    }

    /**
     * Get language ID from preferences.
     * @return integer language ID, 0 if no preference was found.
     */
    public function getLanguagePreferenceId() {
        return intval($this->getPreference('PreferenceLanguage'));
    }

    /**
     * Get all possible levels of languages
     */
    public function get_language_levels() {

        $table = "memberslanguageslevel";
        $column = "Level";
        $tt = $this->sql_get_enum($table,$column);
        return $tt;
    }


    /**
     * Get all Restrictions for Accomodation
     */
    public function get_TabRestrictions() {

        $tt = $this->sql_get_set("members", "Restrictions");
        return $tt;
    }


    /**
     * Get all Restrictions for Accomodation
     */
    public function get_TabRelationsType() {

        $tt = $this->sql_get_set("specialrelations", "Type");
        return $tt;
    }

    /**
     * Get all Typical Offers possible
     */
    public function get_TabTypicOffer() {

        $tt = $this->sql_get_set("members", "TypicOffer");
        return $tt;
    }

    /**
     * Check if a property of a member is filled or not (ie: if the owner has filled something)
     * for exemple if ->IsFilled("Hobbies")  will return true if teh member has some Hobbies declared
     */
    public function IsFilled($fieldname) {
        return($this->$fieldname!=0) ;
    }

    /**
     * Use to retrieve all the fields in members table which are a a foreign key to memberstrads
     * This is typically neded when you want to delete a a given translation
     * @return unknown
     */
    public function get_trads_fields()
    {
        return array(
            'Occupation',
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
    }

    /**
     * automatically called by __get('trads'),
     * when someone writes '$member->trads'
     *
     * @return nothing but $this->profile_languages is set
     * cave at : base on the existence of a ProfileSummary  for the current member
     */
    protected function set_profile_languages()
    {
        $trads_for_member = $this->bulkLookup("SELECT SQL_CACHE languages.id,ShortCode,Name from memberstrads,languages 
        where languages.id=memberstrads.IdLanguage and IdOwner = $this->id and IdTrad=$this->ProfileSummary") ;
        $this->profile_languages = array();

        foreach ($trads_for_member as $trad) {
            $this->profile_languages[$trad->id] = $trad;
        }
    }


     /**
     * automatically called by __get('trads'),
     * when someone writes '$member->trads'
     *
     * @return unknown
     */
    protected function get_trads()
    {
        // This code is obsolete (jy) 
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
    ShortCode,
    Name
FROM
    languages
            ",
            "id"
        );
        $trads_by_tradid = array();
        $this->profile_languages = array();
        $field_names = $this->get_trads_fields();
        $field_ids = array();
        foreach ($field_names as $field) {
            $field_ids[] = $this->$field;
        }
        foreach ($trads_for_member as $trad) {
            if (!isset($trads_by_tradid[$trad->IdTrad])) {
                $trads_by_tradid[$trad->IdTrad] = array();
            }
            $trads_by_tradid[$trad->IdTrad][$trad->IdLanguage] = $trad;
            //keeping track of which translations of the profile texts have been encountered
            $language_id = $trad->IdLanguage;

            if (in_array($trad->IdTrad,$field_ids))
                $this->profile_languages[$language_id] = $language_data[$language_id];
        }
        $this->trads_by_tradid= $trads_by_tradid;

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


    public function get_phone() {
        $phone = array();
        if ($this->get_crypted($this->HomePhoneNumber, ""))
            $phone['HomePhoneNumber'] = $this->get_crypted($this->HomePhoneNumber, "");
        if ($this->get_crypted($this->CellPhoneNumber, ""))
            $phone['CellPhoneNumber'] = $this->get_crypted($this->CellPhoneNumber, "");
        if ($this->get_crypted($this->WorkPhoneNumber, ""))
            $phone['WorkPhoneNumber'] = $this->get_crypted($this->WorkPhoneNumber, "");
        return $phone;
    }

    public function get_homephonenumber() {
        return $this->get_crypted($this->HomePhoneNumber, "");
    }

    /**
     * Get member's public profile
     *
     * @return mixed Public profile entity or false if not public
     */
    public function get_publicProfile()
    {
        $s = $this->singleLookup(
            "
SELECT *
FROM memberspublicprofiles
WHERE IdMember = ".$this->id
         );
        return $s;
    }

    /**
     * Find out if member's profile is public
     *
     * @return bool True if public, false if not
     */
    public function isPublic()
    {
        if ($this->publicProfile === false) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * TODO: get name from crypted fields in an architecturally sane place (to be determined)
     */
    public function get_name() {
        $name = "{$this->get_firstname()} {$this->get_secondname()} {$this->get_lastname()}";
        return $name;
    }

    public function get_firstname() {
        return $this->get_crypted($this->FirstName, "");
    }

    public function get_secondname() {
        return $this->get_crypted($this->SecondName, "");
    }

    public function get_lastname() {
        return $this->get_crypted($this->LastName, "");
    }

    /**
     * Get member's email address (uses various permission checks)
     * @return string Email address of member, empty if read permission denied
     */
    public function get_email() {
        return $this->get_crypted($this->Email, "");
    }

    /**
     * Get member's email address (no permission checks)
     * @return string|bool Email address of member, false on database error
     */
    public function getEmailWithoutPermissionChecks() {
        return urldecode(strip_tags(MOD_crypt::AdminReadCrypted($this->Email)));
    }

    public function get_messengers() {
          $messengers = array(
            array("network" => "GOOGLE", "nicename" => "Google Talk", "image" => "icon_gtalk.png", "href" => ""),
            array("network" => "ICQ", "nicename" => "ICQ", "image" => "icon_icq.png", "href" => ""),
            array("network" => "AOL", "nicename" => "AIM", "image" => "icon_aim.png", "href" => "aim:goim?"),
            array("network" => "MSN", "nicename" => "MSN", "image" => "icon_msn.png", "href" => "msnim:chat?contact="),
            array("network" => "YAHOO", "nicename" => "Yahoo", "image" => "icon_yahoo.png", "href" => "ymsgr:sendIM?"),
            array("network" => "SKYPE", "nicename" => "Skype", "image" => "icon_skype.png", "href" => "skype:echo"),
            array("network" => "Others", "nicename" => "Other", "image" => "icon_other.png", "href" => "#")
        );
          $r = array();
          foreach($messengers as $m) {
              $address_id = $this->__get("chat_".$m['network']);
              $address = $this->get_crypted($address_id, "");
              if(isset($address) && $address != "*") {
                  $r[] = array("network" => $m["nicename"], "network_raw" => $m['network'], "image" => $m["image"], "href" => $m["href"], "address" => $address, "address_id" => $address_id);
              }
          }
          if(sizeof($r) == 0)
              return null;
          return $r;
    }

    /**
     * checks if the member has set any addresses for messenger protocols
     *
     * @author Fake51
     * @access public
     * @return bool
     */
    public function hasMessengers()
    {
        if (!($m = $this->get_messengers()))
        {
            return false;
        }
        foreach ($m as $messenger)
        {
            if (!empty($messenger['address']))
            {
                return true;
            }
        }
        return false;
    }

    public function get_age() {
        
        if ($this->HideBirthDate=='Yes') {
            return('hidden' );
        }
        $layoutbits = new MOD_layoutbits;    
        return ($layoutbits->fage_value($this->BirthDate));
    }

    /*
    return the gender in plain text (to trasnlate) if it is public
    */
    public function get_gender_for_public() {
        if ($this->HideGender=='No') {
            return($this->Gender) ;
        }
        else return('IDontTell') ;
    }
    /**
     * returns 'unencrypted' housenumber
     *
     * @access public
     * @return string
     */
    public function get_housenumber()
    {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->get_crypted($this->address->HouseNumber, '');
    }

    /**
     * returns 'unencrypted' street
     *
     * @access public
     * @return string
     */
    public function get_street()
    {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->get_crypted($this->address->StreetName, '');
    }

    /**
     * returns 'unencrypted' zip
     *
     * @access public
     * @return string
     */
    public function get_zip() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->get_crypted($this->address->Zip, '');
    }

    /**
     * returns city
     *
     * @access public
     * @return string
     */
    public function get_city() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->address->CityName;
    }

    /**
     * returns region
     *
     * @access public
     * @return string
     */
    public function get_region() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->address->RegionName;
    }

    /**
     * returns country
     *
     * @access public
     * @return string
     */
    public function get_country() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->address->CountryName;
    }

    /**
     * returns countrycode
     *
     * @access public
     * @return int
     */
    public function get_countrycode() {
        if(!isset($this->address)) {
            $this->get_address();
        }
        return $this->address->CountryCode;
    }


    /**
     * returns address entity for first address
     *
     * @access public
     * @return Address
     */
    public function getFirstAddress()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Address')->getMemberAddress($this);
    }

    /**
     * returns feedback entity matched to the users signup
     * if the user left feedback then
     *
     * @access public
     * @return Feedback
     */
    public function getSignupFeedback()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Feedback')->getSignupFeedback($this);
    }

    public function get_photo() {
        // $photos = $this->bulkLookup(
            // "
// SELECT * FROM membersphotos
// WHERE IdMember = ".$this->id
        // );

        // return $photos;
    }



    public function get_previous_photo($photorank) {
        // $photorank--;

        // if($photorank < 0) {
            // $photos = $this->bulkLookup(
                // "
// SELECT * FROM membersphotos
// WHERE IdMember = $this->id
// ORDER BY SortOrder DESC LIMIT 1"
            // );
        // }

    }

    /*
    returns the number of notes the current logged in user has with  this member
    return : int or 0 if current user is not logged
    */
    public function count_mynotes()
    {
        if  (empty($_SESSION['IdMember'])) return (0) ;
        $rr=$this->singleLookup("select SQL_CACHE count(*) as cnt from mycontacts where IdMember=".$_SESSION["IdMember"]." and IdContact=".$this->id);
        return($rr->cnt) ;
    } // end of count_mynotes

    public function count_comments() {
        if (!$this->isLoaded()) {
            return array(
                'positive' => 0,
                'all' => 0
            );
        }
        $id = intval($this->id);
        $positive = $this->bulkLookup("
            SELECT
                COUNT(*) as positive
            FROM
                comments,
                members
            WHERE
                comments.IdToMember = $id
                AND
                comments.Quality = 'Good'
                AND
                members.id = comments.IdFromMember
                AND
                members.status IN ('Active', 'ChoiceInactive')
            "
        );

        // TODO: This could be done in first query
        $all = $this->bulkLookup("
            SELECT
                COUNT(*) as sum
            FROM
                comments,
                members
            WHERE
                comments.IdToMember = $id
                AND
                members.id = comments.IdFromMember
                AND
                members.status IN ('Active', 'ChoiceInactive')
            "
        );

        $commentCounters = array(
            'positive' => $positive[0]->positive,
            'all' => $all[0]->sum
        );
        return $commentCounters;
    }


    /**
     * return an array of trip entities that the member created
     *
     * @access public
     * @return array
     */
    public function getTripsArray()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        $tripmodel = new Trip();
        $usertrips = $tripmodel->getTrips($this->Username);
        $trip_data = $tripmodel->getTripData();
        return array($usertrips,$trip_data);
    }
    
    /**
     * return an array of blog entities that the member created
     *
     * @access public
     * @return array
     */
    public function getBlogs()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        $tripmodel = new Blog();
        $usertrips = $tripmodel->getTrips($this->Username);
        $trip_data = $tripmodel->getTripData();
        return array($usertrips,$trip_data);
    }

    /**
     * Get number of gallery items
     *
     * @todo Cache count to save database queries
     * @return integer Number of items
     */
    public function getGalleryItemsCount()
    {
        $gallery = new GalleryModel;
        $count = $gallery->getUserItemCount($this->get_userid());
        return $count;
    }

    /**
     * return an array of blog entities that have a start date that lies in the future
     *
     * @access public
     * @return array
     */
    public function getComingPosts()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        return $this->createEntity('BlogEntity')->getComingPosts($this->id);        
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

        return $this->createEntity('GroupMembership')->getMemberGroups($this, 'In');
    }

    /**
     * returns an array of notes the member wrote
     *
     * @access public
     * @return array
     */
    public function getNotes()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        return $this->createEntity('ProfileNote')->getNotes($this);
    }


    /**
     * automatically called by __get('group_memberships'),
     * when someone writes '$member->group_memberships'
     *
     * @return unknown
     */
    public function get_group_memberships()
    {
        throw new Exception("don't use this function, use getGroups() instead!");

    }

    /**
     * Member address lookup
     * TODO: Translate errors
     */
    protected function get_address() {
        $id = $this->id;
        $query = "
            SELECT
                SQL_CACHE a.*
            FROM
                addresses AS a
            WHERE
                a.IdMember = $id
            LIMIT
                1
            ";
        $rows = $this->bulkLookup($query);

        // Check if address was found
        if($rows != null && sizeof($rows) > 0) {
            $addressRow = $rows[0];
            $city = $this->createEntity('Geo')->findById($addressRow->IdCity);
            if ($city) {
                // Set city name
                if ($city->getName() == '') {
                    $cityName = 'Error: City name not set';
                } else {
                    $cityName = $city->getName();
                }

                // Set region name
                $region = $city->getParent();
                if ($region) {
                    $regionName = $region->getName();
                } else {
                    // Suppress display in template
                    $regionName = '';
                }

                // Set country name and code
                $country = $city->getCountry();
                if ($country) {
                    $countryName = $country->getName();
                    $countryCode = $country->fk_countrycode;
                }

                // Set remaining address fields
                $idCity = $addressRow->IdCity;
                $houseNumber = $addressRow->HouseNumber;
                $streetName = $addressRow->StreetName;
                $zip = $addressRow->Zip;
            } else {
                // Use error message everywhere if city could not be found
                $errorMessage = 'Error: City not found';
                $idCity = '';
                $cityName = $errorMessage;
                $houseNumber = $errorMessage;
                $streetName = $errorMessage;
                $zip = $errorMessage;
                $regionName = $errorMessage;
                $countryName = $errorMessage;
                $countryCode = $errorMessage;
            }
        } else {
            // Use error message everywhere if database returned no address
            $errorMessage = 'Error: Address not set';
            $idCity = '';
            $cityName = $errorMessage;
            $houseNumber = $errorMessage;
            $streetName = $errorMessage;
            $zip = $errorMessage;
            $regionName = $errorMessage;
            $countryName = $errorMessage;
            $countryCode = $errorMessage;
        }

        // Build address
        $address = new stdClass();
        $address->IdCity = $idCity;
        $address->HouseNumber = $houseNumber;
        $address->StreetName = $streetName;
        $address->Zip = $zip;
        $address->CityName = $cityName;
        $address->RegionName = $regionName;
        $address->CountryName = $countryName;
        $address->CountryCode = $countryCode;

        $this->address = $address;
    }

    /*
    * this function get the number of post of the current member
    */
    public function forums_posts_count() {
        // Todo (jyh) : to make it more advanced and consider the visibility of current surfing member
        if (!$this->ForumPostCount)
        {
            $this->ForumPostCount = $this->createEntity('Post')->getMemberPostCount($this);
        }
        return($this->ForumPostCount)  ; // Nota: in case a new post was make during the session it will not be considerated, this is a performance compromise
    } // forums_posts_count
    
    public function get_verification_status()
    {
        // Loads the verification level of the member (if any) 
        $sql = "
SELECT *
FROM verifiedmembers
WHERE
    IdVerified = $this->id
ORDER BY
    Type desc limit 1
        ";
        $rr = $this->singleLookup($sql);
        if ($rr) {
            if (empty($rr->Type)) {
                return "Normal";
            } else {
                return $rr->Type;
            }
        }
    }

    public function update_relation($IdRelation, $IdTrad)
    {
        $result = false;
        $sql = "
            UPDATE 
                specialrelations
            SET 
                Comment = " . $IdTrad . "
            WHERE
                Id = " . $IdRelation;
        $s = $this->dao->query($sql);
        if ($s) {
            $result = true;
        }
        return $result;
    }
    
      public function get_relations()
      {
          $all_relations = $this->all_relations();
          $Relations = array();
          foreach($all_relations as $rr) {
              if ($rr->Confirmed == 'Yes')
              array_push($Relations, $rr);
          }
          return $Relations;
      }
      
      public function get_all_relations()
      {
          $words = $this->getWords();
          $sql = "
SELECT
    specialrelations.Id AS id,
    specialrelations.IdRelation AS IdRelation,
    members.Username,
    specialrelations.Type AS Type,
    specialrelations.Comment AS Comment,
    specialrelations.Confirmed AS Confirmed
FROM
    specialrelations,
    members
WHERE
    specialrelations.IdOwner = $this->id  AND
    specialrelations.IdRelation = members.Id AND
    members.Status in ('Active','ActiveHidden','ChoiceInactive') 
          ";
          $s = $this->dao->query($sql);
          $Relations = array();
          while( $rr = $s->fetch(PDB::FETCH_OBJ)) {
              $rr->IdTradComment = $rr->Comment;
//              $rr->Comment = $words->mTrad($rr->Comment);
              array_push($Relations, $rr);
          }
          return $Relations;
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
ORDER BY preferences.position asc
          ";
        $rows = array();
        if (!$sql_result = $this->dao->query($sql)) {
            // sql problem
        } else while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            $rows[$row->codeName] = $row;
        }
        return $rows;
      }

    /**
     * Get value of a user's preference.
     *
     * @param string $name codeName of preference.
     * @param string $default Default value of preference (if not set).
     * @return mixed Preference value, null or default if preference not set.
     */
    public function getPreference($name, $default = false) {
        $preferences = $this->get_preferences();
        foreach ($preferences as $preference) {
            if ($preference->codeName == $name) {
                $value = $preference->Value;
                break;
            }
        }
        if ((!isset($value) || $value == null) && $default !== false) {
            return $default;
        } else {
            return $value;
        }
    }

    /**
     * returns count of profile visitors
     *
     * @access public
     * @return int
     */
    public function getVisitorCount()
    {
        if (!$this->isLoaded())
        {
            return 0;
        }
        return $this->createEntity('ProfileVisit')->getVisitCountForMember($this);
    }

    /**
     * returns array of members that have visited this profile
     *
     * @param PagerWidget $pager - pager containing details of visitor subset to fetch
     *
     * @access public
     * @return array
     */
    public function getVisitorsSubset(PagerWidget $pager) 
    {
        return $this->createEntity('ProfileVisit')->getVisitingMembersSubset($this, $pager);
    }

      public function get_comments() {
          $sql = "
SELECT comments.*,
    comments.Quality AS comQuality,
    comments.id AS id,
    comments.created,
    comments.updated,
    UNIX_TIMESTAMP(comments.created) unix_created,
    UNIX_TIMESTAMP(comments.updated) unix_updated,
    members.username AS UsernameFromMember,
    members2.username AS UsernameToMember
FROM
    comments,
    members,
    members as members2
WHERE
    comments.IdToMember   = " . $this->id . " AND
    comments.IdFromMember = members.Id AND
    comments.IdToMember = members2.Id
    AND members.Status IN ('Active', 'ChoiceInactive')
ORDER BY
    comments.updated DESC
          ";


          //echo $sql;
          //print_r($r);
          return $this->bulkLookup($sql);

      }

      public function get_comments_commenter($id) {
        $id = (int)$id;
          $sql = "
SELECT comments.*,
    comments.Quality AS comQuality,
    comments.id AS id,
    comments.created,
    comments.updated,
    UNIX_TIMESTAMP(comments.created) unix_created,
    UNIX_TIMESTAMP(comments.updated) unix_updated
FROM
    comments,
    members
WHERE
    comments.IdToMember   = $this->id  AND
    comments.IdFromMember = ".$id."  AND
    comments.IdFromMember = members.Id
          ";


          //echo $sql;
          //print_r($r);
          return $this->bulkLookup($sql);

      }

    /**
     * Fetches comments written by this member
     *
     * @return array of objects
     *
     */
    public function get_comments_written() {
        $sql = "
SELECT
  comments.*,
  comments.Quality AS comQuality,
  comments.id AS id,
  comments.created,
  comments.updated,
  UNIX_TIMESTAMP(comments.created) unix_created,
  UNIX_TIMESTAMP(comments.updated) unix_updated,
  members.username as UsernameFromMember,
  members2.username as UsernameToMember
FROM
  comments,
  members,
  members as members2
WHERE
  comments.IdFromMember   = " . $this->id . " AND
  comments.IdFromMember = members.Id AND
  comments.IdToMember = members2.Id
ORDER BY
    comments.updated DESC        ";
        return $this->bulkLookup($sql);
    }


    /**
     * Fetches translation of specific field in user profile.
     * Initializes instance variable $trads if it hasn't been
     * initialized already.
     *
     * @param fieldname name of the profile field
     * @param IdLanguage required translation
     *
     * @return text of $fieldname if available, English otherwise,
     *     and empty string if field has no content
     */
    public function get_trad($fieldname, $IdLanguage,$ReplaceWithBr=False) {
        if (!$this->IsFilled($fieldname)) return("") ;
        return ($this->get_trad_by_tradid($this->$fieldname,$IdLanguage,$ReplaceWithBr)) ;
        
        // Code after this is obsolete (JY)
          if(!isset($this->trads)) {
            $this->trads = $this->get_trads();
        }

        if(!isset($this->trads->$fieldname)  || empty($this->trads->$fieldname))
            return "";
        else {
            $field = $this->trads->$fieldname;
            if(!array_key_exists($IdLanguage, $field)) {
                // echo "Not translated";
                if($IdLanguage != 0 && isset($field[0]))
                    return $field[0]->Sentence;
                foreach ($field as $field_single) {
                    if ($field_single->Sentence != "")
                        return $field_single->Sentence;
                }
                return "";
            }
            else {
                return $field[$IdLanguage]->Sentence;
            }
        }
    }


    public function get_trad_by_tradid($IdTrad, $IdLanguage,$ReplaceWithBr=False) {
        if (!isset($this->trad_by_tradid_inlang[$ReplaceWithBr][$IdTrad][$IdLanguage])) {
            $words = $this->getWords();
            $this->trad_by_tradid_inlang[$ReplaceWithBr][$IdTrad][$IdLanguage]=$words->mInTrad($IdTrad,$IdLanguage,$ReplaceWithBr) ;
        }
        return($this->trad_by_tradid_inlang[$ReplaceWithBr][$IdTrad][$IdLanguage]) ;

    
        // Following code is obsolete
        
        if(!isset($this->trads)) {
            $this->get_trads();
        }

        if(!isset($this->trads_by_tradid[$IdTrad]))
            return "";
        else {
            $trad = $this->trads_by_tradid[$IdTrad];
            if(!array_key_exists($IdLanguage, $trad)) {
                //echo "Not translated";
                if($IdLanguage != 0)
                    return $trad[0]->Sentence;
                else return "";
            }
            else {
                return $trad[$IdLanguage]->Sentence;
            }
        }
    }

    public function setEditMode($state = false)
    {
        $this->edit_mode = $state;
    }

    /**
     * This needs to go someplace else,
     * pending architectural attention
     */
    protected function get_crypted($crypted_id, $return_value = "")
    {
        if ($crypted_id == "" or $crypted_id == 0) return "";
        // check for Admin
        $right = new MOD_right();
        if ($right->hasRight('Admin')) {
            return urldecode(strip_tags(MOD_crypt::AdminReadCrypted($crypted_id)));
        }
        // check for Member's own data
        if ($this->edit_mode) {
            if (($mCrypt = MOD_crypt::MemberReadCrypted($crypted_id)) != "cryptedhidden")
                return urldecode(strip_tags($mCrypt));
        }
        return urldecode(MOD_crypt::get_crypted($crypted_id, $return_value));
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

        return $this->createEntity('GroupMembership')->getMembership($group, $this);
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

        return $this->createEntity('MemberRole')->getMemberRoles($this);
    }

    /**
     * checks if member has a specific role assigned to it
     *
     * @param object $role - the role to check if the member has
     * @param object $object - object to check privilegescopes against
     * @access public
     * @return bool
     */
    public function hasRole($role, $object = false)
    {
        if (!$role->isPKSet() || !$this->isPKSet())
        {
            return false;
        }

        $hasrole = $this->createEntity('MemberRole')->memberHasRole($this, $role);
        if (!$object || !$hasrole)
        {
            return $hasrole;
        }
        return (bool) $this->createEntity('PrivilegeScope')->hasAnyRoleScope($this, $role, $object);
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
        if (!($privilege = $this->createEntity('Privilege')->findNamedPrivilege($controller, $method)) && !($privilege = $this->createEntity('Privilege')->findNamedPrivilege($controller)) && !($privilege = $this->createEntity('Privilege')->findNamedPrivilege('*', '*')))
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
        $priv_scope = $this->createEntity('PrivilegeScope');
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

    // sql_get_set returns in an array the possible set values of the colum of table name
    public function sql_get_set($table, $column)
    {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        if (!($ret = mysql_query($sql)))
            die("Error: Could not show columns $column");

        $line = mysql_fetch_assoc($ret);
        $set = $line['Type'];
        $set = substr($set, 5, strlen($set) - 7); // Remove "set(" at start and ");" at end
        return preg_split("/','/", $set); // Split into and array
    }


    // sql_get_enum returns in an array the possible set values of the colum of table name
    public function sql_get_enum($table, $column)
    {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        if (!($ret = mysql_query($sql)))
            die("Error: Could not show columns $column");

        $line = mysql_fetch_assoc($ret);
        $set = $line['Type'];
        $set = substr($set, 6, strlen($set) - 8); // Remove "enum(" at start and ");" at end
        return preg_split("/','/", $set); // Split into and array
    }

    /**
     * returns array of all post votes the member has cast
     *
     * @access public
     * @return array
     */
    public function getAllPostVotes()
    {
        return $this->createEntity('PostVote')->getVotesForMember($this);
    }

    /**
     * returns array of all thread votes the member has cast
     *
     * @access public
     * @return array
     */
    public function getAllThreadVotes()
    {
        return $this->createEntity('ThreadVote')->getVotesForMember($this);
    }

    /**
     * returns true if the member is Active or ActiveHidden
     *
     * @access public
     * @return bool
     */
    public function isActive()
    {
        if ($this->isLoaded() && in_array($this->Status, array('Active', 'ActiveHidden')))
        {
            return true;
        }
        return false;
    }

    /**
     * returns true if the member is not in state ActiveHidden
     *
     * @access public
     * @return bool
     */
    public function isNotActiveHidden()
    {
        if ($this->isLoaded()  && ($this->Status!='ActiveHidden')) {
            return true;
        }
        return false;
    }

    /**
     * returns true if the member is Pending
     *
     * @access public
     * @return bool
     */
    public function isPending()
    {
        if ($this->isLoaded() && ($this->Status == 'Pending')) {
            return true;
        }
        return false;
    }

    /**
     * returns true if the member is banned
     *
     * @access public
     * @return bool
     */
    public function isBanned()
    {
        if ($this->isLoaded() && $this->Status == 'Banned')
        {
            return true;
        }
        return false;
    }

    /**
     * Records visit of current member to another member's profile, respecting
     * "Show profile visits" preference.
     *
     * @param Member $member Visiting member entity
     * @return bool True if visit recorded, false if not recorded
     */
    public function recordVisit(Member $member)
    {
        if (!$this->isLoaded() || !$member->isLoaded())
        {
            return false;
        }
        $visitorShow = $member->getPreference('PreferenceShowProfileVisits',
            'Yes');
        $ownerShow = $this->getPreference('PreferenceShowProfileVisits',
            'Yes');
        if ($visitorShow == 'Yes' && $ownerShow == 'Yes') {
            $visit = $this->createEntity('ProfileVisit');
            if ($visit->recordVisit($this, $member) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * deletes all a members languages
     *
     * @access public
     * @return bool
     */
    public function removeLanguages()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('MemberLanguage')->deleteMembersLanguages($this);
    }

    /**
     * returns array of the old style rights
     *
     * @access public
     * @return array
     */
    public function getOldRights()
    {
        if (!$this->isLoaded())
        {
            return array();
        }

        if (!$this->old_rights)
        {
            $query = "SELECT * FROM rightsvolunteers AS rv, rights AS r WHERE rv.IdMember = {$this->getPKValue()} AND rv.IdRight = r.id";
            $result = $this->dao->query($query);
            $return = array();
            while ($row = $result->fetch(PDB::FETCH_ASSOC))
            {
                $return[$row['Name']] = $row;
            }
            $this->old_rights = $return;
        }
        return $this->old_rights;
    }
    
    /**
     * sets a new password for this member
     *
     * @param string $pw - new password as string
     *
     * @access public
     * @return bool
     */
    public function setPassword($pw)
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($pw).'\') WHERE `id` = '.$this->id;
        if( $this->model->dao->exec($query)) {
            $L = MOD_log::get();
            $L->write("Password changed", "change password");
            return true;
        } else return false;
    }

    /**
     * logs a member out and deletes the session for the member
     *
     * @access public
     * @return bool
     */
    public function logOut()
    {
        if (!isset($_SESSION) || !$this->isLoaded())
        {
            return false;
        }

        // if "stay logged in active, clear memory cookie
        $this->removeSessionMemory();

        $keys_to_delete = array(
            'IdMember',
            'MemberStatus',
            'Status',
            'lang',
            'IdLang',
            'IsVol',
            'UserName',
            'stylesheet',
            'Param',
            'TimeOffset',
            'PreferenceDayLight',
            'MemberCryptKey',
            'LogCheck',
            'RightLevel',
            'RightScope',
            'FlagLevel',
            );
        foreach ($keys_to_delete as $key)
        {
            if (isset($_SESSION[$key]))
            {
                unset($_SESSION[$key]);
            }
        }
                
        /**
         old stuff from TB - we don't rely on this
        if (!isset($this->sessionName))
            return false;
        if (!isset($_SESSION[$this->sessionName]))
            return false;
        $this->loggedIn = false;
        unset($_SESSION[$this->sessionName]);
        */

        $query = "delete from online where IdMember={$this->getPKValue()}";
        $this->dao->query($query);

        if(isset($_COOKIE) && is_array($_COOKIE))
        {
            $env = PVars::getObj('env');
            if( isset($_COOKIE[$env->cookie_prefix.'userid'])) {
                self::addSetting($_COOKIE[$env->cookie_prefix.'userid'], 'skey');
                setcookie($env->cookie_prefix.'userid', '', time()-3600, '/');
            }
            if( isset($_COOKIE[$env->cookie_prefix.'userkey'])) {
                setcookie($env->cookie_prefix.'userkey', '', time()-3600, '/');
            }
            if( isset($_COOKIE[$env->cookie_prefix.'ep'])) {
                setcookie($env->cookie_prefix.'ep', '', time()-3600, '/');
            }
        }

        // todo: remove this when app_user is finally removed
        APP_User::get()->setLogout();

        session_unset() ;
        session_destroy() ;
        $this->wipeEntity();
        session_regenerate_id();

        return true;
    }

    /**
     * checks if a member has a certain old-type right
     * if member has one of the asked for rights returns true
     *
     * @param array $rights - array of right/scope pairs to check for
     *
     * @access public
     * @return bool
     */
    public function hasOldRight(array $rights)
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $mod_right = new MOD_right;
        foreach ($rights as $right => $scope)
        {
            if ($mod_right->hasRight($right, $scope, $this->getPKValue())) return true;
        }
        return false;
    }

    /**
     * returns array of possible values for Status column
     *
     * @access public
     * @return array
     */
    public function getPossibleStatusArray()
    {
        $info = $this->getTableDescription();
        return $info['Status']['values'];
    }

    /**
     * sets the profile as inactive
     *
     * @access public
     * @return bool
     */
    public function inactivateProfile()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $this->Status = 'ChoiceInactive';
        return $this->update();
    }

    /**
     * sets the profile as active
     *
     * @access public
     * @return bool
     */
    public function activateProfile()
    {
        if (!$this->isLoaded() || in_array($this->Status, array('TakenOut', 'Banned', 'SuspendedBeta', 'AskToLeave', 'PassedAway', 'Buggy')))
        {
            return false;
        }
        $this->Status = 'Active';
        return $this->update();
    }

    /**
     * sets the profile as active
     *
     * @access public
     * @return bool
     */
    public function removeProfile()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $this->Status = 'AskToLeave';
        $return = $this->update();

        // todo: fill in code to actually remove profile here

        return $return;
    }

    /**
     * checks if the profile is displayable
     *
     * @access public
     * @return bool
     */
    public function isBrowsable()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if (in_array($this->Status, array('TakenOut', 'SuspendedBeta', 'AskToLeave', 'PassedAway', 'Buggy', 'Banned', 'Rejected', 'DuplicateSigned')))
        {
            return false;
        }
        return true;
    }

    /**
     * Sends a mail to member's email address (i.e. for notifications).
     *
     * @param string $subject Email subject.
     * @param string $body Email body.
     */
    public function sendMail($subject, $body) {
        $from = PVars::getObj('mailAddresses')->noreply;
        $to = $this->getEmailWithoutPermissionChecks();

        // Create HTML version via purifier (linkify and add paragraphs)
        $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
        $bodyHTML = $purifier->purify($body);

        // Set language for email translations
        $languageCode = $this->getLanguagePreference();

        // TODO: Error handling
        $result = MOD_mail::sendEmail($subject, $from, $to, false, $body, $bodyHTML, false, $languageCode);
    }

    /**
     * Validates "stay logged in" tokens and refreshes them
     *
     * @param boolean   $newsession: flag for a new session (no validation)
     *
     * @return boolean true if cookie refreshed, false if cookie removed
     */
    public function refreshMemoryCookie($newsession = false) {
        $modified = 0;
        if ($newsession === false) {

            $memoryCookie = $this->getMemoryCookie();
            if ($memoryCookie !== false) {

                list($id,$seriesToken,$authToken) = $memoryCookie;

                $seriesTokenEsc = $this->dao->escape($seriesToken);

                // existing session -> validate first
                $s = $this->dao->query('
                                        SELECT
                                            AuthToken, SeriesToken, modified
                                        FROM
                                            members_sessions
                                        WHERE
                                            IdMember = ' . (int)$this->id . '
                                            AND
                                            SeriesToken = \'' . $seriesTokenEsc . '\''
                );
                $tokens = $s->fetch(PDB::FETCH_OBJ);

                // compare tokens from database with those in cookie
                if ($tokens) {
                    $authTokenDB = $tokens->AuthToken;
                    $seriesToken = $tokens->SeriesToken;
                    $modified = $tokens->modified;
                    if ($authToken !== $authTokenDB) {
                        // auth token incorrect but series token correct -> hijacked
                        $this->removeSessionMemory($seriesToken, true);
                        return false;
                    }
                } else {
                    // both tokens (or just series token) incorrect
                    $this->removeSessionMemory($seriesToken);
                    return false;
                }
            } else {
                $this->removeSessionMemory(); // just to clean up token records in database
                return false;
            }

            // both tokens correct -> continue
            // log in user
            $loginModel = new LoginModel;
            $tb_user = $loginModel->getTBUserForBWMember($this);
            $loginModel->setupBWSession($this);
            $loginModel->setTBUserAsLoggedIn($tb_user);

        } else {
            // create series token
            $seriesToken = md5(rand()+time());
        }

        // create auth token
        $authToken = md5(rand()+time());

        // write tokens to database
        if ($modified) {
            // update token from existing series
            $s = $this->dao->query('
                                    UPDATE
                                        members_sessions
                                    SET
                                        AuthToken = \'' . $authToken . '\'
                                    WHERE
                                        IdMember = ' . (int) $this->id . ' AND SeriesToken = \'' . $seriesToken . '\''
            );
        } else { // create new token series
            $s = $this->dao->query('
                                    INSERT INTO
                                        members_sessions
                                        (IdMember, AuthToken, SeriesToken)
                                    VALUES
                                        (' . (int) $this->id . ', \'' . $authToken . '\', \'' . $seriesToken . '\')'
            );
        }

        // create cookie
        $this->setMemoryCookie($this->id, $seriesToken, $authToken);

        return true;
    }

    /**
     * Removes "stay logged in" tokens and cookie
     *
     * @param  boolean $hijacked: true if session was hijacked
     *
     * @return boolean (always true)
     */
    public function removeSessionMemory($seriesToken = '', $hijacked = false) {
        if (empty($seriesToken)) {
            // no cookie passed -> get current one
            $memoryCookie = $this->getMemoryCookie();
            if ($memoryCookie !== false) {
                $seriesToken = $memoryCookie[1];
            }
        }
        $seriesTokenEsc = $this->dao->escape($seriesToken);
        // remove tokens from database
        // (also removes tokens more than cookie expiry)
        $s = $this->dao->query('
                                DELETE FROM
                                    members_sessions
                                WHERE
                                    (IdMember = ' . (int) $this->id . '
                                    AND
                                    SeriesToken = \'' . $seriesTokenEsc . '\')
                                    OR
                                    modified < NOW() - INTERVAL ' . PVars::getObj('env')->rememberme_expiry . ' DAY'
        );

        if ($hijacked === true) {
            // session hijacked
            setcookie('bwRemember', 'hijacked', time() + 3600, '/');
        } else {
            // remove cookie
            $this->setMemoryCookie(false);
        }

        return true;
    }

    /**
     * returns a bool based on whether the
     * member is able to log in or not, based
     * on status
     *
     * @access public
     * @return bool
     */
    public function canLogIn()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        if (in_array($this->Status, array('Rejected', 'TakenOut', 'Banned', 'SuspendedBeta', 'AskToLeave', 'PassedAway', 'Buggy', 'DuplicateSigned')))
        {
            return false;
        }
        return true;
    }
}
