<?php


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
		// This query countes the number of members in the same way as HC or CS
        $query = "
SELECT
    countries.Name AS countryname,
    count(*) AS cnt
FROM members,countries,cities
WHERE (members.Status in ('Active','ChoiceInactive','OutOfRemind'))  
AND members.IdCity=cities.id 
AND cities.IdCountry=countries.id
GROUP BY countries.id
ORDER BY cnt desc
        ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve number of members per Country!');
        }
        $result = array();
        $i=0;
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($i<6) {
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
     * retrieve the last login date from the db
     */
    public function getLastLoginRank() {
        $query = '
SELECT
    TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff,
    COUNT(*) AS cnt
FROM members 
WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
GROUP BY logindiff 
ORDER BY logindiff ASC
        ';
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
            } else {
                    $result['longer'] =  $result['longer'] + $row->cnt;        
            }
        }
        return $result;        
    }    

    
    /**
     * retrieve the stats from db - all time weekly average
     */
    public function getStatsLogAll() {
        $query = '
SELECT
    AVG(NbActiveMembers) AS NbActiveMembers,
    AVG(NbMessageSent) AS NbMessageSent,
    AVG(NbMessageRead) AS NbMessageRead,
    AVG(NbMemberWithOneTrust) AS NbMemberWithOneTrust,
    AVG(NbMemberWhoLoggedToday) AS NbMemberWhoLoggedToday,
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
     * retrieve the stats from db - daily for last 2months
     */
    public function getStatsLog2Month() {
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
}


?>