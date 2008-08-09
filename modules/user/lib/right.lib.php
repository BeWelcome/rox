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
 * 
 * @see /htdocs/bw/lib/rights.php
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class MOD_right
{

    /**
     * Singleton instance
     * 
     * @var MOD_right
     * @access private
     */
    private static $_instance;
    
    private $tableName;
    protected $dao;
    
    private function __construct()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
    }
    
    /**
     * singleton getter
     * 
     * @param void
     * @return PApps
     */
    public static function get()
    {   
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public function __destruct()
    {
        unset($this->_dao);
    }
    
    /**
     * FIXME: this is (with little exception) 
     * copy-paste from /htdocs/bw/lib/rights.php; to be improved!
     * 
     * @see /htdocs/bw/lib/rights.php
     */
    // -----------------------------------------------------------------------------
// return the RightLevel if the members has the Right RightName 
// optional Scope value can be send if the RightScope is set to All then Scope
// will always match if not, the sentence in Scope must be find in RightScope
// The function will use a cache in session
// $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
// from scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter 
// $OptionalIdMember  allow to specify another member than the current one, in this case the cache is not used
public function hasRight($RightName, $_Scope = "", $OptionalIdMember = 0) 
{
	global $_SYSHCVOL;

	//if (!IsLoggedIn())
	$A = new MOD_bw_user_Auth();
	if (!$A->isBWLoggedIn()) {
		return (0); // No need to search for right if no member logged, he has no right
	}
	if ($OptionalIdMember != 0) { // In case we want to test for the rigt of a specific member, who is not the logged
		$IdMember = $OptionalIdMember;
	} else {
		$IdMember = $_SESSION['IdMember'];
	}

	$Scope = rtrim(ltrim($_Scope)); // ensure they are no extra spaces 
	if ($Scope != "") {
		if ($Scope {
			0 }
		!= "\"")
		$Scope = "\"" . $Scope . "\""; // add the " " if they are missing 
	}

	// First test if this is the logged in member, and if by luck his right is allready cached in his session variable
	if ((!isset ($_SESSION['Right_' . $RightName])) or 
		($_SYSHCVOL['ReloadRight'] == 'True') or 
		($OptionalIdMember != 0)) {
		    
		    $str = '
SELECT SQL_CACHE Scope, Level
FROM rightsvolunteers, rights
WHERE IdMember=' . $IdMember . ' AND rights.id=rightsvolunteers.IdRight AND rights.Name=\'' . $RightName . '\'';
		
		//$query = mysql_query($str) or bw_error("function HasRight");
		//$right = mysql_fetch_object(mysql_query($str)); // LoadRow not possible because of recusivity
		$rights = $this->dao->query($str);
		$right = $rights->fetch(PDB::FETCH_OBJ);
		if (!isset ($right->Level)) {
			return (0); // Return false if the Right does'nt exist for this member in the DB
		}
		$rlevel = $right->Level;
		$rscope = ltrim(rtrim($right->Scope)); // remove extra space
		if ($OptionalIdMember == 0) { // if its current member cache for next research 
			$_SESSION['RightLevel_' . $RightName] = $rlevel;
			$_SESSION['RightScope_' . $RightName] = $rscope;
		}
	}
	if ($Scope != "") { // if a specific scope is asked
		if ($rscope == "\"All\"") {
			if (($_SESSION["IdMember"]) == 1)
				return (10); // Admin has all rights at level 10
			return ($rlevel);
		} else {
			if ((!(strpos($rscope, $Scope) === false)) or ($Scope == $rscope)) {
				return ($rlevel);
			} else
				return (0);
		}
	} else {
		if (($_SESSION["IdMember"]) == 1)
			return (10); // Admin has all rights at level 10
		return ($rlevel);
	}
}


/**
 * Checks, if the logged on member has any right by searching her
 * in the table rightsvolunteers
 *
 * @return true, if the current user is logged on and
 * exists in table rightsvolunteers
 * Improvment by JeanYves : if the member has not any right, 
 *  a $_SESSION["hasRightAny"]="no" is set, this will allow 
 *  for a faster test at next attempt
 */
public function hasRightAny()
{
	global $_SYSHCVOL;
	
	// Test if in the session cache it is allready said that the member has no right
	if (($_SYSHCVOL['ReloadRight'] != 'True') and 
	     (isset($_SESSION['hasRightAny'])) and 
		 ($_SESSION['hasRightAny']='no') ){
		 
		 return(false) ;		 
	} 

    $A = new MOD_bw_user_Auth();
    if (!$A->isBWLoggedIn()) {
        return false;
    }
    
    $query = '
SELECT SQL_CACHE Level
FROM rightsvolunteers
WHERE IdMember=' . $_SESSION['IdMember'];
    $rights = $this->dao->query($query);
    $right = $rights->fetch(PDB::FETCH_OBJ);
    if (!isset ($right->Level)) {
	 	 $_SESSION["hasRightAny"]="no" ; // Put is session the info that the member has no right
        return false;
    }
    
    return true;
}



    /**
     * FIXME: this is (with little exception) 
     * copy-paste from /htdocs/bw/lib/rights.php; to be improved!
     * 
     * @see /htdocs/bw/lib/rights.php
     */
// -----------------------------------------------------------------------------
// return the Scope in the specific right 
// The funsction will use a cache in session
//   $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
//  from scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter
public function rightScope($RightName, $Scope = "")
{
	global $_SYSHCVOL;

	//if (!IsLoggedIn())
	$A = new MOD_bw_user_Auth();
	if (!$A->isBWLoggedIn()) {
		return false;
	}
	
	$IdMember = $_SESSION['IdMember'];
	if ((!isset ($_SESSION['Right_' . $RightName])) or ($_SYSHCVOL['ReloadRight'] == 'True')) {
		$str = '
SELECT SQL_CACHE Scope, Level
FROM rightsvolunteers,rights
WHERE IdMember=' . $IdMember . '
AND rights.id=rightsvolunteers.IdRight
AND rights.Name=\'' . $RightName . '\'';
		
		$rights = $this->dao->query($str);
		$right = $rights->fetch(PDB::FETCH_OBJ);
		
		if (!isset ($right->Level)) {
			return false;
		}
		$_SESSION['RightLevel_' . $RightName] = $right->Level;
		$_SESSION['RightScope_' . $RightName] = $right->Scope;
	}
	return ($_SESSION['RightScope_' . $RightName]);
}

    
}
?>
