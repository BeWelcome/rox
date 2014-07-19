<?php
/*
Copyright (c) 2007 BeVolunteer

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
 * searchmembers model
 *
 * @package searchmembers
 * @author matrixpoint
 */
class Searchmembers extends RoxModelBase
{
    private $column_sort_order = array(
            'members.created' => 'FindPeopleNewMembers',
            'BirthDate'       => 'Age',
            'LastLogin'       => 'Lastlogin',
            'Comments'        => 'Comments',
            'Accomodation'    => 'Accomodation',
        );

    private $default_sort_direction = array(
            'members.created' => 'DESC',
            'BirthDate'       => 'DESC',
            'LastLogin'       => 'DESC',
            'Comments'        => 'DESC',
            'Accomodation'    => 'ASC',
        );

    private $default_column = 'Accomodation';

    // supported languages for translations; basis for flags in the footer
    private $_langs;

    /**
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return boolean if language is supported true, otherwise false
     */
    public function isValidLang($lang)
    {
        $this->getLangs();
        return in_array($lang, $this->_langs);
    }

    /**
     * fills the _langs array with data
     *
     * @access private
     * @return void
     */
    private function getLangs()
    {
        if (!isset($this->_langs) || !is_array($this->_langs))
        {
            // TODO: it is fun to offer the members the language of the volunteers, i.e. 'prog',
            // so I don't make any exceptions here; but we miss the flag - the BV flag ;-)
            // TODO: is it consensus we use "WelcomeToSignup" as the decision maker for languages?
            $query =<<<SQL
SELECT  ShortCode
FROM    words
WHERE   code = 'WelcomeToSignup'
SQL;
            $result = $this->dao->query($query);
            while ($row = $result->fetch(PDB::FETCH_OBJ)) {
                $this->_langs[] = $row->ShortCode;
            }
        }
    }

