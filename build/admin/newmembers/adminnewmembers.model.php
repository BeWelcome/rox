<?php

/**
 * Class AdminNewMembersModel
 */
class AdminNewMembersModel extends RoxModelBase {

    private $_statuses = null;

    private function ReplaceWithBR($ss,$ReplaceWith=false) {
        if (!$ReplaceWith) return ($ss);
        return(str_replace("\n","<br>",$ss));
    }

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

    private function getNamePart($namePartId) {
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
     *
     */
    public function getMembersCount($safetyTeamOrAdmin) {
        $statuses = "'Active'";
        if ($safetyTeamOrAdmin) {
            $statuses .= ", 'MailToConfirm'";
        }
        $query = "
            SELECT
                count(*) as cnt
            FROM
                members m
            WHERE
                m.Status IN (" . $statuses . ")";
        if (!$safetyTeamOrAdmin) {
            $query .= " AND bewelcomed < 3";
        }
        $query .= " AND DATEDIFF(NOW(), created) < 120";
        $row = $this->singleLookup($query);
        return $row->cnt;
    }

    /**
     *
     * @param array $vars
     * @param string $admin1
     * @param string $country
     * @return multitype:unknown
     */
    public function getMembers($first, $count, $safetyTeamOrAdmin) {
        $langarr = explode('-', $_SESSION['lang']);
        $lang = $langarr[0];
        // First get current page and limits

        $statuses = "'Active'";
        if ($safetyTeamOrAdmin) {
            $statuses .= ", 'MailToConfirm'";
        }

        $str = "
            SELECT DISTINCT
                m.id,
                m.Username,
                m.created,
                m.Status,
                m.BirthDate,
                m.HideBirthDate,
                m.Accomodation,
                m.TypicOffer,
                m.Restrictions,
                m.ProfileSummary,
                m.Occupation,
                m.Gender,
                m.HideGender,
                m.FirstName,
                m.SecondName,
                m.LastName,
                m.email,
                m.bewelcomed,
                g.geonameid as geonameid,
                g.country as country
            FROM
                members m,
                geonames g
            WHERE
                m.Status IN (" . $statuses . ")
                AND DATEDIFF(NOW(), created) < 120";
        if (!$safetyTeamOrAdmin) {
            $str .= " AND bewelcomed < 3";
        }
        $str .= " AND m.IdCity = g.geonameid
            ORDER BY
                m.created DESC
            LIMIT
                " . $first . ", " . $count;

        $rawMembers = $this->bulkLookup($str);

        $loggedInMember = $this->getLoggedInMember();

        $members = array();
        $geonameIds = array();
        $countryIds = array();
        $layoutBits = new MOD_layoutbits();
        foreach($rawMembers as $member) {
            $geonameIds[$member->geonameid] = $member->geonameid;
            $countryIds[$member->country] = $member->country;
            $aboutMe = MOD_layoutbits::truncate_words($this->FindTrad($member->ProfileSummary,true), 70);
            $FirstName = $this->getNamePart($member->FirstName);
            $SecondName = $this->getNamePart($member->SecondName);
            $LastName = $this->getNamePart($member->LastName);
            $member->Name = trim($FirstName . " " . $SecondName . " " . $LastName);
            $member->ProfileSummary = $aboutMe;

            if ($safetyTeamOrAdmin) {
                $email = Mod_crypt::AdminReadCrypted($member->email);
                $member->EmailAddress = $email;
            }

            if ($member->HideBirthDate=="No") {
                $member->Age =floor($layoutBits->fage_value($member->BirthDate));
            } else {
                $member->Age = "";
            }
            if ($member->HideGender != "Yes") {
                $member->GenderString = MOD_layoutbits::getGenderTranslated($member->Gender, false, false);
            }
            $member->Occupation = MOD_layoutbits::truncate_words($this->FindTrad($member->Occupation), 10);

            $query = "
                SELECT
                    mll.Level,
                    l.WordCode,
                    l.EnglishName,
                    l.Name
                FROM
                    memberslanguageslevel mll,
                    languages l
                WHERE
                  mll.IdMember = " . $member->id . "
                  AND mll.IdLanguage = l.Id
                ORDER BY
                  mll.Level ASC";
            $languages = $this->bulkLookup($query);
            $member->languages = $languages;
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
        foreach($rawNames as $rawName) {
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
        foreach($countryRawNames as $countryRawName) {
            if (!isset($countryNames[$countryRawName->countryCode])) {
                $countryNames[$countryRawName->countryCode] = $countryRawName->country;
            }
        }
        foreach($members as &$member) {
            $member->CityName = $names[$member->geonameid];
            $member->CountryName = $countryNames[$member->country];
        }
        return $members;
    }

    public function getStatuses() {
        if (!isset($this->_statuses)) {
            $MembersModel = new MembersModel();
            $this->_statuses = $MembersModel->getStatuses();
        }
        return $this->_statuses;
    }

    public function localGreetingSent($member) {
        $bewelcomed = $member->bewelcomed;
        $bewelcomed |= 1;
        $member->bewelcomed = $bewelcomed;
        $member->update();
    }

    public function globalGreetingSent($member) {
        $bewelcomed = $member->bewelcomed;
        $bewelcomed |= 2;
        $member->bewelcomed = $bewelcomed;
        $member->update();
    }
}