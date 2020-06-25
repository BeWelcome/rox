<?php

use App\Doctrine\MemberStatusType;

/**
 * AboutStatisticsPage
 *
 * @package about
 * @author Philipp
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class StatsModel extends RoxModelBase
{
    public function __construct()
    {
        parent::__construct();
    }

    //---------------------------------
    // needed for statistics page
    //---------------------------------

    /**
     * retrieve the number of members for each country
     */
    public function getMembersPerCountry() {
		// This query counts the number of members in the same way as HC or CS
        $query = "
            SELECT
                gc.name AS countryname,
                count(*) AS cnt
            FROM
                members m,
                geonamescountries gc,
                geonames g
            WHERE
                m.Status IN (" . MemberStatusType::ACTIVE_ALL . ")
                AND
                m.IdCity = g.geonameId
                AND
                g.country = gc.country
            GROUP BY
                gc.country
            ORDER BY
                cnt DESC
            ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of members per Country!');
        }
        $result = array();
        $i=0;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<14) {
                $result[$row->countryname] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * retrieve the number of members for each languages
     */
    public function getLanguages() {
		// This fetches the languages spoken by the members along with their count
        $query = "
            SELECT
                l.englishname language,
                COUNT(m.id) cnt
            FROM
                memberslanguageslevel mll,
                languages l,
                members m
            WHERE
                l.id = mll.IdLanguage
                AND mll.idMember = m.id
                AND m.Status IN (" . MemberStatusType::ACTIVE_ALL . ")
            GROUP BY
                l.name
            ORDER BY
                cnt DESC";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of languages per members!');
        }
        $result = array();
        $i=1;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<10) {
                $result[$row->language] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * retrieve the number of members for each country
     */
    public function getPreferredLanguages() {
		// This fetches the languages spoken by the members along with their count
        $query = "
            SELECT
                COUNT(m.id) cnt, l.englishname language
            FROM
                languages l, `members` m
            LEFT JOIN
                memberspreferences mp
            ON
                m.id = mp.idmember
                AND mp.idpreference = 1
            WHERE
                m.status IN (" . MemberStatusType::ACTIVE_ALL . ")
                AND l.id = IFNULL(mp.value, 0)
            GROUP BY
                language
            ORDER BY
                cnt DESC";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of preferred languages per members!');
        }
        $result = array();
        $i=0;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<14) {
                $result[$row->language] = $row->cnt;
            }
            else {
                if (isset($result["Others"])) {
                    $result["Others"] = $result["Others"] + $row->cnt;
                }
                else {
                    $result["Others"] = $row->cnt;
                }
            }
            $i++;
        }
        return $result;
    }

