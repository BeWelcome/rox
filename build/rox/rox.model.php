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
 * rox model
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class Rox extends PAppModel {
    protected $dao;
    
    // supported languages for translations; basis for flags in the footer
	private $_langs = array();
    
	/**
	 * @see /htdocs/bw/lib/lang.php
	 */
    public function __construct()
    {
        parent::__construct();
        
        // TODO: it is fun to offer the members the language of the volunteers, i.e. 'prog',
        // so I don't make any exceptions here; but we miss the flag - the BV flag ;-)
        // TODO: is it consensus we use "WelcomeToSignup" as the decision maker for languages?
        $query = '
SELECT `ShortCode`
FROM `words`
WHERE code = \'WelcomeToSignup\'';
        $result = $this->dao->query($query);
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $this->_langs[] = $row->ShortCode;
        }   
    }
    
    
    
    
    /**
     * set defaults
     * TODO: check: how do we replace the files base.php and page.php? do we need a
     * replacement at all?
     * @see loadDefault in /build/mytravelbook/mytravelbook.model.ctrl
     * @see __construct in /build/rox/rox.model.ctrl
     * @param
     * @return true
     */
    public function loadDefaults()
    {
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        PVars::register('lang', $_SESSION['lang']);
        
        if (file_exists(SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php')) {
	        $loc = array();
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php';
	        setlocale(LC_ALL, $loc);
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/page.php';
        }
        
        return true;
    }
    
    /**
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return boolean if language is supported true, otherwise false
     */
    public function isValidLang($lang)
    {
        return in_array($lang, $this->_langs);
    }
    
    /**
     * @param
     * @return associative array mapping language abbreviations to 
     * 			long, English names of the language
     */
    public function getLangNames()
    {
        
        $l =  '';
		foreach ($this->_langs as $lang) {
		    $l .= '\'' . $lang . '\',';
		}
		$l = substr($l, 0, (strlen($l)-1));
		
        $query = '
SELECT `EnglishName`, `ShortCode`
FROM `languages`
WHERE `ShortCode` in (' . $l . ')
		';
        $result = $this->dao->query($query);
        
        $langNames = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $langNames[$row->ShortCode] = $row->EnglishName;
        }
        return $langNames;
    }
    
    public function getNewMessagesNumber($_idUser)
    {
        $query = '
SELECT COUNT(*) AS n
FROM `messages`
WHERE `IdReceiver` = ' . $_idUser . '
AND `Status` = \'Sent\'
AND (NOT FIND_IN_SET(\'receiverdeleted\', `DeleteRequest`))
AND `WhenFirstRead` = 0';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
		return $record->n;
    }


    
    /**
     * Returns the number of people due to be checked to become a member
     * of BW. The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people waiting acceptance
     */
    public function getNumberPersonsToBeAccepted()
    {
        $R = MOD_right::get();
        $AccepterScope=$R->RightScope('Accepter');
        if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
           $InScope = " /* All countries */";
        } else {
          $InScope = "AND countries.id IN (" . $AccepterScope . ")";
        }
        $query = '
SELECT SQL_CACHE COUNT(*) AS cnt
FROM members, countries, cities
WHERE members.Status=\'Pending\'
AND cities.id=members.IdCity
AND countries.id=cities.IdCountry ' . $InScope;
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people in need to be checked
     */
    public function getNumberPersonsToBeChecked($AccepterScope)
    {
        // FIXME: this if clause indicates a problem, doesn't it???
        // But you need database access to solve it.
        if (($AccepterScope == "\"All\"") or ($AccepterScope == "All") or ($AccepterScope == "'All'")) {
           $InScope = " /* All countries */";
        } else {
          $InScope = "AND countries.id IN (" . $AccepterScope . ")";
        }
        $query = '
SELECT SQL_CACHE COUNT(*) AS cnt
FROM pendingmandatory, countries, cities
WHERE pendingmandatory.Status=\'Pending\'
AND cities.id=pendingmandatory.IdCity
AND countries.id=cities.IdCountry ' . $InScope;
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of people due to be checked to problems or what.
     * The number depends on the scope of the person logged on.
     *
     * @return integer indicating the number of people wiche need to be accepted 
		 * in a Group if the current member has right to accept them
     */
    public function getNumberPersonsToAcceptInGroup($GroupScope)
    {
        // FIXME: this if clause indicates a problem, doesn't it???
        // But you need database access to solve it.
				$where="" ;
				if ($GroupScope!='"All"') {
				 		 $tt=explode(",",$GroupScope) ;
						 $where="(" ;
						 foreach ($tt as $Scope) {
						 				 if ($where!="(") {
										 		$where.="," ;
										 }
										 $where=$where.$Scope;
						 }
						 $where=" and `groups`.`Name` in " .$where.")" ;
				}
        $query = 'SELECT SQL_CACHE COUNT(*) AS cnt FROM `membersgroups`,`groups` where `membersgroups`.`Status`="WantToBeIn" and `groups`.`id`=`membersgroups`.`IdGroup`'.$where ;
//	 die($query) ;
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        if (isset($record->cnt)) {
					 return $record->cnt;
				}
				else {
					 return(0) ;
				}
    } // end of getNumberPersonsToAcceptedInGroup

    /**
     * Returns the number of messages, which should be checked.
     *
     */
    public function getNumberMessagesToBeChecked()
    {
        $query = '
SELECT COUNT(*) AS cnt
FROM messages
WHERE Status=\'ToCheck\'
AND messages.WhenFirstRead=\'0000-00-00 00:00:00\'';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }
    
    /**
     * Returns the number of spam messages
     *
     */
    public function getNumberSpamToBeChecked()
    {
        $query = '
SELECT COUNT(*) AS cnt
FROM messages, members AS mSender, members AS mReceiver
WHERE mSender.id=IdSender
AND messages.SpamInfo=\'SpamSayMember\'
AND mReceiver.id=IdReceiver
AND mSender.Status=\'Active\'';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        return $record->cnt;
    }

// retrieve the number of members for each country
	public function getMembersPerCountry() {
		$query = 'select countries.Name 
		as countryname,count(*) 
		as cnt from members,countries,cities where members.Status="Active" 
		and members.IdCity=cities.id 
		and cities.IdCountry=countries.id group by countries.id  order by cnt desc';
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve number of members per Country!');
		}
		$result = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$result[$row->countryname] = $row->cnt;
		}
		return $result;		
	}

//retrieve the last login date from the db


	public function getLastLoginRank() {
		$query = 'select TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) AS logindiff, COUNT(*) AS cnt FROM members 
		WHERE TIMESTAMPDIFF(DAY,members.LastLogin,NOW()) >= 0
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

	
// retrieve the stats from db - all time weekly average
	public function getStatsLogAll() {
		$query = 'select AVG(NbActiveMembers) AS NbActiveMembers,AVG(NbMessageSent) AS NbMessageSent,AVG(NbMessageRead) AS NbMessageRead,AVG(NbMemberWithOneTrust) AS NbMemberWithOneTrust,AVG(NbMemberWhoLoggedToday) AS NbMemberWhoLoggedToday,created,YEARWEEK(created) AS week  
		FROM stats
		GROUP BY week ';
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
	
// retrieve the stats from db - daily for last 2months
	public function getStatsLog2Month() {
		$query = 'select * 
		FROM stats
		ORDER BY id DESC
		LIMIT 0,60';
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


    /**
     * Returns true if member belongs to group volunteer
     *
     */
	 
    public function isVolunteer($_idUser)
    {
        $query = '
SELECT *
FROM membersgroups
WHERE membersgroups.IdGroup = 17
AND membersgroups.Status="In" 
AND membersgroups.IdMember='. $_idUser;
        $result = $this->dao->query($query);
		$record = $result->fetch(PDB::FETCH_OBJ);
		if (!empty($record)) {
		return true;
		}
		else return false;
    }
}
?>
