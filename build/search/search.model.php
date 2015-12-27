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

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;
use AnthonyMartin\GeoLocation\GeoLocation as GeoLocation;

/**
 * Search model
 *
 * @package Search
 * @author shevek
 */
class SearchModel extends RoxModelBase
{
    const SPHINX_PLACES = 1;
    const SPHINX_ADMINUNITS = 2;
    const SPHINX_COUNTRIES = 4;

    const EARTH_RADIUS = 6378;

    // const ORDER_NOSORT = 0; // Not needed as this would be the same as for MEMBERSHIP
    const ORDER_USERNAME = 2;
    const ORDER_AGE = 4;
    const ORDER_ACCOM = 6;
    const ORDER_LOGIN = 8;
    const ORDER_MEMBERSHIP = 10;
    const ORDER_COMMENTS = 12;
    const ORDER_DISTANCE = 14;

    const SUGGEST_MAX_ITEMS = 30;
    // No need to find historical and destroyed places
    const PLACES_FILTER = " g.fclass = 'P' AND g.fcode <> 'PPLH' AND g.fcode <> 'PPLW' AND g.fcode <> 'PPLQ' AND g.fcode <> 'PPLCH' ";

    private $keywordCondition = "";
    private $ageCondition = "";
    private $usernameCondition = "";
    private $genderCondition = "";
    private $locationCondition = "";
    private $groupsCondition = "";
    private $languagesCondition = "";
    private $accommodationCondition = "";
    private $typicalOfferCondition = "";
    private $tables = "";

    private static $ORDERBY = array(
        self::ORDER_USERNAME => array('WordCode' => 'SearchOrderUsername', 'Column' => 'm.Username'),
        self::ORDER_ACCOM => array('WordCode' => 'SearchOrderAccommodation', 'Column' => 'm.Accomodation'),
        self::ORDER_DISTANCE => array('WordCode' => 'SearchOrderDistance', 'Column' => 'Distance'),
        self::ORDER_LOGIN => array('WordCode' => 'SearchOrderLogin', 'Column' => 'LastLogin'),
        self::ORDER_MEMBERSHIP => array('WordCode' => 'SearchOrderMembership', 'Column' => 'm.created'),
        self::ORDER_COMMENTS => array('WordCode' => 'SearchOrderComments', 'Column' => 'CommentCount')
    );

    public static function getOrderByArray()
    {
        return self::$ORDERBY;
    }

    private function getOrderBy($orderBy)
    {
        $orderType = $orderBy - ($orderBy % 2);
        $order = self::$ORDERBY[$orderType]['Column'];
        if ($orderBy % 2 == 1) {
            $order .= " DESC";
        } else {
            $order .= " ASC";
        }
        switch ($orderType) {
            case self::ORDER_ACCOM:
            case self::ORDER_COMMENTS:
                $order .= ', Distance ASC, HasProfileSummary DESC, HasProfilePhoto DESC, LastLogin DESC';
                break;
            case self::ORDER_DISTANCE:
                $order .= ', m.Accomodation, HasProfileSummary DESC, HasProfilePhoto DESC, LastLogin DESC';
                break;
        }

        return $order;
    }

    private function ReplaceWithBR($ss, $ReplaceWith = false)
    {
        if (!$ReplaceWith) {
            return ($ss);
        }

        return (str_replace("\n", "<br>", $ss));
    }

    private function FindTrad($IdTrad, $ReplaceWithBr = false)
    {

        $AllowedTags = "<b><i><br>";
        if ($IdTrad == "") {
            return ("");
        }

        if (isset($_SESSION['IdLanguage'])) {
            $IdLanguage = $_SESSION['IdLanguage'];
        } else {
            $IdLanguage = 0; // by default laguange 0
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
                return (strip_tags($this->ReplaceWithBr($row->Sentence, $ReplaceWithBr), $AllowedTags));
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
                return (strip_tags($this->ReplaceWithBr($row->Sentence, $ReplaceWithBr), $AllowedTags));
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
                return (strip_tags($this->ReplaceWithBr($row->Sentence, $ReplaceWithBr), $AllowedTags));
            }
        }

