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
    public $address = null;
    private $profile_languages = null;
    private $edit_mode = false;

    public function __construct($member_id = false)
    {
        parent::__construct();
        if ($member_id)
        {
            $this->findById($member_id);
        }
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
        $str = "SELECT SQL_CACHE memberslanguageslevel.IdLanguage AS IdLanguage,languages.Name,languages.ShortCode AS ShortCode, " .
          "memberslanguageslevel.Level AS Level FROM memberslanguageslevel,languages " .
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
     * automatically called by __get('trads'),
     * when someone writes '$member->trads'
     *
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
     * Get the status of the member's profile (public/private)
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

    public function get_email() {
        return $this->get_crypted($this->Email, "");
    }

    public function get_messengers() {
          $messengers = array(
            array("network" => "GOOGLE", "nicename" => "Google Talk", "image" => "icon_gtalk.png", "href" => ""),
            array("network" => "ICQ", "nicename" => "ICQ", "image" => "icon_icq.png", "href" => ""),
            array("network" => "AOL", "nicename" => "AIM", "image" => "icon_aim.png", "href" => "aim:goim?"),
            array("network" => "MSN", "nicename" => "MSN", "image" => "icon_msn.png", "href" => "msnim:chat?contact="),
            array("network" => "YAHOO", "nicename" => "Yahoo", "image" => "icon_yahoo.png", "href" => "ymsgr:sendIM?"),
            array("network" => "SKYPE", "nicename" => "Skype", "image" => "icon_skype.png", "href" => "skype:echo"),
            array("network" => "Others", "nicename" => "Other", "image" => "", "href" => "#")
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


    public function get_age() {
        $age = $this->get_crypted("age", "");
        return $age;
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

        return $this->createEntity('GroupMembership')->getMemberGroups($this, 'In');
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
     */
    protected function get_address() {
        $sql = <<<SQL
SELECT
    SQL_CACHE a.*
FROM
    addresses AS a
WHERE
    a.IdMember = {$this->id}
SQL;
        ;
        $a = $this->bulkLookup($sql);
        if($a != null && sizeof($a) > 0)
        {
            $city = $this->createEntity('Geo')->findById($a[0]->IdCity);
            if ($city)
            {
                $a[0]->CityName = $city->getName();
            }
            $region = $city->getParent();
            $country = $city->getCountry();
            if ($region && $country)
            {
                $a[0]->RegionName = $region->getPKValue() == $country->getPKValue() ? '' : $region->getName();
                $a[0]->CountryName = $country->getName();
                $a[0]->CountryCode = $country->fk_countrycode;
            }
        }
        else
        {
            $a[0] = new stdClass();
            $a[0]->IdCity = '';
            $a[0]->CityName = 'Unknown';
            $a[0]->HouseNumber = 'Unknown';
            $a[0]->StreetName = 'Unknown';
            $a[0]->Zip = 'Unknown';
        }
        $a[0]->CityName = ((!empty($a[0]->CityName)) ? $a[0]->CityName : '');
        $a[0]->RegionName = ((!empty($a[0]->RegionName)) ? $a[0]->RegionName : '');
        $a[0]->CountryName = ((!empty($a[0]->CountryName)) ? $a[0]->CountryName : 'Unknown');
        $a[0]->CountryCode = ((!empty($a[0]->CountryCode)) ? $a[0]->CountryCode : 'Unknown');

        $this->address = $a[0];
    }

    
	/*
	* this function get the number of post of the current member
	*/
	public function forums_posts_count() {
		// Todo (jyh) : to make it more advanced and consider the visibility of current surfing member
		if (isset($this->ForumPostCount)) {
			return($this->ForumPostCount)  ; // Nota: in case a new post was make during the session it will not be considerated, this is a performance compromise
		}
		else {
			$sql = "SELECT count(*) as cnt from forums_posts where IdWriter=".$this->id ;
			$rr = $this->singleLookup($sql);
			if ($rr) {
				$this->ForumPostCount=$rr->cnt;
			}
			else {
				$this->ForumPostCount=0 ;
			}
			return($this->ForumPostCount)  ; // Nota: in case a new post was make during the session it will not be considerated, this is a performance compromise
		}
	} // forums_posts_count
	
    public function get_verification_status()
    {
        // Loads the vérification level of the member (if any) 
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
            return $rr->Type;
        }
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
    specialrelations.IdRelation = members.Id 
          ";
          $s = $this->dao->query($sql);
          $Relations = array();
          while( $rr = $s->fetch(PDB::FETCH_OBJ)) {
              $rr->Comment = $words->mTrad($rr->Comment);
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
ORDER BY Value asc
          ";
        $rows = array();
        if (!$sql_result = $this->dao->query($sql)) {
            // sql problem
        } else while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            $rows[$row->codeName] = $row;
        }
        return $rows;
      }


        public function get_visitors_raw() {
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

            // FIXME: Not the best way to provide pagination. But for now there's not better choice.
            $visitors = $this->dao->query($sql);
            return $visitors;
        }

        public function get_visitors() 
        {
            $s = $this->get_visitors_raw();
			if (!$s) return false;
			$visitors = array();
	        while ($rr = $s->fetch(PDB::FETCH_OBJ)) {
	            array_push($visitors, $rr);
	        }
            return $visitors;
        }

      public function get_comments() {
          $sql = "
SELECT *,
    comments.Quality AS comQuality,
    comments.id AS id
FROM
    comments,
    members
WHERE
    comments.IdToMember   = $this->id  AND
    comments.IdFromMember = members.Id
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
SELECT *,
    comments.Quality AS comQuality,
    comments.id AS id
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

        if(!isset($this->trads->$fieldname)  || empty($this->trads->$fieldname))
            return "";
        else {
            $field = $this->trads->$fieldname;
            if(!array_key_exists($language, $field)) {
                // echo "Not translated";
                if($language != 0 && isset($field[0]))
                    return $field[0]->Sentence;
                foreach ($field as $field_single) {
                    if ($field_single->Sentence != "")
                        return $field_single->Sentence;
                }
                return "";
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
}

