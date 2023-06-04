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

use App\Doctrine\MemberStatusType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\SphinxQL;
use AnthonyMartin\GeoLocation\GeoPoint;

/**
 * Search model
 *
 * @package Search
 * @author shevek
 */
class SearchModel extends RoxModelBase
{
    const ORDER_USERNAME = 2;
    const ORDER_ACCOM = 6;
    const ORDER_LOGIN = 8;
    const ORDER_MEMBERSHIP = 10;
    const ORDER_COMMENTS = 12;
    const ORDER_DISTANCE = 14;

    const DIRECTION_ASCENDING = 1;
    const DIRECTION_DESCENDING = 2;

    private $statusCondition = "";
    private $maxGuestCondition = "";
    private $keywordCondition = "";
    private $ageCondition = "";
    private $usernameCondition = "";
    private $genderCondition = "";
    private $locationCondition = "";
    private $groupsCondition = "";
    private $offersCondition = "";
    private $restrictionsCondition = "";
    private $languagesCondition = "";
    private $accommodationCondition = "";
    private $typicalOfferCondition = "";
    private $commentsCondition = "";
    private $profilePictureCondition = "";
    private $profileSummaryCondition = "";
    private $limitAndOffsetExpression = "";
    private $distanceExpression = "";
    private $orderByExpression = "";

    private $tables = "";
    private $joins = "";

    private const ORDER_BY = [
        self::ORDER_USERNAME => ['WordCode' => 'SearchOrderUsername', 'Column' => 'm.Username'],
        self::ORDER_ACCOM => ['WordCode' => 'SearchOrderAccommodation', 'Column' => 'accomodation'],
        self::ORDER_DISTANCE => ['WordCode' => 'SearchOrderDistance', 'Column' => 'Distance'],
        self::ORDER_LOGIN => ['WordCode' => 'SearchOrderLogin', 'Column' => 'LastLogin'],
        self::ORDER_MEMBERSHIP => ['WordCode' => 'SearchOrderMembership', 'Column' => 'm.created'],
        self::ORDER_COMMENTS => ['WordCode' => 'SearchOrderComments', 'Column' => 'CommentCount'],
    ];

    private EntityManagerInterface $entityManager;

    /**
     * SearchModel constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    private function getOrderBy($orderBy, $direction)
    {
        $orderType = $orderBy - ($orderBy % 2);
        if (self::ORDER_MEMBERSHIP === $orderBy) {
            $directionType = ' DESC';
        } else {
            $directionType = ' ASC';
        }
        $order = self::ORDER_BY[$orderType]['Column'] . $directionType;
        switch ($orderType) {
            case self::ORDER_ACCOM:
                $order .= ', (IF(mp.photoCount IS NULL, 0, 1) + IF(m.ProfileSummary != 0, 2, 0)) ASC'
                    . ', hosting_interest ASC, LastLogin DESC, Distance ASC';
                break;
            case self::ORDER_COMMENTS:
                $order .= ', (IF(mp.photoCount IS NULL, 0, 1) + IF(m.ProfileSummary != 0, 2, 0)) ASC, '
                        . 'LastLogin DESC, Distance ASC';
                break;
            case self::ORDER_DISTANCE:
                $order .= ', hosting_interest DESC, LastLogin DESC';
                break;
        }

        // if descending order is requested switch all ASC to DESC and vice versa
        if (self::DIRECTION_DESCENDING == $direction) {
            $order = str_replace('ASC', 'BSC', $order);
            $order = str_replace('DESC', 'ASC', $order);
            $order = str_replace('BSC', 'DESC', $order);
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

        if ($this->session->has( 'IdLanguage' )) {
            $IdLanguage = $this->session->get('IdLanguage');
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

    /**
     * @param array $vars
     * @return string
     */
    private function getStatusCondition($vars): string
    {
        // Calculate last login since x month rougly
        $daysSinceLastLogin = intval($vars['search-last-login'] * 30.4);
        $statusCondition = " AND m.status IN (" . MemberStatusType::ACTIVE_SEARCH . ") AND ";
        $statusCondition .= " DATEDIFF(NOW(), m.LastLogin) <= ";
        $statusCondition .= $daysSinceLastLogin . " ";

        return $statusCondition;
    }

    /**
     * @param array $vars
     * @return string
     */
    private function getCommentsCondition($vars): string
    {
        $commentsCondition = "";
        if ($vars['search-has-comments']) {
            $commentsCondition .= " AND CommentCount > 0 ";
        }

        return $commentsCondition;
    }

