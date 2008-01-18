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
 * admin model
 *
 * @package admin
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class Admin extends PAppModel
{
    
    protected $dao;
    
    public function __construct()
    {
        parent::__construct();
    }

    public function procActivitylogs($vars, $level = 0)
    {
        
		$where = '';
		$username = $vars["username"];

		$cid = $this->_idMember($username);
		if ($level <= 1) {
			$cid = $_SESSION["IdMember"]; // Member with level 1 can only see his own rights
		}
		if ($cid != 0) {
			$where .= " AND IdMember=" . $cid;
		}
		
		$R = MOD_right::get();
		$level = $R->hasRight('Logs');
		
		
		$limitcount=$vars["limitcount"]; // Number of records per page
		$start_rec=$vars["start_rec"]; // Number of records per page
		
		
		$andS1 = $vars["andS1"];
		if ($andS1 != "") {
			$where .= " AND Str LIKE '%" . $andS1 . "%'";
		}
		
		$andS2 = $vars["andS2"];
		if ($andS2 != "") {
			$where .= " AND Str LIKE '%" . $andS2 . "%'";
		}
		
		$notAndS1 = $vars["notAndS1"];
		if ($notAndS1 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS1 . "%'";
		}
		
		$notAndS2 = $vars["notAndS2"];
		if ($notAndS2 != "") {
			$where .= " AND Str NOT LIKE '%" . $notAndS2 . "%'";
		}
		
		$ip = $vars["ip"];
		if ($ip != "") {
			$where .= " AND IpAddress=" . ip2long($ip) . "";
		}
		
		$type = $vars["type"];
		if ($type != "") {
			$where .= " AND Type='" . $type . "'";
		}
		
		// If there is a Scope limit logs to the type in this Scope (unless it his own logs)
		if (!$R->hasRight('Logs', "\"All\"")) {
			$scope = RightScope("Logs");
			str_replace($scope, "\"", "'");
			$where .= " AND (Type IN (" . $scope . ") OR IdMember=" . $_SESSION["IdMember"] . ") ";
		}
		
		$tData = array ();
		$db = "";
		if (!empty($_SYSHCVOL['ARCH_DB'])) {
		    $db = $_SYSHCVOL['ARCH_DB'] . ".";
		}
		
		// not using: SQL_CALC_FOUND_ROWS and FOUND_ROWS()
		$query = "SELECT logs.*, Username " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
		        "WHERE 1=1 " . $where . " " .
		        "ORDER BY created DESC LIMIT $start_rec," . $limitcount;
		$resultRecords = $this->dao->query($query);
		
		$query = "SELECT COUNT(*) AS n " .
		        "FROM " . $db . ".logs LEFT JOIN members ON members.id=logs.IdMember " . 
		        "WHERE 1=1 " . $where;
		$result = $this->dao->query($query);
		$altogether = $result->fetch(PDB::FETCH_OBJ);
		
		return array($altogether->n => $resultRecords);
    }

    /**
     * FIXME: more or less a copy from method IdMember($username) - improve!
     * 
     * FIXME: move to dedicated module
     *
     * @see FunctionsTools.php
     * @param unknown_type $username
     * @return unknown
     */
    //------------------------------------------------------------------------------ 
// function IdMember return the numeric id of the member according to its username
// This function will TARNSLATE the username if the profile has been renamed.
// Note that a numeric username is provided no Username trnslation will be made
function _idMember($username) {
	if (is_numeric($username)) { // if already numeric just return it
		return ($username);
	}
	$query = "select SQL_CACHE id,ChangedId,Username,Status from members where Username='" . addslashes($username) . "'";
	$rr = LoadRow($query);
	if (!isset($rr->id)) return(0) ; // Return 0 if no username match
	if ($rr->ChangedId > 0) { // if it is a renamed profile
		$rRenamed = LoadRow("select SQL_CACHE id,Username from members where id=" . $rr->ChangedId);
		$rr->id = IdMember($rRenamed->Username); // try until a not renamde profile is found
	}
	if (isset ($rr->id)) {
	    // test if the member is the current member and has just bee rejected (security trick to immediately remove the current member in such a case)
		if (array_key_exists("IdMember", $_SESSION) and $rr->id==$_SESSION["IdMember"]) $this->_testIfIsToReject($rr->Status) ;
		return ($rr->id);
	}
	return (0);
} // end of IdMember

/**
 * FIXME: more or less a copy from method TestIfIsToReject($Status) - improve!
 * 
 * FIXME: move to dedicated module
 * 
 * @see FunctionsTools.php
 */

// THis TestIfIsToReject function check wether the status of the members imply an immediate logoff
// This for the case a member has just been banned
// the $Status of the member is the current status from the database
function _testIfIsToReject($Status) {
	 if (($Status=='Rejected ')or($Status=='Banned')) { 
		$L = MOD_log::get();
		$L->write("Force Logout GAMEOVER", "Login");
		APP_User::get()->logout();
		die(" You can't use this site anymore") ;
	 }
} // end of funtion IsToReject

public function wordsdownload() {
    $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
    PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
    if (!PPostHandler::isHandling())
        return $callbackId;

    $vars = &PPostHandler::getVars($callbackId);

    if(array_key_exists('SubmitUpload', $vars)) $upload = true;
    else $upload = false;

    if(array_key_exists('importfilename', $vars)) $importfilename = $vars['importfilename'];

    if(array_key_exists('Replace', $vars)) $replace = true;
    else $replace = false;

    PPostHandler::clearVars($callbackId);

    if($upload) {
        exec("mysql.exe -u root bewelcome < \"$importfilename\"");
        echo "<H3>Import complete</H3>";
        PPHP::PExit();
    }
    $fields = "";
    $qry = $this->dao->query("describe words");
	while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
        $name = $rr->Field;
        $fields .= "`$name`, ";
    }
    $fields = substr($fields, 0, strlen($fields)-2);
    if($replace) $results = "REPLACE";
    else $results = "INSERT";
    $results .= " INTO `words` ($fields) VALUES \r\n";
    $qry = $this->dao->query("select $fields from words");
	while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
        $results .= "(";
        $line = "";
        foreach($rr as $key => $r) {
            if(substr($key, 0, 2) == "Id")
                $line .= "$r, ";
            else
                $line .= "'".mysql_real_escape_string($r)."', ";
        }
        $results .= substr($line, 0, strlen($line)-2)."),\r\n";
    }
    $results = substr($results, 0, strlen($results)-3).";\r\n";
    $results = gzencode($results);

    header("Content-length: ".strlen($results));
    header("Content-type: application/x-gzip");
    header("Content-Disposition: attachment; filename=words.sql.gzip");
    echo $results;
    PPHP::PExit();
}
}
?>