/**
     * retrieve the last login date from the db
     */
    public function getLastLoginRank() {
        $query = '
SELECT
    TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
    COUNT(*) AS cnt
FROM members
WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
AND status IN (' . MemberStatusType::ACTIVE_ALL . ')
GROUP BY logindiff
ORDER BY logindiff ASC';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve last login listing!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                    $result[$row->logindiff] = $row->cnt;
        }
        return $result;
    }

    public function getLastLoginRankGrouped() {
        $query = '
SELECT
    TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
    COUNT(*) AS cnt
FROM members
WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
AND status IN (' . MemberStatusType::ACTIVE_ALL . ')
GROUP BY logindiff
ORDER BY logindiff ASC
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve last login listing!');
        }
        $result = array();

        $result['1 day'] = 0;
        $result['1 week'] = 0;
        $result['1-2 weeks'] = 0;
        $result['2-4 weeks'] = 0;
        $result['1-3 months'] = 0;
        $result['3-6 months'] = 0;
        $result['6-12 months'] = 0;
        $result['longer'] = 0;


        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($row->logindiff==1) {
                    $result['1 day'] = $result['1 day'] + $row->cnt;
            } elseif ($row->logindiff<=7) {
                    $result['1 week'] = $result['1 week'] + $row->cnt;
            } elseif ($row->logindiff<=14) {
                    $result['1-2 weeks'] = $result['1-2 weeks'] + $row->cnt;
            } elseif ($row->logindiff<=30) {
                    $result['2-4 weeks'] = $result['2-4 weeks'] + $row->cnt;
            } elseif ($row->logindiff<=90) {
                    $result['1-3 months'] = $result['1-3 months'] + $row->cnt;
            } elseif ($row->logindiff<=182) {
                    $result['3-6 months'] = $result['3-6 months'] + $row->cnt;
            } elseif ($row->logindiff<=365) {
                    $result['6-12 months'] = $result['6-12 months'] + $row->cnt;
            } else {
                $result['longer'] =  $result['longer'] + $row->cnt;
            }
        }
        return $result;
    }


    /**
     * retrieve the stats from db - all time weekly average
     */
    public function getStatisticsAll() {
        $query = '
SELECT
    MAX(NbActiveMembers) AS NbActiveMembers,
    MAX(NbMessageSent) AS NbMessageSent,
    MAX(NbMessageRead) AS NbMessageRead,
    MAX(NbMemberWithOneTrust) AS NbMemberWithOneTrust,
    MAX(NbMemberWhoLoggedToday) AS NbMemberWhoLoggedToday,
    created,
    YEARWEEK(created) AS week
FROM stats
GROUP BY week
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve statistics table!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * retrieve the stats from db - all time weekly average
     */
    public function getRequestsAll() {
        $query = "
SELECT
    MAX(NbRequestsSent) AS NbRequestsSent,
    MAX(NbRequestsAccepted) AS NbRequestsAccepted,
    created,
    YEARWEEK(created) AS week
FROM stats
WHERE created >= '2019-04-20'
GROUP BY week
        ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve statistics table!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * retrieve the stats from db - daily for last 2months
     */
    public function getStatsLog2Months() {
        $query = '
SELECT *
FROM stats
ORDER BY id DESC
LIMIT 0,60
        ';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve statistics table!');
        }
        $result = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $result[] = $row;
        }
        $result = array_reverse($result);
        return $result;
    }

    public function updateStatistics()
    {
        // Number of member
        $query = "SELECT COUNT(*) AS cnt FROM members WHERE Status in ('Active','ChoiceInactive','OutOfRemind')";
        $row = $this->singleLookup($query);
        $NbActiveMembers = $row->cnt;

        // Number of member with at least one positive comment
        $query = "SELECT COUNT(DISTINCT(members.id)) AS cnt FROM members,comments WHERE Status in ('Active','ChoiceInactive','OutOfRemind') AND members.id=comments.IdToMember AND comments.Quality='Good'";
        $row = $this->singleLookup($query);
        $NbMemberWithOneTrust = $row->cnt;

        $d1 = strftime("%Y-%m-%d 00:00:00", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $d2 = strftime("%Y-%m-%d 00:00:00", mktime(0, 0, 0, date("m"), date("d"), date("Y")));

        // Number of member who have logged
        $NbMemberWhoLoggedToday = 0;
        $str = "SELECT COUNT(distinct(members.id)) as cnt from members right join " . PVars::getObj('syshcvol')->ARCH_DB . ".logs on  members.id=" . PVars::getObj('syshcvol')->ARCH_DB . ".logs.IdMember and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.type='Login' and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.created between '$d1' and '$d2' and " . PVars::getObj('syshcvol')->ARCH_DB . ".logs.Str like 'Successful login%' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMemberWhoLoggedToday = $row->cnt;
        }

        $NbMessageSent = 0;
        $str = "SELECT COUNT(*) as cnt from messages where DateSent between '$d1' and '$d2' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMessageSent = $row->cnt;
        }

        // Number of message read
        $NbMessageRead = 0;
        $str = "SELECT COUNT(*) as cnt from messages where WhenFirstRead between '$d1' and '$d2' ";
        $rr = $this->dao->query($str);
        if ($rr) {
            $row = $rr->fetch(PDB::FETCH_OBJ);
            $NbMessageRead = $row->cnt;
        }

        $str = "INSERT INTO stats ( id , created , NbActiveMembers , NbMessageSent , NbMessageRead , NbMemberWithOneTrust , NbMemberWhoLoggedToday )VALUES (NULL ,CURRENT_TIMESTAMP , $NbActiveMembers , $NbMessageSent , $NbMessageRead , $NbMemberWithOneTrust , $NbMemberWhoLoggedToday )";
        $this->dao->query($str);

        return true;
    }
}

