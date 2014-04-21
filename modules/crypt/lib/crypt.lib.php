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
 * Build an array with the last visits on the member IdMember
  * assumed it is the currently logged member if no IdMember is provided
 * @author Philipp Lange
 */
class MOD_crypt {


    /**
     * Singleton instance
     *
     * @var MOD_trips
     * @access private
     */
    private static $_instance;

    public function __construct()
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

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function enc($function,$ss)
    {
        require_once SCRIPT_BASE.'inc/enc.inc.php';
        switch ($function) {
            case 'CryptA':
                return CryptA($ss);
            case 'CryptM':
                return CryptM($ss);
            case 'DeCryptA':
                return DeCryptA($ss);
            case 'DeCryptM':
                return DeCryptM($ss);
            default:
                return false;
        }
    }


    /**
     * getall_crypted
     *
     * Equals the old BW style function "PublicReadCrypted"
     * Get a decrypted value for a given crypted_id
     */
    public static function getall_crypted($IdCrypt, $return_value)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ".$crypt_db."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

        if ($rr != NULL && sizeof($rr) > 0)
        {
            if ($rr->IsCrypted == "not crypted") {
                return $rr->MemberCryptedValue;
            }
            if ($rr->MemberCryptedValue == "" || $rr->MemberCryptedValue == 0) {
                return (""); // if empty no need to send crypted
            }
        	if ($_SESSION["IdMember"] == $rr->IdMember) {
        		//	  echo $rr->MemberCryptedValue,"<br>";
        		return (self::GetDeCryptM($rr->MemberCryptedValue));
        	}
            if ($rr->IsCrypted == "crypted") {
                return ($return_value);
            }
        }
        /*elseif(sizeof($rr) > 0) {
            return ("");
        }*/
        else {
            return ($return_value);
        }
    }



    /**
     * get_crypted
     *
     * Equals the old BW style function "PublicReadCrypted"
     * Get a decrypted value for a given crypted_id
     */
    public static function get_crypted($IdCrypt, $return_value)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ".$crypt_db."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

        if ($rr != NULL && sizeof($rr) > 0)
        {
            if ($rr->IsCrypted == "not crypted") {
                return $rr->MemberCryptedValue;
            }
            if ($rr->MemberCryptedValue == "" || $rr->MemberCryptedValue === 0) {
                return (""); // if empty no need to send crypted
            }
            if ($rr->IsCrypted == "crypted") {
                return ($return_value);
            }
        }
        /*elseif(sizeof($rr) > 0) {
            return ("");
        }*/
        else {
			if ($return_value == "") {
				$ww= new MOD_words();
				return ($ww->getFormatted("cryptedhidden"));
			}
			else {
				return ($return_value);
			}
        }
    }

    /**
     * insertCrypted
     *
     * @param $TableComumn must be something like "members.ProfileSummary"
     * @param $Idrecord is the id of the record in the corresponding $TableColumn,
     * it's not normalized but needed for mainteance
     * @return insertId()
     */
    public static function insertCrypted($ss, $TableColumn, $IdRecord, $IdMember = false, $IsCrypted = "crypted")
    {
    	if (!$ss)
    		return (0); // Don't insert null values
    	if (!$IdMember)
            $IdMember = $_SESSION['IdMember'];
        $ssA = self::GetCryptA($ss);
        $ssM = self::GetCryptM($ss,$IsCrypted);
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $query = '
INSERT INTO '.$crypt_db.'cryptedfields
(
	`AdminCryptedValue`,
	`MemberCryptedValue`,
	`IdMember`,
	`IsCrypted`,
	`TableColumn`,
	`IdRecord`
)
VALUES
(
	\'' . $ssA . '\',
	\'' . $ssM . '\',
	\'' . $IdMember . '\',
	\'' . $IsCrypted . '\',
	\'' . $TableColumn . '\',
	\'' . $IdRecord . '\'
)';
        $cryptedfields = self::get()->dao->query($query);
        return $cryptedfields->insertId();
    }

    /**
     * MemberCrypt
     *
     * allows a member to Crypt his data (to update it)
     *
     * @param $TableComumn must be something like "members.ProfileSummary"
     * @param $Idrecord is the id of the record in the corresponding $TableColumn,
     * it's not normalized but needed for mainteance
     * @return insertId()
     */
    public static function MemberCrypt($IdCrypt)
    {
        $IdMember = $_SESSION['IdMember'];
        $ssA = self::GetCryptA($ss);
        $ssM = self::GetCryptM($ss, $IsCrypted);
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        if (!$rr = self::get_crypted($IdCrypt, false))
            return false;
        $ssM = self::GetCryptM($rr);
        $query = "

UPDATE
    ". $crypt_db ."cryptedfields
SET
    IsCrypted = 'crypted',
    MemberCryptedValue = '" . $ssM . "'
WHERE
    IsCrypted = 'not crypted',
    IdMember = '" . $IdMember . "',
    id = ". (int)$IdCrypt
    ;

        self::get()->dao->query($query);

    }


    /**
     * MemberCrypt
     *
     * allows a member to DeCrypt his data (to update it)
     *
     * @param $TableComumn must be something like "members.ProfileSummary"
     * @param $Idrecord is the id of the record in the corresponding $TableColumn,
     * it's not normalized but needed for mainteance
     * @return insertId()
     */
    public static function MemberDeCrypt($IdCrypt = false)
    {
        if (!$IdCrypt || $IdCrypt == '')
            return ('');
        $IdMember = $_SESSION['IdMember'];
        $crypt_db = PVars::getObj('syshcvol')->Crypted;

        $rr = self::get()->dao->query("
SELECT
    MemberCryptedValue
FROM
    ".$crypt_db."cryptedfields
WHERE
    IdMember = " . $IdMember . "
AND id = " . $IdCrypt
        )->fetch(PDB::FETCH_OBJ);

        if (!$rr = self::get_crypted($IdCrypt, false))
            return false;
        $ssM = self::GetDeCryptM($rr);
        $query = "

UPDATE
    ". $crypt_db ."cryptedfields
SET
    IsCrypted = 'not crypted',
    MemberCryptedValue = '" . $ssM . "'
WHERE
    IsCrypted = 'crypted',
    IdMember = '" . $IdMember . "',
    id = ". (int)$IdCrypt
    ;

        self::get()->dao->query($query);

    }

    /**
     * IsCrypted
     * Get a decrypted value for a given crypted_id
     */
    public function IsCrypted($IdCrypt)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

    	if (!$IdCrypt)
    		return (false); // if no value, it is not crypted
        if ($rr != NULL && sizeof($rr) > 0)
        {
        	switch ($rr->IsCrypted) {
        		case "not crypted" :
        			return (false);
        		case "crypted" :
        			return (true);
        		case "always" :
        			return (true);
        		default :
        			return (true);

        	}
        }
    }

    /**
     * AdminReadCrypted
     * Reads the crypted fields
     */
    public static function AdminReadCrypted($IdCrypt = false)
    {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        if (!$IdCrypt || $IdCrypt == '')
            return ('');
        $crypted_id = (int)$IdCrypt;
        // TODO: limit this to a right 'decrypt' or similar
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $crypted_id
            "
        )->fetch(PDB::FETCH_OBJ);
    	if (!$rr)
    		return (false); // if no value, it is not crypted
        return (self::GetDeCryptA($rr->AdminCryptedValue));
    }

    // TODO: COMPLETE BELOW!

    //------------------------------------------------------------------------------
    // MemberReadCrypted read the crypt field
    // return the plain text if the current member is the owner of the crypted object
    // If not return standard "is crypted text"
    // todo : complete this function
    public static function MemberReadCrypted($IdCrypt) {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
    	if ($IdCrypt == 0)
    		return (""); // if 0 it mean that the field is empty
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);
    	if (!$rr)
    		return (false); // if no value, it is not crypted
    	if (isset($_SESSION["IdMember"]) && $_SESSION["IdMember"] == $rr->IdMember) {
    		//	  echo $rr->MemberCryptedValue,"<br>";
    		return (self::GetDeCryptM($rr->MemberCryptedValue));
    	} else {
    		if ($rr->MemberCryptedValue == "")
    			return (""); // if empty no need to send crypted
    		return ("cryptedhidden");
    	}
    } // end of MemberReadCrypted


    /**
     * ReverseCrypt
     *
     * @returns "decrypt" if $IdCrypt correspond to a crypt field
     * @returns "crypt" if $IdCrypt correspond to a not crypted field
     * it's used to propose the proper option for the layout, no action on DB required here
     */
    public static function ReverseCrypt($IdCrypt) {
    	if (self::IsCrypted($IdCrypt))
    		return "decrypt";
    	else
    		return "crypt";
    }

    /**
     * NewReplaceInCrypted
     *
     * Allows to replace an entry in cryptedfields
     *
     * @param $TableComumn must be something like "members.ProfileSummary"
     * @param $Idrecord is the id of the record in the corresponding $TableColumn,
     * it's not normalized but needed for mainteance
     * @return insertId()
     */
    public static function NewReplaceInCrypted($ss,$TableColumn,$IdRecord, $IdCrypt, $IdMember = false, $IsCrypted = "crypted") {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
    	if (!$ss)
    		return false; // Don't insert null values
    	if (!$IdMember)
            $IdMember = $_SESSION['IdMember'];
    	if ($IdCrypt == 0) {
    		return (self::insertCrypted($ss,$TableColumn,$IdRecord, $IdMember, $IsCrypted)); // Create a new entry
    	}
        $rr = self::get()->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

    	if (!$rr)
    		return (self::insertCrypted($ss,$TableColumn,$IdRecord, $IdMember, $IsCrypted)); // Create a new entry

    	// TODO: manage cryptation, manage IdMember when it is not the owner of the record (in this case he must have the proper right)
        // Micha: What exactly do we need?
        $ssA = self::GetCryptA($ss);
        $ssM = self::GetCryptM($ss,$IsCrypted);
        $query = "

UPDATE
    ". $crypt_db ."cryptedfields
SET
    TableColumn = '" . $TableColumn . "',
    IsCrypted = '" . $IsCrypted . "',
    IdRecord = '" . $IdRecord . "',
    AdminCryptedValue = '" . $ssA . "',
    MemberCryptedValue = '" . $ssM . "'
WHERE
    id = ". (int)$rr->id ." AND
    IdMember = '" . $rr->IdMember . "'"
    ;

        self::get()->dao->query($query);
    	return $IdCrypt;
    } // end of NewReplaceInCrypted



    /**
     * GetCryptA
     *
     * @param string
     * @returns the crypted value of $ss according to admin cryptation algorithm
     */
    private function GetCryptA($ss)
    {
        if (strstr($ss,"<admincrypted>") !== false)
            return($ss);

        // TODO: Add a test for a specific right
        return ("<admincrypted>".self::enc('CryptA',$ss)."</admincrypted>");
    }

    /**
     * GetDeCryptA
     *
     * @param string
     * @returns the decrypted value of $ss according to admin cryptation algorithm
     */
    private function GetDeCryptA($ss)
    {
        if (strstr($ss,"<admincrypted>") === false)
            return($ss);
        $res = strip_tags($ss);
        // TODO: Add a test for a specific right
        return self::enc('DeCryptA', $res);
    }

    /**
     * IsCryptedValue
     *
     * @param string
     * @returns the content of the IsCrypted field if data is crypted
     */
    public static function IsCryptedValue($IdCrypt)
    {
    	global $_SYSHCVOL; // use global vars
    	if (!$IdCrypt)
    		return ("not crypted"); // if no value, it is not crypted
    	$IdMember = $_SESSION['IdMember'];
    	$rr = MyLoadRow("select SQL_CACHE * from ".PVars::getObj('syshcvol')->Crypted."cryptedfields where id=" . $IdCrypt);
    	return($rr->IsCrypted) ;
    } // end of IsCryptedValue

    /**
     * GetCryptM
     *
     * @param string
     * @returns the crypted value of $ss according to member cryptation algorithm
     */
    private function GetCryptM($ss, $IsCrypted = "crypted") {
        switch ($IsCrypted) {
             case "crypted" :
             case "always" :
                if (strstr($ss,"<membercrypted>") !== false)
                    return($ss);
                // TODO: Add a test for a specific right
                return ("<membercrypted>".self::enc('CryptM',$ss)."</membercrypted>");
                break;
             case "not crypted" :
                return(strip_tags($ss));
                break ;
             default : // we should never come here
                $strlog="function MOD_crypt::GetCryptM() Problem to crypt ".$ss." IsCrypted=[".$IsCrypted."]" ;
                if (function_exists(LogStr)) {
                      LogStr($strlog,"Bug") ;
                }
                if (function_exists(bw_error)) {
                      bw_error($strlog) ;
                }
                else {
                   error_log($strlog) ;
                }
                die ("Major problem with crypting issue") ;
        }
    } // end of GetCryptM


    /**
     * GetDeCryptM
     *
     * @param string
     * @returns the decrypted value of $ss according to member cryptation algorithm
     */
    private function GetDeCryptM($ss) {
        if (strstr($ss, "<membercrypted>") === false)
            return ($ss);
        $res = strip_tags($ss);
        // todo add right test
        return self::enc('DeCryptM',$res);
    } // end of GetDeCryptM

} // end of MOD_crypt
?>