        return ("");
    } // end of FindTrad

    private function getNamePart($namePartId)
    {
        $namePart = "";
        if ($namePartId == 0) {
            return $namePart;
        }
        if (MOD_crypt::IsCrypted($namePartId) == 1) {
        } else {
            $namePart = MOD_crypt::get_crypted($namePartId, "");
        }

        return $namePart;
    }

    /**
     * @param array $vars
     * r@return string
     */
    private function getStatusCondition($vars)
    {
        if (array_key_exists('search-membership', $vars) && ($vars['search-membership'] == 1)) {
            $statusCondition = " AND m.status IN (" . Member::ACTIVE_SEARCH . ") ";
        } else {
            $statusCondition = " AND m.status IN ( 'Active') ";
        }

        return $statusCondition;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $distance
     *
     * @return stdClass
     */
    private function _getRectangle($latitude, $longitude, $distance) {

        $edison = GeoLocation::fromDegrees($latitude, $longitude);
        $coordinates = $edison->boundingCoordinates($distance, 'km');

        $result = new stdClass;
        $result->latne = $coordinates[0]->getLatitudeInDegrees();
        $result->longne = $coordinates[0]->getLongitudeInDegrees();
        $result->latsw = $coordinates[1]->getLatitudeInDegrees();
        $result->longsw = $coordinates[1]->getLongitudeInDegrees();
        return $result;
    }

    /**
     *
     * @param array $vars
     * @param string $admin1
     * @param string $country
     * @return string
     */
    private function getLocationCondition($vars, $admin1, $country)
    {
        if ($country) {
            if ($admin1) {
                // We run based on an admin unit
                $condition = "AND a.IdCity = g.geonameid
                AND g.admin1 = '" . $admin1 . "'
                AND g.country = '" . $country . "'";
            } else {
                // we're looking for all members of a country
                $condition = "AND a.IdCity = g.geonameid
                AND g.country = '" . $country . "'";
            }
        } else {
            $condition = "AND a.IdCity = g.geonameid";
            if (empty($vars['location'])) {
                // we search around the world.
            } else {
                // a simple place with a square rectangle around it
                $distance = $vars['search-distance'];
                if ($distance != 0) {
                    // calculate rectangle around place with given distance
                    $lat = deg2rad(doubleval($vars['location-latitude']));
                    $long = deg2rad(doubleval($vars['location-longitude']));

                    $longne = rad2deg(($distance + self::EARTH_RADIUS * $long) / self::EARTH_RADIUS);
                    $longsw = rad2deg((self::EARTH_RADIUS * $long - $distance) / self::EARTH_RADIUS);

                    $radiusAtLatitude = cos($lat) * self::EARTH_RADIUS;
                    $latne = rad2deg(($distance + $radiusAtLatitude * $lat) / $radiusAtLatitude);
                    $latsw = rad2deg(($radiusAtLatitude * $lat - $distance) / $radiusAtLatitude);
                    // Sanity check if $latne < $latsw or $longne < $longsw switch the two (Melbourne)
                    // TODO: search around the date line
                    if ($latne < $latsw) {
                        $tmp = $latne;
                        $latne = $latsw;
                        $latsw = $tmp;
                    }
                    if ($longne < $longsw) {
                        $tmp = $longne;
                        $longne = $longsw;
                        $longsw = $tmp;
                    }
                    // now fetch all location from geonames which are in that given rectangle
                    $query = "
                        SELECT
                            g.geonameid AS geonameid
                        FROM
                            geonames g
                        WHERE
                            " . self::PLACES_FILTER . "
                            AND g.latitude BETWEEN " . $latsw . " AND " . $latne . "
                            AND g.longitude BETWEEN " . $longsw . " AND " . $longne;
                    $condition .= "
                            AND g.geonameid IN ('";
                    $geonameids = $this->bulkLookup($query);
                    foreach ($geonameids as $geonameid) {
                        $condition .= $geonameid->geonameid . "', '";
                    }
                    $condition = substr($condition, 0, -3) . ")";
                } else {
                    $condition .= " AND g.geonameid = " . $this->dao->escape($vars['location-geoname-id']);
                }
            }
        }

        return $condition;
    }

    private function getGenderCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-gender'])) {
            $gender = $vars['search-gender'];
            switch ($gender) {
                case "male":
                case "female":
                    $condition = " AND m.Gender = '" . $gender . "' AND m.HideGender = 'No'";
                    break;
                case "genderOther":
                    $condition = " AND m.Gender = 'other' AND m.HideGender = 'No'";
                    break;
            }
        }

        return $condition;
    }

    private function getAgeCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-age-minimum']) && ($vars['search-age-minimum'] != 0)) {
            $minAge = $vars['search-age-minimum'];
            $condition .= ' AND m.BirthDate <= (NOW() - INTERVAL ' . $minAge . ' YEAR)';
        }
        if (isset($vars['search-age-maximum']) && ($vars['search-age-maximum'] != 0)) {
            $maxAge = $vars['search-age-maximum'];
            $condition .= ' AND m.BirthDate >= (NOW() - INTERVAL ' . $maxAge . ' YEAR)';
        }
        if (!empty($condition)) {
            $condition .= " AND m.HideBirthDate='No'";
        }

        return $condition;
    }

    private function getUsernameCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-username']) && (!empty($vars['search-username']))) {
            $username = $vars['search-username'];
            // create LIKE condition from query parameter with wildcards
            if (strpos($username, "*") !== false) {
                $username = str_replace("*", "%", $username);
            }
            $condition = " AND m.username LIKE '" . $this->dao->escape($username) . "'";
        }

        return $condition;
    }

    private function getKeywordCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-text']) && (!empty($vars['search-text']))) {
            $condition = "AND mt.Sentence LIKE '%" . $this->dao->escape(
                    $vars['search-text']
                ) . "%' AND mt.IdOwner = m.id";
        }

        return $condition;
    }

    private function getGroupsCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-groups'])) {
            $groups = array();
            foreach ($vars['search-groups'] as $group) {
                $groups[] = $group;
            }
            if (!empty($groups)) {
                $condition = " AND mg.IdMember = m.id AND mg.IdGroup IN ('" . implode("', '", $groups) . "')";
            }
        }

        return $condition;
    }

    private function getLanguagesCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-languages'])) {
            $languages = array();
            foreach ($vars['search-languages'] as $language) {
                $languages[] = $language;
            }
            if (!empty($languages)) {
                $condition = " AND mll.IdMember = m.id AND mll.IdLanguage IN ('" . implode("', '", $languages) . "')
                    AND mll.Level <> 'HelloOnly'";
            }
        }

        return $condition;
    }

    private function getAccommodationCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-accommodation'])) {
            $accommodations = array();
            $accommodation = $vars['search-accommodation'];
            if (is_array($accommodation)) {
                foreach ($accommodation as $value) {
                    if ($value == '') {
                        continue;
                    }
                    $accommodations[] = "Accomodation = '" . $this->dao->escape($value) . "'";
                }
            }
            if (!empty($accommodations)) {
                $condition = " AND (" . implode(" OR ", $accommodations) . ")";
            }
        }

        return $condition;
    }

    private function getTypicalOfferCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-typical-offer'])) {
            $typicalOffers = array();
            $typicalOffer = $vars['search-typical-offer'];
            if (is_array($typicalOffer)) {
                foreach ($typicalOffer as $value) {
                    if ($value == '') {
                        continue;
                    }
                    $typicalOffers[] = " FIND_IN_SET('" . $this->dao->escape($value) . "', TypicOffer)";
                }
            }
            if (!empty($typicalOffers)) {
                $condition = " AND ( " . implode(" AND ", $typicalOffers) . ") ";
            }
        }

        return $condition;
    }

    private function getMembersCount($publicOnly)
    {
        // Fetch count of public members at/around the given place
        $str = "
            SELECT
                COUNT( DISTINCT m.id) cnt
            FROM
            " . $this->tables;
        if ($publicOnly) {
            $str .= ",
                memberspublicprofiles mpp";
        }
        $str .= "
            WHERE
                m.id = a.IdMember
                " . $this->maxGuestCondition . "
                " . $this->statusCondition;
        if ($publicOnly) {
            $str .= "
                AND m.id = mpp.IdMember ";
        }
        $str .= $this->locationCondition . "
            " . $this->genderCondition . "
            " . $this->ageCondition . "
            " . $this->usernameCondition . "
            " . $this->keywordCondition . "
            " . $this->groupsCondition . "
            " . $this->languagesCondition . "
            " . $this->accommodationCondition . "
            " . $this->typicalOfferCondition;
        $count = $this->dao->query($str);

        $row = $count->fetch(PDB::FETCH_OBJ);

        return $row->cnt;
    }

    private function getParameter($vars, $var, $default)
    {
        $result = $default;
        if (isset($vars[$var])) {
            $result = $vars[$var];
        }

        return $result;
    }

    /**
     *
     * @param array $vars
     * @param string $admin1
     * @param string $country
     * @return multitype:unknown
     */
    private function getMemberDetails(&$vars, $admin1 = false, $country = false)
    {
        $langarr = explode('-', $_SESSION['lang']);
        $lang = $langarr[0];
        // First get current page and limits
        $limit = $this->getParameter($vars, 'search-number-items', 10);
        $pageno = $this->getParameter($vars, 'search-page', 1);
        $start = ($pageno - 1) * $limit;

        $this->statusCondition = $this->getStatusCondition($vars);
        $this->maxGuestCondition = "AND m.MaxGuest >= " . $vars['search-can-host'];
        $this->locationCondition = $this->getLocationCondition($vars, $admin1, $country);
        $this->genderCondition = $this->getGenderCondition($vars);
        $this->ageCondition = $this->getAgeCondition($vars);
        $this->usernameCondition = $this->getUsernameCondition($vars);
        $this->keywordCondition = $this->getKeywordCondition($vars);
        $this->groupsCondition = $this->getGroupsCondition($vars);
        $this->languagesCondition = $this->getLanguagesCondition($vars);
        $this->typicalOfferCondition = $this->getTypicalOfferCondition($vars);
        $this->accommodationCondition = $this->getAccommodationCondition($vars);

        $this->tables = "addresses a,
                geonames g";
        if (!empty($this->keywordCondition)) {
            $this->tables .= ", memberstrads mt";
        }
        if (!empty($this->groupsCondition)) {
            $this->tables .= ", membersgroups mg";
        }
        if (!empty($this->languagesCondition)) {
            $this->tables .= ", memberslanguageslevel mll";
        }
        $this->tables .= ", members m";

        // Fetch count of members at/around the given place
        $vars['countOfMembers'] = $this->getMembersCount(false);
        $vars['countOfPublicMembers'] = $this->getMembersCount(true);

        // *FROM* and *WHERE* will be replaced later on (don't change)
        $str = "
            SELECT DISTINCT
                m.id,
                m.Username,
                m.created,
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.TypicOffer,
                m.Restrictions,
                m.ProfileSummary,
                m.Occupation,
                m.Gender,
                m.HideGender,
                m.MaxGuest,
                m.FirstName,
                m.SecondName,
                m.LastName,
                date_format(m.LastLogin,'%Y-%m-%d') AS LastLogin,
                IF(m.ProfileSummary != 0, 1, 0) AS HasProfileSummary,
                IF(mp.photoCount IS NULL, 0, 1) AS HasProfilePhoto,
                g.geonameid,
                g.country,
                g.latitude,
                g.longitude,
                ((g.latitude - " . $vars['location-latitude'] . ") * (g.latitude - " . $vars['location-latitude'] . ") +
                        (g.longitude - " . $vars['location-longitude'] . ") * (g.longitude - " . $vars['location-longitude'] . "))  AS Distance,
                IF(c.IdToMember IS NULL, 0, c.commentCount) AS CommentCount
            *FROM*
                " . $this->tables . "
            LEFT JOIN (
                SELECT
                    COUNT(*) As commentCount, IdToMember
                FROM
                    comments, members m2
                WHERE
                    IdFromMember = m2.id
                    AND m2.Status IN ('Active', 'OutOfRemind')
                GROUP BY
                    IdToMember ) c
            ON
                c.IdToMember = m.id
            LEFT JOIN (
                SELECT
                    COUNT(*) As photoCount, IdMember
                FROM
                    membersphotos
                GROUP BY
                    IdMember) mp
            ON
                mp.IdMember = m.id
            *WHERE*
                m.id = a.idmember
                " . $this->maxGuestCondition . "
                " . $this->statusCondition . "
                " . $this->locationCondition . "
                " . $this->genderCondition . "
                " . $this->ageCondition . "
                " . $this->usernameCondition . "
                " . $this->keywordCondition . "
                " . $this->groupsCondition . "
                " . $this->languagesCondition . "
                " . $this->accommodationCondition . "
                " . $this->typicalOfferCondition . "
            ORDER BY
                " . $this->getOrderBy($vars['search-sort-order']) . "
            LIMIT
                " . $start . ", " . $limit;

        // Make sure only public profiles are found if no one's logged in
        if (!$this->getLoggedInMember()) {
            $str = str_replace('*FROM*', 'FROM memberspublicprofiles mpp,', $str);
            $str = str_replace('*WHERE*', 'WHERE m.id = mpp.IdMember AND ', $str);
        }
        $str = str_replace('*FROM*', 'FROM', $str);
        $str = str_replace('*WHERE*', 'WHERE', $str);

        $rawMembers = $this->bulkLookup($str);

        $loggedInMember = $this->getLoggedInMember();

        $members = array();
        $geonameIds = array();
        $countryIds = array();
        $layoutBits = new MOD_layoutbits();
        foreach ($rawMembers as $member) {
            $geonameIds[$member->geonameid] = $member->geonameid;
            $countryIds[$member->country] = $member->country;
            $aboutMe = MOD_layoutbits::truncate_words($this->FindTrad($member->ProfileSummary, true), 70);
            $FirstName = $this->getNamePart($member->FirstName);
            $SecondName = $this->getNamePart($member->SecondName);
            $LastName = $this->getNamePart($member->LastName);
            $member->Name = trim($FirstName . " " . $SecondName . " " . $LastName);
            $member->ProfileSummary = $aboutMe;

            if ($member->HideBirthDate == "No") {
                $member->Age = floor($layoutBits->fage_value($member->BirthDate));
            } else {
                $member->Age = "";
            }
            if ($member->HideGender != "Yes") {
                $member->GenderString = MOD_layoutbits::getGenderTranslated($member->Gender, false, false);
            }
            $member->Occupation = MOD_layoutbits::truncate_words($this->FindTrad($member->Occupation), 10);

            if ($loggedInMember) {
                // get message count for found member with current member
                $query = "
                    SELECT
                        COUNT(*) cnt
                    FROM
                        `messages`
                    WHERE
                        (IdSender = " . $member->id . " OR IdReceiver = " . $member->id . ")
                        AND (IdSender = " . $loggedInMember->id . " OR IdReceiver = " . $loggedInMember->id . ")";
                $messageCount = $this->singleLookup($query);
                $member->MessageCount = $messageCount->cnt;
            } else {
                $member->MessageCount = 0;
            }
            $members[] = $member;
        }
        $inGeonameIds = implode("', '", $geonameIds);
        $query = "
            SELECT
                g.geonameid geonameid, a.alternatename name, a.ispreferred ispreferred, a.isshort isshort, 'alternate' source
            FROM
                geonames g, geonamesalternatenames a
            WHERE
                g.geonameid IN ('" . $inGeonameIds . "') AND g.geonameid = a.geonameid AND a.isoLanguage = '" . $lang . "'
            UNION SELECT
                g.geonameid geonameid, g.name name, 0 ispreferred, 0 isshort, 'geoname' source
            FROM
                geonames g
            WHERE
                g.geonameid IN ('" . $inGeonameIds . "')
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC";
        $rawNames = $this->bulkLookup($query);
        $names = array();
        foreach ($rawNames as $rawName) {
            if (!isset($names[$rawName->geonameid])) {
                $names[$rawName->geonameid] = $rawName->name;
            }
        }
        $inCountries = implode("', '", $countryIds);
        // fetch country names, prefer alternate names (preferred, short) over geonames entry
        $query = "
            SELECT
                c.geonameid geonameid, c.country countryCode, a.alternatename country, a.ispreferred ispreferred, a.isshort isshort, 'alternate' source
            FROM
                geonamescountries c, geonamesalternatenames a
            WHERE
                c.country IN ('" . $inCountries . "') AND c.geonameid = a.geonameid AND a.isoLanguage = '" . $lang . "'
            UNION SELECT
                c.geonameid geonameid, c.country countryCode, c.name country, 0 ispreferred, 0 isshort, 'geoname' source
            FROM
                geonamescountries c
            WHERE
                c.country IN ('" . $inCountries . "')
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC";
        $countryRawNames = $this->bulkLookup($query);
        $countryNames = array();
        foreach ($countryRawNames as $countryRawName) {
            if (!isset($countryNames[$countryRawName->countryCode])) {
                $countryNames[$countryRawName->countryCode] = $countryRawName->country;
            }
        }
        foreach ($members as &$member) {
            $member->CityName = $names[$member->geonameid];
            $member->CountryName = $countryNames[$member->country];
        }

        return $members;
    }

    private function getPlacesFromDatabase($ids)
    {
        $query = "
            SELECT
                g.geonameid AS geonameid, g.name AS name, g.latitude AS latitude, g.longitude AS longitude,
                a.name AS admin1, c.name AS country, IF(m.id IS NULL, 0, COUNT(g.geonameid)) AS cnt, '"
            . $this->getWords()->getSilent('SearchPlaces') . "' AS category
            FROM
                geonames g
            LEFT JOIN
                geonamescountries c
            ON
                g.country = c.country
            LEFT JOIN
                geonamesadminunits a
            ON
                g.country = a.country
                AND g.admin1 = a.admin1
                AND a.fclass = 'A'
                AND a.fcode = 'ADM1'
            LEFT JOIN
                members m
            ON
                g.geonameid = m.IdCity
                AND m.Status = 'Active'
                AND m.MaxGuest >= 1
            WHERE
                g.geonameid in ('" . implode("','", $ids) . "')
            GROUP BY
                g.geonameid
            ORDER BY
                cnt DESC, country, admin1";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function getFromDataBase($ids, $category = "")
    {
        // get country names for found ids
        $query = "
            SELECT
                a.geonameid AS geonameid, a.latitude AS latitude, a.longitude AS longitude, a.name AS admin1, c.name AS country, 0 AS cnt, '"
            . $this->dao->escape($category) . "' AS category
            FROM
                geonames a
            LEFT JOIN
                geonamescountries c
            ON
                a.country = c.country
            WHERE
                a.geonameid in ('" . implode("','", $ids) . "')
            ORDER BY
                a.population DESC";
        $sql = $this->dao->query($query);
        if (!$sql) {
            return array();
        }
        $rows = array();
        while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function getCountryNames($countryIds, $lang)
    {
        $inCountries = implode("', '", $countryIds);
        // fetch country names, prefer alternate names (preferred, short) over geonames entry
        $query = "
            SELECT
                c.geonameid geonameid, c.country countryCode, a.alternatename country, a.ispreferred ispreferred, a.isshort isshort, 'alternate' source
            FROM
                geonamescountries c, geonamesalternatenames a
            WHERE
                c.country IN ('" . $inCountries . "') AND c.geonameid = a.geonameid AND a.isoLanguage = '" . $lang . "'
            UNION SELECT
                c.geonameid geonameid, c.country countryCode, c.name country, 0 ispreferred, 0 isshort, 'geoname' source
            FROM
                geonamescountries c
            WHERE
                c.country IN ('" . $inCountries . "')
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC";
        $countryRawNames = $this->bulkLookup($query);
        $countryNames = array();
        foreach ($countryRawNames as $countryRawName) {
            if (!isset($countryNames[$countryRawName->countryCode])) {
                $data = new StdClass;
                $data->country = $countryRawName->country;
                $data->code = $countryRawName->countryCode;
                $countryNames[$countryRawName->countryCode] = $data;
            }
        }
        asort($countryNames);

        return $countryNames;
    }

    private function getAdminUnitNames($admin1Ids, $countryIds, $lang)
    {
        // fetch admin units, prefer alternate names (preferred, short) over geonames entry
        // just fetch all for the given countries short out which are needed later
        $inCountries = implode("', '", $countryIds);
        $inAdminUnits = implode("', '", $admin1Ids);
        $query = "
            SELECT
                a.geonameid geonameid, an.alternatename name, a.admin1 admin1Code, a.country country, an.ispreferred ispreferred, an.isshort isshort, 'alternate' source
            FROM
                geonamesadminunits a , geonamesalternatenames an
            WHERE
                a.country IN ('" . $inCountries . "') AND a.admin1 IN ('" . $inAdminUnits . "') AND a.fcode = 'ADM1' AND a.geonameid = an.geonameid AND an.isoLanguage = '" . $lang . "'
            UNION SELECT
                a.geonameid geonameid, a.name name, a.admin1 admin1Code, a.country country, 0 ispreferred, 0 isshort, 'geoname' source
            FROM
                geonamesadminunits a
            WHERE
                a.country IN ('" . $inCountries . "') AND a.admin1 IN ('" . $inAdminUnits . "') AND a.fcode = 'ADM1'
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC";
        $admin1Names = array();
        $admin1RawNames = $this->bulkLookup($query);
        foreach ($admin1RawNames as $admin1RawName) {
            if (!isset($admin1Names[$admin1RawName->country])) {
                $admin1Names[$admin1RawName->country] = array();
            }
            if (!isset($admin1Names[$admin1RawName->country][$admin1RawName->admin1Code])) {
                $data = new StdClass;
                $data->admin1 = $admin1RawName->name;
                $admin1Names[$admin1RawName->country][$admin1RawName->admin1Code] = $data;
            }
        }

        return $admin1Names;
    }

    private function getPlaces($place, $admin1 = false, $country = false, $limit = false)
    {
        $langarr = explode('-', $_SESSION['lang']);
        $lang = $langarr[0];
        $constraint = "";
        if ($country && count($country) > 0) {
            $constraint .= " AND g.country IN ('" . implode("', '", $country) . "')";
            if ($admin1 && count($admin1) > 0) {
                $constraint .= " AND g.admin1 IN ('" . implode("', '", $admin1) . "')";
            }
        }
        $query = "
            SELECT
                COUNT(m.idCity) cnt, geo.*
            FROM (
                SELECT
                    a.geonameid geonameid, g.latitude, g.longitude, g.admin1, g.country, '" . $this->getWords(
            )->getSilent('SearchPlaces') . "' category
                FROM
                    geonamesalternatenames a, geonames g
                WHERE
                    a.alternatename like '" . $this->dao->escape($place) . (strlen($place) >= 3 ? "%" : "") . "'
                    AND a.geonameid = g.geonameid AND " . self::PLACES_FILTER . $constraint . "
                UNION SELECT
                    g.geonameid geonameid, g.latitude, g.longitude, g.admin1, g.country, '" . $this->getWords(
            )->getSilent('SearchPlaces') . "' category
                FROM
                    geonames g
                WHERE
                    g.name like '" . $this->dao->escape($place) . (strlen($place) >= 3 ? "%" : "") . "' AND "
            . self::PLACES_FILTER . $constraint . "
            ) geo
            LEFT JOIN
                members m
            ON
                m.IdCity = geo.geonameid
                AND m.Status = 'Active'
                AND m.MaxGuest >= 1
            GROUP BY
                geonameid
            ORDER BY
                cnt DESC";
        if ($limit) {
            $query .= " LIMIT 0, " . $limit;
        }

        $places = $this->bulkLookup($query);
        // Now fetch admin units and country name for the found entities
        // shevek: I tried to combine this into one query but search time exploded so separate step (for now?)
        $adminUnits = array();
        $countries = array();
        $geonameIds = array();
        foreach ($places as $place) {
            $adminUnits[$place->country . "-" . $place->admin1] = $place->admin1;
            $countries[$place->country] = $place->country;
            $geonameIds[$place->geonameid] = $place->geonameid;
        }
        $countryNames = $this->getCountryNames($countries, $lang);
        $admin1Names = $this->getAdminUnitNames($adminUnits, $countries, $lang);

        // And finally get the place names in the UI language
        $inGeonameIds = implode("', '", $geonameIds);
        $query = "
            SELECT
                g.geonameid geonameid, a.alternatename name, a.ispreferred ispreferred, a.isshort isshort, 'alternate' source
            FROM
                geonames g, geonamesalternatenames a
            WHERE
                g.geonameid IN ('" . $inGeonameIds . "') AND g.geonameid = a.geonameid AND a.isoLanguage = '" . $lang . "'
            UNION SELECT
                g.geonameid geonameid, g.name name, 0 ispreferred, 0 isshort, 'geoname' source
            FROM
                geonames g
            WHERE
                g.geonameid IN ('" . $inGeonameIds . "')
            ORDER BY
                geonameid, source, ispreferred DESC, isshort DESC";
        $rawNames = $this->bulkLookup($query);
        $names = array();
        foreach ($rawNames as $rawName) {
            if (!isset($names[$rawName->geonameid])) {
                $names[$rawName->geonameid] = $rawName->name;
            }
        }
        foreach ($places as &$place) {
            // sequence is key here as $place->country (isocode) will be replaced with country name in the second statement
            if (isset($admin1Names[$place->country][$place->admin1])) {
                $place->admin1 = $admin1Names[$place->country][$place->admin1]->admin1;
            } else {
                unset($place->admin1);
            }
            $place->country = $countryNames[$place->country]->country;
            $place->name = $names[$place->geonameid];
        }

        return $places;
    }

    /*
     * Fetches the country codes for a given country name (partial matching)
     *
     * If admin1 is not empty it also fetches the matching admin units
     *
     * No filtering of country codes is done based on admin1 (meaning the result will be broader than needed)
     */
    private function getIdsForCountriesAndAdminUnits($country, $admin1)
    {
        $countryIds = array();
        if (!empty($country)) {
            $query = "
                SELECT
                    c.country
                FROM
                    geonamesalternatenames a, geonamescountries c
                WHERE
                    a.alternatename LIKE '" . $this->dao->escape($country) . "%' AND a.geonameid = c.geonameid
                UNION SELECT
                    c.country
                FROM
                    geonamescountries c
                WHERE
                    c.name LIKE '" . $this->dao->escape($country) . "%'";
            $countryIds = $this->bulkLookup_assoc($query);
            $countryIds = array_map(
                function ($a) {
                    return array_pop($a);
                },
                $countryIds
            );
        }
        // if admin1 is given fetch the admin1's codes based on the given countries
        $admin1Ids = array();
        if ((count($countryIds) > 0) && (!empty($admin1))) {
            $query = "
                SELECT
                    a.admin1
                FROM
                    geonamesalternatenames an, geonamesadminunits a
                WHERE
                    an.alternatename LIKE '" . $this->dao->escape($admin1) . "%' AND an.geonameid = a.geonameid AND a.fcode = 'ADM1'
                    AND a.country IN ('" . implode("', '", $countryIds) . "')
                UNION SELECT
                    a.admin1
                FROM
                    geonamesadminunits a
                WHERE
                    a.fcode = 'ADM1' AND a.name LIKE '" . $this->dao->escape($admin1) . "%'
                    AND a.country IN ('" . implode("', '", $countryIds) . "')";
            $admin1Ids = $this->bulkLookup_assoc($query);
            $admin1Ids = array_map(
                function ($a) {
                    return array_pop($a);
                },
                $admin1Ids
            );
        }

        return array($countryIds, $admin1Ids);
    }

    /**
     * Returns an array with the default settings for the advanced options.
     */
    public function getDefaultAdvancedOptions()
    {
        $vars = array();
        $vars['search-username'] = '';
        $vars['search-text'] = '';
        $vars['search-age-minimum'] = 0;
        $vars['search-age-maximum'] = 0;
        $vars['search-gender'] = 0;
        $vars['search-groups'] = array();
        $vars['search-accommodation'] = array('anytime', 'dependonrequest', 'neverask');
        $vars['search-typical-offer'] = array();
        $vars['search-text'] = '';
        $vars['search-membership'] = 0;
        $vars['search-languages'] = array();
        $vars['member'] = $this->getLoggedInMember();

        return $vars;
    }

    /**
     * Returns an array with the default settings for the advanced options.
     */
    public function getDefaultSimpleOptions()
    {
        $vars = array();
        $vars['location'] = '';
        $vars['search-can-host'] = 1;
        $vars['search-distance'] = 25;
        $vars['location-geoname-id'] = 0;
        $vars['location-latitude'] = 0;
        $vars['location-longitude'] = 0;
        $vars['search-number-items'] = 10;
        $vars['search-sort-order'] = SearchModel::ORDER_ACCOM;
        $vars['search-page'] = 1;

        return $vars;
    }

    /**
     *
     */
    public function checkSearchVarsOk($vars)
    {
        $errors = array();
        if (empty($vars['location'])) {
            $errors[] = 'SearchLocationEmpty';
        }

        return $errors;
    }

    /*
     * Returns either a list of members for a selected location or
    * a list of possible locations based on the input text
    */
    public function getResultsForLocation(&$vars)
    {
        // first we need to check if someone clicked on one of the suggestions buttons
        $geonameid = 0;
        foreach (array_keys($vars) as $key) {
            if (strstr($key, 'geonameid-') !== false) {
                $geonameid = str_replace('geonameid-', '', $key);
            }
        }
        if ($geonameid != 0) {
            $vars['location-geoname-id'] = $geonameid;
            // We need longitude and latitude for the search so let's fetch that
            $query = "SELECT * FROM geonames g WHERE g.geonameid = " . $geonameid;
            $rowGeonameId = $this->singleLookup($query);
            $vars['location-latitude'] = $rowGeonameId->latitude;
            $vars['location-longitude'] = $rowGeonameId->longitude;
            // Now collect admin1 (if any) and country information to set search location correctly
            // Additionally we need to set the admin1 unit and the country for the given geonameid
            $query = "
                SELECT
                    g.name name, ";
            if (!empty($rowGeonameId->admin1)) {
                $query .= "a.name admin1, ";
            }
            $query .= "c.name country
                FROM
                    geonames g,";
            if (!empty($rowGeonameId->admin1)) {
                $query .= " geonamesadminunits a, ";
            }
            $query .= " geonamescountries c
                WHERE
                    g.geonameid = " . $geonameid;
            if (!empty($rowGeonameId->admin1)) {
                $query .= " AND g.admin1 = a.admin1
                    AND g.country = a.country
                    AND a.fcode = 'ADM1' ";
            }
            $query .= "
                    AND g.country = c.country";
            $row = $this->singleLookup($query);
            $searchLocation = $row->name . ", ";
            if (!empty($rowGeonameId->admin1)) {
                $searchLocation .= $row->admin1 . ", ";
            }
            $searchLocation .= $row->country;
            $vars['location'] = $searchLocation;
        }
        $country = $admin1 = "";
        $countryCode = $admin1Code = "";
        foreach ($vars as $key => $value) {
            if (strstr($key, 'country-') !== false) {
                $countryCode = str_replace('country-', '', $key);
                $country = $value;
            }
            if (strstr($key, 'admin1-') !== false) {
                $admin1Code = str_replace('admin1-', '', $key);
                $admin1 = $value;
            }
        }
        if (!empty($country)) {
            $vars['search-location'] = $vars['search-location'] . ", " . $country;
        }
        if (!empty($admin1)) {
            $locationParts = explode(",", $vars['search-location']);
            $vars['search-location'] = $locationParts[0] . ", " . $admin1 . ", " . $locationParts[1];
        }
        $results = array();
        $geonameid = $vars['location-geoname-id'];
        if ($geonameid == 0) {
            if (empty($vars['location'])) {
                // Search all over the world
                $results['type'] = 'members';
                $results['members'] = $this->getMemberDetails($vars);
            } else {
                // User didn't select from the suggestion list (javascript might be disabled)
                // get suggestions directly from the database
                $res = $this->suggestLocationsFromDatabase($vars['search-location']);
                if ($res["status"] == "success") {
                    if (count($res["locations"]) == 1) {
                        // found exactly one location get members for this one and return them
                        // todo
                        return $res;
                    } else {
                        return $res;
                    }
                }
            }
        } else {
            // we have a geoname id.
            // Let's check if it is an admin unit
            $query = "SELECT * FROM geonames WHERE geonameid = " . $geonameid;
            $location = $this->singleLookup($query);
            if ($location->fclass == 'A') {
                // check if found unit is a country
                if (strstr($location->fcode, 'PCL') === false) {
                    $results['type'] = 'members';
                    $results['members'] = $this->getMemberDetails(
                        $vars,
                        $location->admin1,
                        $location->country
                    );
                } else {
                    // get all members of that country
                    $results['type'] = 'members';
                    $results['members'] = $this->getMemberDetails(
                        $vars,
                        false,
                        $location->country
                    );
                }
            } else {
                // just get all active members from that place
                $results['type'] = 'members';
                $results['members'] = $this->getMemberDetails($vars);
                $results['map'] = $this->_getMembersLowDetails($vars['location-latitude'], $vars['location-longitude'], $vars['search-distance'], $vars['search-can-host']);
            }
        }
        $results['countOfMembers'] = $vars['countOfMembers'];
        $results['countOfPublicMembers'] = $vars['countOfPublicMembers'];

        return $results;
    }

    private function getAdmin1UnitIdsForPlace($place, $countryIds)
    {
        $query = "
            SELECT
                 g.admin1
            FROM
                geonamesalternatenames a, geonames g
            WHERE
                a.alternatename LIKE '" . $this->dao->escape($place) . "%' AND a.geonameid = g.geonameid
                AND g.country IN ('" . implode("', '", $countryIds) . "')
            UNION SELECT
                g.admin1
            FROM
                geonames g
            WHERE
                g.name LIKE '" . $this->dao->escape($place) . "%'
                AND g.country IN ('" . implode("', '", $countryIds) . "')";
        $temp = $this->bulkLookup_assoc($query);

        return array_map(
            function ($a) {
                return array_pop($a);
            },
            $temp
        );
    }

    private function getCountryIdsForPlace($place)
    {
        $query = "
            SELECT
                g.country AS country
            FROM
                geonames g
            WHERE
                g.name LIKE '" . $this->dao->escape($place) . "%' AND "
            . self::PLACES_FILTER . "
            UNION SELECT
                g.country AS country
            FROM
                geonames g,
                geonamesalternatenames a
            WHERE
                a.alternatename LIKE '" . $this->dao->escape($place) . "%'
                AND a.geonameid = g.geonameid AND "
            . self::PLACES_FILTER . "
            ORDER BY
                country";
        $temp = $this->bulkLookup_assoc($query);

        return array_map(
            function ($a) {
                return array_pop($a);
            },
            $temp
        );
    }

    /*
     * Used when the user either has JavaScript disabled or just typed something and hit enter
     *
     * Assume that the format is location[, [admin1, ]country]
     *
     * Returns only places (can therefore be used by setlocation as well).
     * The result will depend on the number of found places.
     *
     * If the number of results is higher than 30 instead of the places a list of countries for the matching places
     * is returned. From this the user should select one or type it into the search box.
     *
     * If the number of results with a country given is still higher than 30 a list of matching admin units is provided
     * in the same fashion.
     *
     * The function doesn't return members. It is up to the callee to deal with the results
     */
    public function suggestLocationsFromDatabase($location)
    {
        $langarr = explode('-', $_SESSION['lang']);
        $lang = $langarr[0];

        $result = array();
        // first split $location so that we know if we need to search in countries and/or adminunits as well
        $admin1 = $country = "";
        $locationParts = explode(',', $location);
        $place = trim($locationParts[0]);
        switch (count($locationParts)) {
            case 3:
                $admin1 = trim($locationParts[1]);
                $country = trim($locationParts[2]);
                break;
            case 2:
                $country = trim($locationParts[1]);
                break;
        }
        $result['status'] = 'failed';
        // fetch ids for countries and admin units
        list($countryIds, $admin1Ids) = $this->getIdsForCountriesAndAdminUnits($country, $admin1);
        $query = "
            SELECT COUNT(*) cnt FROM (
            SELECT
                g.geonameid
            FROM
                geonames g
            WHERE
                g.name LIKE '" . $this->dao->escape($place);
        if (strlen($place) >= 3) {
            $query .= "%";
        }
        $query .= "'
                AND " . self::PLACES_FILTER;
        if (count($countryIds) > 0) {
            $query .= " AND g.country IN ('" . implode("', '", $countryIds) . "') ";
            if (count($admin1Ids) > 0) {
                $query .= " AND g.admin1 IN ('" . implode("', '", $admin1Ids) . "') ";
            }
        }
        $query .= "UNION SELECT
                g.geonameid
            FROM
                geonames g,
                geonamesalternatenames a
            WHERE
                a.alternatename LIKE '" . $this->dao->escape($place);
        if (strlen($place) >= 3) {
            $query .= "%";
        }
        $query .= "'
                AND a.geonameid = g.geonameid
                AND " . self::PLACES_FILTER;
        if (count($countryIds) > 0) {
            $query .= " AND g.country IN ('" . implode("', '", $countryIds) . "') ";
            if (count($admin1Ids) > 0) {
                $query .= " AND g.admin1 IN ('" . implode("', '", $admin1Ids) . "') ";
            }
        }
        $query .= ") geo";
        $row = $this->singleLookup($query);
        $count = $row->cnt;
        if ($count > self::SUGGEST_MAX_ITEMS) {
            if (empty($country)) {
                // get countries for matching places
                $countryIds = $this->getCountryIdsForPlace($place);
                $locations = $this->getCountryNames($countryIds, $lang);
                $result['type'] = 'countries';
            } else {
                // get admin units for matching places in the given country
                $admin1Ids = $this->getAdmin1UnitIdsForPlace($place, $countryIds);
                $locations = array_pop($this->getAdminUnitNames($admin1Ids, $countryIds, $lang));
                $result['type'] = 'admin1s';
            }
            $result['biggest'] = $this->getPlaces($place, $admin1Ids, $countryIds, 3);
        } else {
            $locations = $this->getPlaces($place, $admin1Ids, $countryIds);
            $result['type'] = 'places';
        }
        $result['status'] = 'success';
        $result['locations'] = $locations;
        $result['count'] = count($locations);

        return $result;
    }

    private function sphinxSearch($location, $type, $count = false)
    {
        $conn = new Connection();
        $conn->setParams(array('host' => 'localhost', 'port' => 9306));

        $query = SphinxQL::create($conn)
            ->select('*')
            ->from('geonames')
            ->match('', "*" . $location . "*");

        switch($type) {
            case self::SPHINX_PLACES:
                $query->where('isplace', '=', 1);
                $query->limit(0, 5);
                $query->orderBy('membercount', 'desc');
                break;
            case self::SPHINX_ADMINUNITS:
                $query->where('isadmin', '=', 1);
                $query->limit(0, 3);
                break;
            case self::SPHINX_COUNTRIES:
                $query->where('iscountry', '=', 1);
                $query->limit(0, 2);
                break;
        }

        // Now, you have an array of results stored.
        $result = $query->execute();
        return $result;
    }

    /**
     * Used as AJAX source by the autosuggest on the search form
     */
    public function suggestLocations($location, $type)
    {
        $result = array();
        $locations = array();
        $result['status'] = 'failed';
        // First get places from sphinx
        $resPlaces = $this->sphinxSearch( $location, self::SPHINX_PLACES );
        if ( $resPlaces) {
            $result = $resPlaces->fetchAllAssoc();
            $ids = array();
            foreach ( $result as $row) {
                $ids[] = $row['id'];
            }
            $places = $this->getPlacesFromDataBase($ids);
            $locations = array_merge($locations, $places);
            $result['result'] = 'success';
            $result['places'] = 1;
        }
        // Get administrative units
        $resAdminUnits = $this->sphinxSearch( $location, self::SPHINX_ADMINUNITS );
        if ( $resAdminUnits) {
            $result = $resAdminUnits->fetchAllAssoc();
            $ids = array();
            foreach ( $result as $row) {
                $ids[] = $row['id'];
            }
            $adminunits= $this->getFromDataBase($ids, $this->getWords()->getSilent('SearchAdminUnits'));
            $locations = array_merge($locations, $adminunits);
            $result["status"] = "success";
            $result['adminunits'] = 1;
        }
        // Get countries
        $resCountries = $this->sphinxSearch( $location, self::SPHINX_COUNTRIES );
        if ( $resCountries) {
            $result = $resCountries->fetchAllAssoc();
            $ids = array();
            foreach ( $result as $row) {
                $ids[] = $row['id'];
            }
            $countries= $this->getFromDataBase($ids, $this->getWords()->getSilent('SearchCountries'));
            $locations = array_merge($locations, $countries);
            $result["status"] = "success";
            $result['countries'] = 1;
        }
        // If nothing was found assume that search daemon isn't running and
        // try to get something from the database
        if (empty($locations)) {
            // assume format place[, [admin1,] country
            $admin1 = $country = "";
            $locationParts = explode(',', $location);
            $place = trim($locationParts[0]);
            switch (count($locationParts)) {
                case 3:
                    $admin1 = trim($locationParts[1]);
                    $country = trim($locationParts[2]);
                    break;
                case 2:
                    $country = trim($locationParts[1]);
                    break;
            }
            list($countryIds, $admin1Ids) = $this->getIdsForCountriesAndAdminUnits($country, $admin1);
            $locations = $this->getPlaces($place, $admin1Ids, $countryIds, 10);
            $result['database'] = 1;
        }
        if (!empty($locations)) {
            $result['status'] = 'success';
        }
        $result["locations"] = $locations;

        return $result;
    }

    /**
     * Used as AJAX source by the autosuggest for usernames
     */
    public function suggestUsernames($username)
    {
        $result = array();
        $query = "
            SELECT
                id as tag_id, username as tag_value
            FROM
                members m
            WHERE
                username like '" . $this->dao->escape($username) . "%'
                AND Status in (" . Member::ACTIVE_ALL . ")
            ORDER BY
                username
            LIMIT
                0,10";
        $usernames = $this->bulkLookup($query);

        return $usernames;
    }

    /**
     * Returns a list of suggestions that match with a given text
     */
    public function searchSuggestions($text)
    {
        $sphinx = new MOD_sphinx();
        $sphinxClient = $sphinx->getSphinxSuggestions();

        $results = $sphinxClient->Query($sphinxClient->EscapeString($text), 'suggestions');

        $suggestions = array();
        if ($results['total'] <> 0) {
            foreach( $results['matches'] as $match) {
                $suggestion = new Suggestion($match['id']);
                $suggestions[] = $suggestion;
            }
        }
        return $suggestions;
    }

    /**
     * Gets only username and accommodation status for the given location and distance
     *
     * Used on the map to show the results
     *
     * @param $location
     * @param $distance
     */
    private function _getMembersLowDetails($latitude, $longitude, $distance, $canhost = 1) {
        $rectangle = $this->_getRectangle($latitude, $longitude, $distance);
        $query = "
            SELECT
                m.Accomodation as Accommodation, m.Username, latitude, longitude
            FROM
                members m
            WHERE
                m.Status IN ('Active', 'OutOfRemind')
                AND m.maxGuest >= {$canhost}
                AND m.latitude BETWEEN $rectangle->latne AND $rectangle->latsw
                AND m.longitude BETWEEN $rectangle->longne AND $rectangle->longsw
        ";
        $query .= $this->accommodationCondition;
        $query .= " ORDER BY Accomodation";
        $results = $this->bulkLookup($query);

        return $results;
    }
}