    /**
     * @param array $vars
     * @return string
     */
    private function getProfilePictureCondition($vars): string
    {
        $profilePictureCondition = "";
        if ($vars['search-has-profile-picture']) {
            $profilePictureCondition .= " AND IF(mp.photoCount IS NULL, 0, 1) = 1 ";
        }

        return $profilePictureCondition;
    }

    /**
     * @param array $vars
     * @return string
     */
    private function getProfileSummaryCondition($vars): string
    {
        $profileSummaryCondition = "";
        if ($vars['search-has-about-me']) {
            $profileSummaryCondition .= " AND IF(m.ProfileSummary != 0, 2, 0) = 2 ";
        }

        return $profileSummaryCondition;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param $distance
     *
     * @return stdClass
     */
    private function _getRectangle($latitude, $longitude, $distance): stdClass
    {

        $result = new stdClass();

        try {
            $center = new GeoPoint($latitude, $longitude);
            $boundingBox = $center->boundingBox($distance, 'km');
            $result->latne = $boundingBox->getMaxLatitude();
            $result->longne = $boundingBox->getMaxLongitude();
            $result->latsw = $boundingBox->getMinLatitude();
            $result->longsw = $boundingBox->getMinLongitude();
        } catch (\Exception $e) {
            // If this really happens the map search area will be rather small :)
            $result->latne = $result->longne = $result->latsw = $result->longsw = 0;
        }

        return $result;
    }

    /**
     *
     * @param array $vars
     * @param array $adminUnits
     * @param string $country
     * @return string
     */
    private function getLocationConditionAdminUnit($adminUnits, $country): string
    {
        $condition = "AND m.IdCity = g.geonameId ";
        if (!empty($adminUnits)) {
            $i = 1;
            foreach ($adminUnits as $adminUnit) {
                $condition .= "AND g.admin_{$i}_id = '{$adminUnit}' ";
                $i++;
            }
        }
        $condition .= "AND g.country_id = '{$country}'";

        return $condition;
    }

    private function getLocationConditionLocation($vars): string
    {
        $condition = "AND m.IdCity = g.geonameId ";
        $distance = $vars['search-distance'];
        if ($distance > 0) {
            $rectangle = $this->_getRectangle($vars['location-latitude'], $vars['location-longitude'], $distance);
            // calculate rectangle around place with given distance
            $longne = $rectangle->longne;
            $longsw = $rectangle->longsw;

            $latne = $rectangle->latne;
            $latsw = $rectangle->latsw;

            // TODO: search around the date line!

            // now fetch all location from geonames which are in that given rectangle
            $condition .= "AND g.latitude BETWEEN {$latsw} AND {$latne}"
                . "AND g.longitude BETWEEN {$longsw} AND {$longne}";
        } else {
            $condition .= " AND m.IdCity = {$vars['location-geoname-id']}";
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
                case "other":
                    $condition = " AND m.Gender = 'other' AND m.HideGender = 'No'";
                    break;
            }
        }

        return $condition;
    }

    private function getAgeCondition($vars)
    {
        $minAge = $maxAge = null;
        $condition = "";
        if (isset($vars['search-age-minimum']) && ($vars['search-age-minimum'] != 0)) {
            $minAge = $vars['search-age-minimum'];
            $condition .= ' AND m.BirthDate <= (NOW() - INTERVAL ' . $minAge . ' YEAR)';
        }
        if (isset($vars['search-age-maximum']) && ($vars['search-age-maximum'] != 0)) {
            $maxAge = $vars['search-age-maximum'];
            $condition .= ' AND m.BirthDate >= (NOW() - INTERVAL ' . $maxAge . ' YEAR)';
        }
        if (!empty($condition) && !($minAge == 18 && $maxAge == 120)) {
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

    private function getRestrictionsCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-restrictions'])) {
            $restrictions = [];
            $searchRestrictions = $vars['search-restrictions'];
            foreach ($searchRestrictions as $value) {
                if ($value='') {
                    continue;
                }
                $restrictions[] = "m.restrictions LIKE '{$value}'";
            }
            if (!empty($restrictions)) {
                $condition = " AND ( " . implode(" AND ", $restrictions) . ") ";
            }
        }
        return $condition;
    }