    /**
     * @param
     * @return associative array mapping language abbreviations to
     *             long, English names of the language
     */
    public function getLangNames()
    {
        $this->getLangs();

        $l =  '';
        foreach ($this->_langs as $lang) {
            $l .= '\'' . $lang . '\',';
        }
        $l = substr($l, 0, (strlen($l)-1));

        $query =<<<SQL
SELECT  EnglishName, ShortCode
FROM    languages
WHERE   ShortCode IN ($l)
SQL;
        $result = $this->dao->query($query);

        $langNames = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $langNames[$row->ShortCode] = $row->EnglishName;
        }
        return $langNames;
    }

    public function quicksearch($_searchtext)
    {
        $TMembers = array ();
        $TReturn->searchtext = $_searchtext ;

        if(strlen($_searchtext) > 2)
        {
            $searchtext = $this->dao->escape(str_replace('*','%',$_searchtext)); // Allows for wildcard

            // search for username
            $where = '';
            $tablelist = '';
            if (!$this->getLoggedInMember())
            {
                $where = "AND memberspublicprofiles.IdMember = m.id"; // must be in the public profile list
                $tablelist = ",memberspublicprofiles";
            }
            $str =<<<SQL
SELECT
    m.id AS IdMember,
    m.Username,
    m.Gender,
    m.HideGender,
    a.IdCity,
    m.ProfileSummary
FROM
    members AS m,
    addresses AS a
    {$tablelist}
WHERE
    m.Status = 'Active'
    AND m.Username LIKE '{$searchtext}'
    AND a.IdMember = m.id
    $where
LIMIT 20
SQL;

            $qry = $this->dao->query($str);

            while ($rr = $qry->fetch(PDB::FETCH_OBJ))
            {
                $geo = $this->createEntity('Geo')->findById($rr->IdCity);
                if (!($country = $geo->getCountry()) || !($parent = $geo->getParent()))
                {
                    $this->logWrite("FindMember(Missing country result for geonames_cache - id = {$rr->IdCity}", "Bug");
                    $rr->RegionName="";
                    $rr->CountryName='';
                    $rr->CityName = '';
                    $rr->fk_countrycode = '';
                }
                else
                {
                    $rr->CountryName = $country->name ;
                    $rr->CityName = $geo->name ;
                    $rr->fk_countrycode = $geo->fk_countrycode ;
                    $rr->RegionName = $parent->name;
                }

                $rr->ProfileSummary = $this->ellipsis($this->FindTrad($rr->ProfileSummary), 100);
                $rr->result = '';

                $query = $this->dao->query("SELECT SQL_CACHE    *
FROM
    membersphotos
WHERE
    IdMember=" . $rr->IdMember . " AND
    SortOrder=0
                "
                );
                $photo = $query->fetch(PDB::FETCH_OBJ);

                if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
                else $rr->photo=$this->DummyPict($rr->Gender,$rr->HideGender) ;
                $rr->photo = MOD_layoutbits::linkWithPicture($rr->Username, $rr->photo);
                array_push($TMembers, $rr);
            }
        } // end of search for username
        $TReturn->TMembers=$TMembers ;

// Now search in places
        $TPlaces=array() ;

                if(strlen($_searchtext) > 1) { // Needs to give more that two chars for a place
            $searchtext = $this->dao->escape($_searchtext) ;
            $str = "SELECT DISTINCT(geonames_cache.geonameId) AS geonameid FROM geonamesalternatenames,geonames_cache WHERE alternateName='{$searchtext}' and geonamesalternatenames.geonameid=geonames_cache.geonameid";
            $qry = $this->dao->query($str);

            while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
                $str="select geonames_cache.*,geo_usage.count as NbMembers from geonames_cache left join geo_usage on geonames_cache.geonameid=geo_usage.geoId and typeId=1 where geonames_cache.geonameid=".$rr->geonameid;
                $result = $this->dao->query($str);
                $cc = $result->fetch(PDB::FETCH_OBJ);

                // Jeanyves trying to find a bug when venice is search in the quicksearch
                if (empty($cc->fcode)) {
            		$this->logWrite("SearchMembersModel : Failed to [".$str."] searchtext=[".$searchtext."]", "Bug");
                }
                // end of Jeanyves trying to find a bug when venice is search in the quicksearch

                if (($cc->fcode=='PPLI') or ($cc->fcode=='PCLI')or ($cc->fcode=='PCLD')or ($cc->fcode=='PCLS')or ($cc->fcode=='PCLF')or ($cc->fcode=='PCLX')){
                    $cc->TypePlace='country' ; // Becareful this will be use as a word, take care with lowercase, don't change
                    $cc->link="places/".$cc->fk_countrycode ;
                    $cc->CountryName="" ;
                    $cc->RegionName="" ;
                }
                elseif (($cc->fcode=='PPL')or($cc->fcode=='PPLA')or($cc->fcode=='PPLG')or($cc->fcode=='PPLC')or($cc->fcode=='PPLS')or($cc->fcode=='PPLX')or($cc->fcode=='PPLA2')or($cc->fcode=='PPLA3')or($cc->fcode=='PPLA4')) {
                    $cc->TypePlace='City' ; // Becareful this will be use as a word, take care with lowercase, don't change
                    $sRegion="select name from geonames_cache where geonameid=".$cc->parentAdm1Id;
                    $qryRegion = $this->dao->query($sRegion);
                    $Region=$qryRegion->fetch(PDB::FETCH_OBJ)  ;

                    $sCountry="select name from geonames_cache where geonameid=".$cc->parentCountryId;
                    $qryCountry = $this->dao->query($sCountry);
                    $Country=$qryCountry->fetch(PDB::FETCH_OBJ)  ;
                    if (isset($Country->name)) {
						$cc->CountryName=$Country->name ;
					}
					else {
						$cc->CountryName="" ;
					}

                    if (isset($Region->name)) {
                    	$cc->RegionName=$Region->name ;
                        $cc->link="places/".$cc->fk_countrycode."/".$Region->name."/".$cc->name ;
                    }
                    else {
                        $cc->link="places/".$cc->fk_countrycode."//".$cc->name ;
			$cc->RegionName='No region' ;
                    }
                }
                elseif (($cc->fcode=='ADM1') or ($cc->fcode=='ADM2')or ($cc->fcode=='ADMD')) {
                    $sCountry="select name from geonames_cache where geonameid=".$cc->parentCountryId;
                    $qryCountry = $this->dao->query($sCountry);
                    $Country=$qryCountry->fetch(PDB::FETCH_OBJ)  ;
                    $cc->CountryName=$Country->name ;

                    $cc->RegionName="" ;
                    $cc->TypePlace='Region' ; // Becareful this will be use as a word, take care with lowercase, don't change
                    $cc->link="places/".$cc->fk_countrycode."/".$cc->name ;
                }
                $cc->searchtext=$searchtext ;
                array_push($TPlaces, $cc);

            }
        }// end of search for Places
        $TReturn->TPlaces=$TPlaces ;


// Now search in forums tags
        $TForumTags=array() ;

        if(strlen($_searchtext) > 1) { // Needs to give more that two chars for a place
            $searchtext=mysql_real_escape_string($_searchtext) ;
            $str="select forums_tags.id as IdTag,counter as NbThreads
            from forums_tags,translations
            where forums_tags.IdName=translations.IdTrad and Sentence='".$searchtext."'" ;
            $qry = $this->dao->query($str);
            while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
                $rr->link="forums/t".$rr->IdTag ;
                array_push($TForumTags, $rr);
            }
        }// end of search for forums tags
        $TReturn->TForumTags = $TForumTags;
        return($TReturn) ;

    }

    private function ellipsis($str, $len)
    {
        $length = strlen($str);
        if($length <= $len) return $str;
        return mb_substr($str, 0, $len, 'utf-8').'...';
    }

    /**
     * monstrous beast of a method to search for members by various input
     *
     * @param array $vars - POST array
     *
     * @access public
     * @return array
     *
     */
    public function search(&$vars) {
        // get pagination parameters
        $limitcount = intval($this->GetParam($vars, 'limitcount', 10)); // Number of records per page
        if ($limitcount > 100) $limitcount = 100;
        $vars['limitcount'] = $limitcount;

        $start_rec = intval($this->GetParam($vars, 'start_rec', 0)); // Number of records per page
        $vars['start_rec'] = $start_rec;

        // determine sort order
        list($order_by, $direction) = $this->getOrderDirection($this->GetParam($vars, 'OrderBy', 'Accomodation'), $this->GetParam($vars, 'OrderByDirection',0) ? 1 : 0);

        $OrderBy = '';
        if ($order_by != 'Accomodation' && $direction != 'ASC') {
            $OrderBy = 'ORDER BY '. $order_by . ' ' . $direction . ', HasLoggedIn DESC, HasSummary DESC, members.Accomodation ASC, members.LastLogin DESC';
        } else {
            $OrderBy = 'ORDER BY HasLoggedIn DESC, HasSummary DESC, members.Accomodation ASC, members.LastLogin DESC';
        }

        $vars['OrderBy'] = $order_by;

        // tables to query
        $tablelist = "members, geonames_cache, geonames_countries, addresses";

        // get all conditions for search
        $where = 'WHERE ' . $this->generateMemberTypeCond($vars)
            . ' AND ' . $this->generateAvailabilityLevelCond($vars)
            . ' AND ' . $this->generateTypicalOfferCond($vars)
            . ' AND ' . $this->generateUsernameCond($vars)
            . ' AND ' . $this->generateKeywordCond($vars)
            . ' AND ' . $this->generateRegionCond($vars)
            . ' AND ' . $this->generateGenderCond($vars)
            . ' AND ' . $this->generateAgeCond($vars)
            . ' AND ' . $this->generateGroupCond($vars);

        // sorting by age, implies leaving out members with hidden age
        if ($order_by == 'BirthDate') {
            $where .= ' AND members.HideBirthDate=\'No\'';
        }

        $visitorsWhere = $this->generateVisitorsOnlyCond($vars);

        // if there is a condition using membertrads table, include it in table list for query
        if (preg_match('/memberstrads/i',$where)) {
            $tablelist .= ', memberstrads';
        }
        // if there is a condition using membergroups table, include it in table list for query
        if (preg_match('/membersgroups/i',$where)) {
            $tablelist .= ', membersgroups';
        }

        // map boundaries for search
        if($this->GetParam($vars, "mapsearch")) {
            $where .= ' AND ' . $this->generateMapSearchCond($vars);
        }

        $where .= ' AND ' . $this->generateLocationSearchCond($vars);

        // safety catch (never show all records)
        if (empty($where)) {
            $this->logWrite('empty where. input: ' . print_r($vars, true), 'Search');
            $where = ' WHERE (1=0)';
        }

        // clean up meaningless conditions
        $where = preg_replace('/ AND 1=1/','',$where);
        $fullWhere = $where;
        $tablelistAll = $tablelist;
        if ($visitorsWhere) { // hide non-public profiles from visitors
            $fullWhere = $where . ' AND ' . $visitorsWhere;
            $tablelist .= ', memberspublicprofiles';
        }

        // perform search
        $TMember = $this->doSearch($vars, $tablelist, $fullWhere, $OrderBy, $start_rec, $limitcount);

        // get full count of search results if not logged in
        $rCountFull = -1;
        if ($visitorsWhere) {
            $result = $this->dao->query('
                SELECT
                    COUNT(DISTINCT members.id) AS fullCount
                FROM
                    (' . $tablelistAll . ')
                ' . $where);
            $row = $result->fetch(PDB::FETCH_OBJ);
            $rCountFull = $row->fullCount;
        }
        $vars['rCountFull'] = $rCountFull;

        return($TMember);
    }

    /**
    *
    * Runs a members search with passed restrictions and returns
    * a list of matches
    *
    * @param	array	 $vars: input variables to be passed back (passed by reference)
    * @param    string   $tablelist: list of tables to query
    * @param    string   $where: WHERE condition
    * @param    string   $orderBy: ORDER BY for query
    * @param    string   $start: first match to show (from pagination)
    * @param    string   $limit: ORDER BY for query
    *
    * @return   array    list of matches (member records)
    *
    * @TODO: Optimise queries (jsfan)
    */
    private function doSearch(&$vars, $tablelist, $where, $orderBy, $start = 0, $limit = 100) {
        $TMember=array();

        // This query only fetch indexes (because SQL_CALC_FOUND_ROWS can be a pain)
        $str = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
                    members.id AS IdMember,
                    Username,
                    geonames_cache.name AS CityName,
                    geonames_countries.name AS CountryName,
                    IF(members.ProfileSummary != 0, 1, 0) AS HasSummary,
                    IF(DATEDIFF(NOW(), members.LastLogin) < 300, 1, 0) AS HasLoggedIn
                FROM
                    (' . $tablelist . ')
                ' . $where . '
                ' . $orderBy . '
                LIMIT ' . $start . ',' . $limit;

        $qry = $this->dao->query($str);
        $result = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $row = $result->fetch(PDB::FETCH_OBJ);
        $rCount= $row->cnt;

        $vars['rCount'] = $rCount;

        while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
            $sData = 'SELECT
                          m.created,
                          m.BirthDate,
                          m.HideBirthDate,
                          m.Accomodation,
                          m.ProfileSummary,
                          m.Gender,
                          m.HideGender,
                          date_format(m.LastLogin,\'%Y-%m-%d\') AS LastLogin,
                          gc.latitude AS Latitude,
                          gc.longitude AS Longitude
                      FROM
                          members AS m,
                          geonames_cache AS gc,
                          addresses AS a
                      WHERE
                          a.IdCity = gc.geonameid
                          AND m.id = ' . $rr->IdMember . '
                          AND m.id = a.IdMember';

            $qryData = $this->dao->query($sData);
            $rData = $qryData->fetch(PDB::FETCH_OBJ) ;

            $rr->created        = $rData->created;
            $rr->BirthDate      = $rData->BirthDate;
            $rr->HideBirthDate  = $rData->HideBirthDate;
            $rr->Accomodation   = $rData->Accomodation;
            $rr->ProfileSummary = $this->ellipsis($this->FindTrad($rData->ProfileSummary,true), 200);
            $rr->Gender         = $rData->Gender;
            $rr->HideGender     = $rData->HideGender;
            $rr->LastLogin      = $rData->LastLogin;
            $rr->Latitude       = $rData->Latitude;
            $rr->Longitude      = $rData->Longitude;

            $sData ="
            SELECT
                COUNT(*) as NbComment
            FROM
                comments,
                members
            WHERE
                comments.IdToMember =" . $rr->IdMember . "
                AND
                members.id = comments.IdFromMember
                AND
                members.status IN ('Active')
            "
            ;
            $qryData = $this->dao->query($sData);
            $rData = $qryData->fetch(PDB::FETCH_OBJ) ;
            $rr->NbComment=$rData->NbComment ;

            $query = $this->dao->query('SELECT SQL_CACHE * FROM  membersphotos WHERE IdMember=". $rr->IdMember . " AND SortOrder=0');
            $photo = $query->fetch(PDB::FETCH_OBJ);

            if (isset($photo->FilePath)) {
                $rr->photo = $photo->FilePath;
            } else {
                $rr->photo = $this->DummyPict($rr->Gender,$rr->HideGender);
            }

            $rr->photo = MOD_layoutbits::linkWithPicture($rr->Username, $rr->photo, 'map_style');

            if ($rr->HideBirthDate=="No") {
                $rr->Age=floor($this->fage_value($rr->BirthDate));
            } else {
                $rr->Age= "Hidden";
            }

            // push found record to list of members to be output
            array_push($TMember, $rr);
        }

        return $TMember;
    }

   /**
    *
    * If map boundaries provided, creates condition to take them into
    * account
    *
    * @param  array		$vars: Variables from query (passed by reference)
    *
    * @return string    WHERE condition
    */
    private function generateMapSearchCond(&$vars) {

        $where = '1=1'; // initialise condition

        // preset latitudes
        $latSW = floatval($this->GetParam($vars, "bounds_sw_lat"));
        $latNE = floatval($this->GetParam($vars, "bounds_ne_lat"));

        // preset longitudes
        $longSW = floatval($this->GetParam($vars, "bounds_sw_lng"));
        $longNE = floatval($this->GetParam($vars, "bounds_ne_lng"));

        // restrict latitude
        if($latSW > $latNE) { // searching across pole (impossible on map?)
            $where .= '
                AND ((
                    geonames_cache.latitude > ' . $latSW . '
                    AND
                    geonames_cache.latitude <= 90
                ) OR (
                    geonames_cache.latitude >= -90
                    AND
                    geonames_cache.latitude < ' . $latNE . '))';
        } else {
            $where .= '
                AND (
                    geonames_cache.latitude > ' . $latSW .'
                    AND
                    geonames_cache.latitude < ' . $latNE . '
                )';
        }

        // restrict longitude
        if($longSW > $longNE) { // searching across 180th meridian
            $where .= '
        AND ((
            geonames_cache.longitude >= ' . $longSW . '
            AND
            geonames_cache.longitude <= 180
        ) OR (
            geonames_cache.longitude >= -180
            AND
            geonames_cache.longitude <= ' . $longNE . '))'  ;
        }
        else
        {
            $where .= '
        AND (
            geonames_cache.longitude > ' . $longSW . '
            AND
            geonames_cache.longitude < ' . $longNE . ')' ;
        }

        return '1=1 AND ' . $where;
    }

   /**
    *
    * If map boundaries provided, creates condition to take them into
    * account
    *
    * @param   array		$vars: Variables from query (passed by reference)
    *
    * @return  string       WHERE condition
    */
    private function generateLocationSearchCond(&$vars) {
        $where = '1=1';

        // only use consistent records
        $where .= " AND geonames_cache.geonameid = addresses.IdCity AND addresses.IdMember = members.id AND geonames_countries.iso_alpha2=geonames_cache.fk_countrycode" ;
        if ($IdCountry = $this->GetParam($vars, 'IdCountry', 0)) {
            $where .= " AND geonames_countries.iso_alpha2='" . $IdCountry . "'";
        }
        if ($IdCity = $this->GetParam($vars, 'IdCity', 0)) {
            $where .= ' AND geonames_cache.geonameid=' . $IdCity;
        }
        if (($g_city = $this->GetParam($vars, 'CityName', '')) || ($g_city = $this->GetParam($vars, 'CityNameOrg', ''))) {
            if ($places = $this->createEntity('Geo')->findLocationsByName($g_city)) {
                foreach ($places as $geo) {
                    if ($geo->isCity() || $geo->isBorough()) {
                        $WhereCity = 'geonames_cache.geonameid = ' . $geo->getPKValue();
                        break;
                    }
                }
            }
        }
        if (($coordinates = $this->GetParam($vars, 'place_coordinates', ''))
                && ($accuracy = $this->GetParam($vars, 'accuracy_level'))
                && intval($accuracy) > 1
                && !isset($WhereCity)) {

            list($long, $lat, $alt) = explode(',', $coordinates);

            foreach ($this->createEntity('Geo')->findLocationsByCoordinates(array('long' => $long, 'lat' => $lat)) as $geo)
            {
                if ($geo->isCity() || $geo->isBorough()) {
                    $cities[] = $geo->geonameid;
                }
            }

            if (!empty($cities)) {
                $WhereCity = 'geonames_cache.geonameid IN (' . implode(',', $cities) . ')';
            } else {
                $WhereCity = "1 = 0";
            }
        }
        if (isset($WhereCity)) {
            $where .= " AND {$WhereCity}";
        }

        return $where;
    }

   /**
    *
    * Reads passed parameters for member type to be found and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateMemberTypeCond(&$vars) {
        if ($this->GetParam($vars, 'IncludeInactive', '0') == '1') {
            return "(members.Status in ('Pending', 'Active', 'OutOfRemind'))";
        }
        // default is active members only
        return "(members.Status = 'Active' AND members.Accomodation != 0)";
    }

   /**
    *
    * Reads passed parameters for hosting availability to be found and
    * processes condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateAvailabilityLevelCond(&$vars) {
        $where_accomodation = array();
        if(array_key_exists('Accomodation', $vars)) {
            $Accommodation = $vars['Accomodation'];
            if(is_array($Accommodation))
            {
                foreach ($Accommodation as $value) {
                    if($value == '') continue;
                    $vars['Accomodation'] = $value;
                    $value = $this->GetParam($vars, 'Accomodation');
                    $where_accomodation[] = "Accomodation='" . $this->dao->escape($value) . "'";
                }
            }
            if ($where_accomodation) {
                return "(" . implode(" OR ", $where_accomodation) . ")";
            }
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for typical offer to be found and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateTypicalOfferCond(&$vars) {
        $where_typicoffer = array();
        if(array_key_exists('TypicOffer', $vars)) {
            $TypicOffer = $vars['TypicOffer'];
            if(is_array($TypicOffer))
            {
                foreach($TypicOffer as $value)
                {
                    if($value == '') continue;
                    $vars['TypicOffer'] = $value;
                    $value = $this->GetParam($vars, 'TypicOffer');
                    $where_typicoffer[] = "FIND_IN_SET('" . $this->dao->escape($value) . "',TypicOffer)" ;
                }
            }
        }
        if($where_typicoffer) {
            return "(" . implode(" AND ", $where_typicoffer) . ")";
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for username to search for and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */

    private function generateUsernameCond(&$vars) {
        if ($Username = $this->GetParam($vars, 'Username', '')) {
            // create LIKE condition from query parameter with wildcards
            if (strpos($Username, "*") !== false) {
                $Username = str_replace("*","%",$Username);
                return "Username LIKE '" . $this->dao->escape($Username) . "'";
            }
            // no wildcards -> convert username to id and back to definitely get current one
            $Username = $this->fUserName($this->IdMember($this->GetParam($vars, "Username"))) ;
            return "Username ='" . $this->dao->escape($Username) . "'";
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for keywords to search for and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateKeywordCond(&$vars) {
        if ($TextToFind = $this->GetParam($vars, 'TextToFind', '')) {
            // Special case where from the quicksearch the user is looking for a username
            // in this case, if there is a username corresponding to TextToFind, we force to retrieve it
            if (($this->GetParam($vars, 'OrUsername', 0) == 1) && ($this->IdMember($TextToFind) != 0)) {
                return "Username=' . $this->dao->escape($TextToFind) . '";
            }
            else {
                return "memberstrads.Sentence LIKE '%" . $this->dao->escape($TextToFind) . "%' AND memberstrads.IdOwner=members.id";
            }
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for region to search for and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateRegionCond(&$vars) {
        if ($IdRegion = $this->GetParam($vars, 'IdRegion', '')) {
            return "geonames_cache.parentAdm1Id = " . $this->dao->escape($IdRegion);
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for gender to search for and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateGenderCond(&$vars) {
        $gender = $this->GetParam($vars, 'Gender', '0');

        if ( strlen($gender) > 1 && $gender != 'genderOther') {
            return "Gender='" . $this->dao->escape($gender) . "' AND HideGender='No'";
        } elseif ($gender == 'genderOther') {
            return "Gender = 'other' AND HideGender='No'";
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for age range to search for and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateAgeCond(&$vars) {
        $operation = '';
        if ($minAge = $this->GetParam($vars, 'MinimumAge', '0')) {
            $operation .= 'members.BirthDate<=(NOW() - INTERVAL ' . $minAge . ' YEAR)'  ;
        }
        if ($maxAge = $this->GetParam($vars, 'MaximumAge', '0')) {
            if ($operation) {
                $operation .= 'AND members.BirthDate >= (NOW() - INTERVAL ' . $maxAge . ' YEAR)' ;
            } else {
                $operation = 'members.BirthDate >= (NOW() - INTERVAL ' . $maxAge . ' YEAR)' ;
            }
        }
        if($operation) {
            return $operation . " AND members.HideBirthDate='No'";
        }
        return '1=1';
    }

   /**
    *
    * Reads passed parameters for group to search in and processes
    * condition for query
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string  WHERE condition
    */
    private function generateGroupCond(&$vars) {
        if ($Group = $this->GetParam($vars, 'IdGroup',0)) {
            return 'membersgroups.IdGroup=' . $Group . "
                    AND membersgroups.Status='In'
                    AND membersgroups.IdMember=members.id";
        }
        return '1=1';
    }

   /**
    *
    * Checks if search is performed without login and adds condition for
    * visitors to only see public profiles
    *
    * @param array		$vars: Variables from query (passed by reference)
    *
    * @return  string/boolean  WHERE condition (false if logged in)
    */
    private function generateVisitorsOnlyCond(&$vars) {
        if ($this->getLoggedInMember()) {
            return false;
        }
        return "memberspublicprofiles.IdMember=members.id";
    }

    //------------------------------------------------------------------------------
    // Get param returns the param value if any
    private function GetParam($vars, $param, $defaultvalue = "")
    {
        if (!isset($vars[$param]))
        {
            return $defaultvalue;
        }

        $m = $this->dao->escape($vars[$param]);
        if (empty($m) and ($m!="0"))
        {
            return ($defaultvalue);
        }
        else
        {
            return ($m);
        }
    }

    //------------------------------------------------------------------------------
    // function IdMember return the numeric id of the member according to its username
    // This function will TARNSLATE the username if the profile has been renamed.
    // Note that a numeric username is provided no Username trnslation will be made
    private function IdMember($username) {
        if (is_numeric($username)) { // if already numeric just return it
            return ($username);
        }
        $query = $this->dao->query(
           "
SELECT SQL_CACHE
    id,
    ChangedId,
    Username,
    Status
FROM
    members
WHERE
    Username='" . mysql_real_escape_string($username) . "'
            "
        );
        $rr = $query->fetch(PDB::FETCH_OBJ);
        if(!$rr) return (0);
        if ($rr->ChangedId > 0) { // if it is a renamed profile
            $qry = $this->dao->query(
                "
SELECT SQL_CACHE
    id,
    Username
FROM
    members
WHERE
    id = $rr->ChangedId
                "
            );
            $rRenamed = $qry->fetch(PDB::FETCH_OBJ);
            $rr->id = $this->IdMember($rRenamed->Username); // try until a not renamde profile is found
        }
        if (isset ($rr->id)) {
            // test if the member is the current member and has just bee rejected (security trick to immediately remove the current member in such a case)
            if (array_key_exists("IdMember", $_SESSION) and $rr->id==$_SESSION["IdMember"]) $this->TestIfIsToReject($rr->Status) ;
            return ($rr->id);
        }
        return (0);
    } // end of IdMember

    // todo: remove this code. A method like this SHOULD NEVER BE PLACED IN AN APP!
    // THis TestIfIsToReject function check wether the status of the members imply an immediate logoff
    // This for the case a member has just been banned
    // the $Status of the member is the current status from the database
    private function TestIfIsToReject($Status) {
         if (($Status=='Rejected ')or($Status=='Banned')) {
            //LogStr("Force Logout GAMEOVER", "Login");
            Logout();
            die(" You can't use this site anymore") ;
         }
    } // end of funtion IsToReject

    private function FindTrad($IdTrad,$ReplaceWithBr=false) {

        $AllowedTags = "<b><i><br>";
        if ($IdTrad == "")
            return ("");

        if (isset($_SESSION['IdLanguage'])) {
             $IdLanguage=$_SESSION['IdLanguage'] ;
        }
        else {
             $IdLanguage=0 ; // by default laguange 0
        }
        // Try default language
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad AND
    IdLanguage= $IdLanguage
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try default eng
        $query = $this->dao->query(
           "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad  AND
    IdLanguage = 0
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try first language available
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad
ORDER BY id ASC
LIMIT 1
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        return ("");
    } // end of FindTrad

    private function ReplaceWithBR($ss,$ReplaceWith=false) {
            if (!$ReplaceWith) return ($ss);
            return(str_replace("\n","<br>",$ss));
    }

    //------------------------------------------------------------------------------
    // fage_value return a  the age value corresponding to date
    private function fage_value($dd) {
        $pieces = explode("-",$dd);
        if(count($pieces) != 3) return 0;
        list($year,$month,$day) = $pieces;
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff = date("d") - $day;
        if ($month_diff < 0) $year_diff--;
        elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
        return $year_diff;
    } // end of fage_value

    /**
     * returns path to a dummy picture
     * function doesn't care about params
     *
     * @param string $Gender -     gender to show
     * @param string $HideGender - whether to hide gender
     *
     * @todo   remove method and call a library instead
     * @access private
     * @return string
     */
    private function DummyPict($Gender="IDontTell", $HideGender="Yes")
    {
        return 'images/misc/empty_avatar_30_30.png';
    }

    //------------------------------------------------------------------------------
    // function LinkWithPicture build a link with picture and Username to the member profile
    // optional parameter status can be used to alter the link
    private function LinkWithPicture($Username, $ParamPhoto="", $Status = "")
    {
        $words = new MOD_words();
        $Photo=$ParamPhoto ;

        if ($Photo=="") {
            $query = $this->dao->query(
                "
SELECT SQL_CACHE
    *
FROM
    members
WHERE
    id = " . IdMember($Username)) . "
                "
            ;
            $rr = $query->fetch(PDB::FETCH_OBJ);
            $Photo = $this->DummyPict($rr->Gender,$rr->HideGender) ;
        }

        $thumb = $this->getthumb($Photo, 100, 100);
        if ($thumb === null) $thumb = "";

        if($Status == 'map_style')
            return "<a href=\"javascript:newWindow('$Username')\" title=\"" . $words->getBuffered("SeeProfileOf", $Username) .
                "\"><img class=\"framed\" style=\"float: left; margin: 4px\" src=\"". $this->bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";

        return "<a href=\"".$this->bwlink("bw/member.php?cid=$Username").
            "\" title=\"" . $words->getBuffered("SeeProfileOf", $Username) .
            "\"><img class=\"framed\" src=\"". $this->bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";
    } // end of LinkWithPicture


    // Thumbnail creator. (by markus5, Markus Hutzler 25.02.2007)
    // tested with GD Version: bundled (2.0.28 compatible)
    // with GIF Read Support: Enabled
    // with JPG Support: Enabled
    // with PNG Support: Enabled

    // this function creates a thumbnail of a JPEG, GIF or PNG image
    // file: path (with /)!!!
    // max_x / max_y delimit the maximal size. default = 100 (it keeps the ratio)
    // the quality can be set. default = 85
    // this function returns the thumb filename or null

    // modified by Fake51
    // $mode specifies if the new image is based on a cropped and resized version of the old, or just a resized
    // $mode = "square" means a cropped version
    // $mode = "ratio" means merely resized
    private function getthumb($file = "", $max_x, $max_y, $quality = 85, $thumbdir = 'thumbs',$mode = 'square')
    {
        // TODO: analyze MIME-TYPE of the input file (not try / catch)
        // TODO: error analysis of wrong paths
        // TODO: dynamic prefix (now: /th/)

        if($file == "") return null;

        $filename = basename($file);
        $filename_noext = substr($filename, 0, strrpos($filename, '.'));
        $filepath = getcwd()."/bw/memberphotos";
        if($_SERVER['HTTP_HOST'] == 'localhost')
            $wwwpath = "http://".$_SERVER['HTTP_HOST']."/bw/htdocs/bw/memberphotos";
        else
            $wwwpath = "http://".$_SERVER['HTTP_HOST']."/bw/memberphotos";

        $thumbfile = $filename_noext.'.'.$mode.'.'.$max_x.'x'.$max_y.'.jpg';

        if(is_file("$filepath/$thumbdir/$thumbfile")) return "$wwwpath/$thumbdir/$thumbfile";

        // locate file

        if (!is_file("$filepath/$filename")) return null;

        // TODO: bw_error("get_thumb: no file found");

        if(!is_dir("$filepath/$thumbdir")) return null;

        // TODO: bw_error("get_thumb: no directory found");

        ini_set("memory_limit",'64M'); //jeanyves increasing the memory these functions need a lot
        // read image
        $image = false;
        if (!$image) $image = @imagecreatefromjpeg("$filepath/$filename");
        if (!$image) $image = @imagecreatefrompng("$filepath/$filename");
        if (!$image) $image = @imagecreatefromgif("$filepath/$filename");

        if($image == false) return null;

        // calculate ratio
        $size_x = imagesx($image);
        $size_y = imagesy($image);

        if($size_x == 0 or $size_y == 0){
            bw_error("bad image size (0)");
        }

        switch($mode){
            case "ratio":
                if (($max_x / $size_x) >= ($max_y / $size_y)){
                    $ratio = $max_y / $size_y;
                } else {
                      $ratio = $max_x / $size_x;
                }
                $startx = 0;
                $starty = 0;
                break;
            default:
                if ($size_x >= $size_y){
                    $startx = ($size_x - $size_y) / 2;
                    $starty = 0;
                    $size_x = $size_y;
                } else {
                    $starty = ($size_y - $size_x) / 2;
                    $startx = 0;
                    $size_y = $size_x;
                }

                if ($max_x >= $max_y){
                    $ratio = $max_y / $size_y;
                } else {
                    $ratio = $max_x / $size_x;
                }
                break;
        }

        $th_size_x = $size_x * $ratio;
        $th_size_y = $size_y * $ratio;

        // creating thumb
        $thumb = imagecreatetruecolor($th_size_x,$th_size_y);
        imagecopyresampled($thumb,$image,0,0,$startx,$starty,$th_size_x,$th_size_y,$size_x,$size_y);

        // try to write the new image
        imagejpeg($thumb, "$filepath/$thumbdir/$thumbfile", $quality);
        return "$wwwpath/$thumbdir/$thumbfile";
    }

    //------------------------------------------------------------------------------
    // bwlink converts a relative link to an absolute link
    // It works from subdirectories too. Result is always relative
    // to the root directory of the site. Works in local environment too.
    // e.g. "" -> "http://www.bewelcome.org/"
    //      "layout/a.php" -> "http://www.bewelcome.org/layout/a.php"

    private function bwlink( $target, $useTBroot = false )
    {
        if (strlen($target) > 8)
        {
            if (substr_compare($target,"https://",0,8)==0 ||
                substr_compare($target,"http://",0,7)==0)
                return $target;
        }

        if ( $useTBroot )
            $a = PVars::getObj('env')->baseuri . $target;
        else {
            $a = "http://".$_SERVER['HTTP_HOST'];
            if($_SERVER['HTTP_HOST'] == "localhost") $a .= '/bw/htdocs/';
            else $a .= '/';
            $a .= $target;
        }
        return $a;
    }

    //------------------------------------------------------------------------------
    // function fUsername return the Username of the member according to its id
    private function fUsername($cid) {
        if (!is_numeric($cid))
            return ($cid); // If cid is not numeric it is assumed to be already a username
        if (array_key_exists("IdMember", $_SESSION) and $cid == $_SESSION["IdMember"])
            return ($_SESSION["Username"]);
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    username
FROM
    members
WHERE
    id = " . intval($cid)
        );
        $rr = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($rr->username)) {
            return ($rr->username);
        }
        return ("");
    } // end of fUsername

    // sql_get_set returns in an array the possible set values of the colum of table name
    public function sql_get_set($table, $column) {
        $query = $this->dao->query(
            "
SHOW COLUMNS
FROM $table
LIKE '$column'
            "
        );
        $line = $query->fetch(PDB::FETCH_OBJ);
        $set = $line->Type;
        $set = preg_replace("/.*\('(.*)'\).*/", "$1", $set);
        return preg_split("/','/", $set); // Split into and array
    } // end of sql_get_set($table,$column)

    /**
     * useless function that will with 100% certainty cause many problems later
     * returning a complete a table with unknown rows in it is just not a good
     * idea.
     *
     * @access public
     * @return array
     */
    public function sql_get_groups()
    {
        $groupEntity = $this->createEntity('Group');
        $groupEntity->sql_order = 'Name ASC';
        return $groupEntity->findAll();
    }

    /**
     * returns an array of columns that can be sorted by
     * as well as the word codes for them
     *
     * @access public
     * @return array
     */
    public function get_sort_order()
    {
        return $this->column_sort_order;
    }

    /**
     * returns an array of columns that can be sorted by
     * and the default direction to use
     *
     * @access public
     * @return array
     */
    public function getDefaultSortDirection()
    {
        return $this->default_sort_direction;
    }

    /**
     * return a proper direction, given order column and input
     *
     * @param string $column - name of order column
     * @param int    $bool   - 0 for default, 1 for revers
     *
     * @access private
     * @return array
     */
    public function getOrderDirection($column, $bool = 0)
    {
        $reverse = array('ASC' => 'DESC', 'DESC' => 'ASC');
        $directions = $this->getDefaultSortDirection();
        $columns = $this->get_sort_order();
        $direction = isset($directions[$column]) ? $directions[$column] : 'ASC';
        $order = isset($columns[$column]) ? $this->dao->escape($column) : $this->default_column;
        // hack to sort by number of comments
        if ($order == 'Comments')
        {
            $order = "(SELECT COUNT(id) FROM comments WHERE members.id = comments.IdToMember)";
        }
        if ($bool) $direction = $reverse[$direction];
        return array($order, $direction);
    }
}