    private function getTypicalOfferCondition($vars)
    {
        $condition = "";
        if (isset($vars['search-typical-offers'])) {
            $typicalOffers = array();
            $typicalOffer = $vars['search-typical-offers'];
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

    public function getMembersCount()
    {
        // Fetch count of public members at/around the given place
        $str = "
            SELECT
                COUNT(DISTINCT m.id) cnt
            FROM
                {$this->tables}
                {$this->joins}
            WHERE
                {$this->maxGuestCondition}
                {$this->statusCondition}
                {$this->locationCondition}
                {$this->genderCondition}
                {$this->commentsCondition}
                {$this->profilePictureCondition}
                {$this->profileSummaryCondition}
                {$this->restrictionsCondition}
                {$this->offersCondition}
                {$this->ageCondition}
                {$this->usernameCondition}
                {$this->keywordCondition}
                {$this->groupsCondition}
                {$this->languagesCondition}
                {$this->accommodationCondition}
                {$this->typicalOfferCondition}
        ";

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
     * @param bool $admin1
     * @param bool $country
     * @return mixed
     * @throws PException
     */
    private function getDetailsForMembers()
    {
        $langarr = explode('-', $this->session->get('lang'));
        $lang = $langarr[0];
        // First get current page and limits
        // *FROM* and *WHERE* will be replaced later on (don't change)
        $str = "
            SELECT DISTINCT
                m.id,
                m.Username,
                Date(m.created) as 'created',
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation as 'Accommodation',
                m.TypicOffer,
                m.Restrictions,
                m.ProfileSummary,
                m.Occupation,
                m.Gender,
                m.HideGender,
                m.MaxGuest,
                m.HideAttribute,
                m.FirstName,
                m.SecondName,
                m.LastName,
                IF (m.accomodation = 'neverask', 0, m.hosting_interest) as hosting_interest,
                date_format(m.LastLogin,'%Y-%m-%d') AS LastLogin,
                IF(m.ProfileSummary != 0, 2, 0) AS HasProfileSummary,
                IF(mp.photoCount IS NULL, 0, 1) AS HasProfilePhoto,
                g.geonameId,
                g.country,
                g.latitude,
                g.longitude,
                {$this->distanceExpression},
                IF(c.IdToMember IS NULL, 0, c.commentCount) AS CommentCount
            FROM
                {$this->tables}
            LEFT JOIN (
                SELECT
                    COUNT(*) As commentCount, IdToMember
                FROM
                    comments, members m2
                WHERE
                    IdFromMember = m2.id
                    AND m2.Status IN ('Active', 'OutOfRemind')
                GROUP BY
                    IdToMember
                ) c ON c.IdToMember = m.id
            LEFT JOIN (
                SELECT
                    COUNT(*) As photoCount, IdMember
                FROM
                    membersphotos
                GROUP BY
                    IdMember
                ) mp ON mp.IdMember = m.id
            WHERE
                {$this->maxGuestCondition}
                {$this->statusCondition}
                {$this->commentsCondition}
                {$this->profilePictureCondition}
                {$this->profileSummaryCondition}
                {$this->restrictionsCondition}
                {$this->offersCondition}
                {$this->locationCondition}
                {$this->genderCondition}
                {$this->ageCondition}
                {$this->usernameCondition}
                {$this->keywordCondition}
                {$this->groupsCondition}
                {$this->languagesCondition}
                {$this->accommodationCondition}
                {$this->typicalOfferCondition}
            ORDER BY
                {$this->orderByExpression}
                {$this->limitAndOffsetExpression}
        ";

        $rawMembers = $this->bulkLookup($str);

        $loggedInMember = $this->getLoggedInMember();

        $members = array();
        $geonameIds = array();
        $countryIds = array();
        $layoutBits = new MOD_layoutbits();
        foreach ($rawMembers as $member) {
            $geonameIds[$member->geonameId] = $member->geonameId;
            $countryIds[$member->country] = $member->country;
        }

        $locationRepository = $this->entityManager->getRepository(\App\Entity\NewLocation::class);
        $rawLocations = $locationRepository->findBy(['geonameId' => $geonameIds]);

        $locations = [];
        foreach($rawLocations as $rawLocation) {
            if (!isset($locations[$rawLocation->getGeonameId()])) {
                $locations[$rawLocation->getGeonameId()] = $rawLocation;
            }
        }

        foreach ($rawMembers as $member) {
            $aboutMe = MOD_layoutbits::truncate_words($this->FindTrad($member->ProfileSummary, true), 70);
            $FirstName = ($member->HideAttribute & \Member::MEMBER_FIRSTNAME_HIDDEN) ? "" : $member->FirstName;
            $SecondName = ($member->HideAttribute & \Member::MEMBER_SECONDNAME_HIDDEN) ? "" : $member->SecondName;
            $LastName = ($member->HideAttribute & \Member::MEMBER_LASTNAME_HIDDEN) ? "" : $member->LastName;
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

            $member->MessageCount = 0;
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
            }

            $member->CityName = $locations[$member->geonameId]->getName();
            $country = $locations[$member->geonameId]->getCountry();
            $member->CountryName = $country ? $country->getName() : "";
            $members[] = $member;
        }

        return $members;
    }

    /*
     * Returns either a list of members for a selected location or
    * a list of possible locations based on the input text
    */
    public function getResultsForLocation(): array
    {
        return [
            'members' => $this->getDetailsForMembers(),
            'map' => $this->getLowDetailsForMembers(),
        ];
    }

    public function getMapResultsForLocation(): array
    {
        return ['map' => $this->getLowDetailsForMembers()];
    }

    /**
     * Gets only limited information (see query) for the given location and distance
     *
     * Used on the map to show the results.
     *
     * @return array
     * @throws Exception
     */
    private function getLowDetailsForMembers(): array
    {
        $query = "
            SELECT DISTINCT
                m.Accomodation as Accommodation, m.Username, m.latitude, m.longitude, m.maxGuest as CanHost
            FROM
                {$this->tables}
                {$this->joins}
            WHERE
                {$this->maxGuestCondition}
                {$this->statusCondition}
                {$this->commentsCondition}
                {$this->profilePictureCondition}
                {$this->profileSummaryCondition}
                {$this->restrictionsCondition}
                {$this->offersCondition}
                {$this->locationCondition}
                {$this->genderCondition}
                {$this->ageCondition}
                {$this->usernameCondition}
                {$this->keywordCondition}
                {$this->groupsCondition}
                {$this->languagesCondition}
                {$this->accommodationCondition}
                {$this->typicalOfferCondition}
        ";

        return $this->bulkLookup($query);
    }

    public function prepareQuery($vars, $adminUnits = [], $country = false)
    {
        $this->statusCondition = $this->getStatusCondition($vars);
        $this->commentsCondition = $this->getCommentsCondition($vars);
        $this->profilePictureCondition = $this->getProfilePictureCondition($vars);
        $this->profileSummaryCondition = $this->getProfileSummaryCondition($vars);
        $this->offersCondition = $this->getTypicalOfferCondition($vars);
        $this->restrictionsCondition = $this->getRestrictionsCondition($vars);
        $this->maxGuestCondition = "m.MaxGuest >= " . $vars['search-can-host'];
        if (!empty($adminunits) || false !== $country) {
            $this->locationCondition = $this->getLocationConditionAdminUnit($adminUnits, $country);
        } else {
            $this->locationCondition = $this->getLocationConditionLocation($vars);
        }
        $this->genderCondition = $this->getGenderCondition($vars);
        $this->ageCondition = $this->getAgeCondition($vars);
        $this->usernameCondition = $this->getUsernameCondition($vars);
        $this->keywordCondition = $this->getKeywordCondition($vars);
        $this->groupsCondition = $this->getGroupsCondition($vars);
        $this->languagesCondition = $this->getLanguagesCondition($vars);
        $this->typicalOfferCondition = $this->getTypicalOfferCondition($vars);
        $this->accommodationCondition = $this->getAccommodationCondition($vars);
        $this->limitAndOffsetExpression = $this->getLimitAndOffsetExpression($vars);
        $this->orderByExpression = $this->getOrderByExpression($vars);
        $this->distanceExpression = $this->getDistanceExpression($vars);

        $this->tables = 'geo__names g';
        if (!empty($this->keywordCondition)) {
            $this->tables .= ", memberstrads mt";
        }
        if (!empty($this->groupsCondition)) {
            $this->tables .= ", membersgroups mg";
        }
        if (!empty($this->languagesCondition)) {
            $this->tables .= ", memberslanguageslevel mll";
        }
        $this->tables .= ', members m';

        $this->joins = '';
        if (!empty($this->profilePictureCondition)) {
            $this->joins .= "
                LEFT JOIN (
                    SELECT
                        COUNT(*) As photoCount, IdMember
                    FROM
                        membersphotos
                    GROUP BY
                        IdMember) mp
                    ON
                        mp.IdMember = m.id
            ";
        }
        if (!empty($this->commentsCondition)) {
            $this->joins .= "
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
            ";
        }
    }

    private function getLimitAndOffsetExpression($vars): string
    {
        $maxItems = $vars['search-number-items'];
        $firstItemToShow = ($vars['search-page'] - 1) * $maxItems;
        return "LIMIT {$maxItems} OFFSET {$firstItemToShow}";
    }

    private function getOrderByExpression($vars): string
    {
        return $this->getOrderBy($vars['search-sort-order'], $vars['search-sort-direction']);
    }

    private function getDistanceExpression($vars): string
    {
        return
            "((g.latitude - {$vars['location-latitude']}) * (g.latitude - {$vars['location-latitude']}) + "
            . "(g.longitude - {$vars['location-longitude']}) * (g.longitude - {$vars['location-longitude']}))"
            . " AS Distance"
        ;
    }
}
