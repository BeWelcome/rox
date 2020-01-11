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

use App\Utilities\SessionTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Build an array with the last visits on the member IdMember
  * assumed it is the currently logged member if no IdMember is provided
 * @author Philipp Lange
 */
class MOD_crypt {
    use SessionTrait;

    /** @var PDB */
    private $dao;

    /**
     * MOD_crypt constructor.
     * @throws Exception
     * @throws PException
     */
    public function __construct()
    {
        $this->setSession();
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
     * @return string|boolean
     */
    public function enc($function,$ss)
    {
        switch ($function) {
            case 'CryptA':
                return urlencode($ss);
            case 'CryptM':
                return urlencode($ss);
            case 'DeCryptA':
                return urldecode($ss);
            case 'DeCryptM':
                return urldecode($ss);
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
    public function getall_crypted($IdCrypt, $return_value)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = $this->dao->query(
            "
SELECT *
FROM ".$crypt_db."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

        if ($rr)
        {
            if ($rr->IsCrypted == "not crypted") {
                return $rr->MemberCryptedValue;
            }
            if ($rr->MemberCryptedValue == "" || $rr->MemberCryptedValue == 0) {
                return (""); // if empty no need to send crypted
            }
        	if ($this->session->get("IdMember") == $rr->IdMember) {
        		//	  echo $rr->MemberCryptedValue,"<br>";
        		return (self::GetDeCryptM($rr->MemberCryptedValue));
        	}
            if ($rr->IsCrypted == "crypted") {
                return ($return_value);
            }
        }
        return ($return_value);
    }



    /**
     * get_crypted
     *
     * Equals the old BW style function "PublicReadCrypted"
     * Get a decrypted value for a given crypted_id
     */
    public function get_crypted($IdCrypt, $return_value)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = $this->dao->query(
            "
SELECT *
FROM ".$crypt_db."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

        if ($rr != null)
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
    public function insertCrypted($ss, $TableColumn, $IdRecord, $IdMember = false, $IsCrypted = "crypted")
    {
    	if (!$ss)
    		return (0); // Don't insert null values
    	if (!$IdMember)
            $IdMember = $this->session->get('IdMember');
        $ssA = $this->GetCryptA($ss);
        $ssM = $this->GetCryptM($ss,$IsCrypted);
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
        $cryptedfields = $this->dao->query($query);
        return $cryptedfields->insertId();
    }

    /**
     * IsCrypted
     * Get a decrypted value for a given crypted_id
     */
    public function IsCrypted($IdCrypt)
    {
        $IdCrypt = (int)$IdCrypt;
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        $rr = $this->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);

    	if (!$IdCrypt)
    		return (false); // if no value, it is not crypted
        if ($rr)
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
    public function AdminReadCrypted($IdCrypt = false)
    {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
        if (!$IdCrypt || $IdCrypt == '')
            return ('');
        $crypted_id = (int)$IdCrypt;
        // TODO: limit this to a right 'decrypt' or similar
        $rr = $this->dao->query(
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
    public function MemberReadCrypted($IdCrypt) {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
    	if ($IdCrypt == 0)
    		return (""); // if 0 it mean that the field is empty
        $rr = $this->dao->query(
            "
SELECT *
FROM ". $crypt_db ."cryptedfields
WHERE id = $IdCrypt
            "
        )->fetch(PDB::FETCH_OBJ);
    	if (!$rr)
    		return (false); // if no value, it is not crypted
    	if ($this->session->has( "IdMember" ) && $this->session->get("IdMember") == $rr->IdMember) {
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
     * @param string $TableComumn must be something like "members.ProfileSummary"
     * @param int $Idrecord is the id of the record in the corresponding $TableColumn,
     * it's not normalized but needed for mainteance
     * @return int insertId()
     */
    public function NewReplaceInCrypted($ss,$TableColumn,$IdRecord, $IdCrypt, $IdMember = false, $IsCrypted = "crypted") {
        $crypt_db = PVars::getObj('syshcvol')->Crypted;
    	if (!$ss)
    		return false; // Don't insert null values
    	if (!$IdMember)
            $IdMember = $this->session->get('IdMember');
    	if ($IdCrypt == 0) {
    		return (self::insertCrypted($ss,$TableColumn,$IdRecord, $IdMember, $IsCrypted)); // Create a new entry
    	}
        $rr = $this->dao->query(
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
        $ssA = $this->GetCryptA($ss);
        $ssM = $this->GetCryptM($ss,$IsCrypted);
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

        $this->dao->query($query);
    	return $IdCrypt;
    } // end of NewReplaceInCrypted



    /**
     * GetCryptA
     *
     * @param string
     * @returns string crypted value of $ss according to admin cryptation algorithm
     */
    private function GetCryptA($ss)
    {
        if (strstr($ss,"<admincrypted>") !== false)
            return($ss);

        // TODO: Add a test for a specific right
        return ("<admincrypted>".$this->enc('CryptA',$ss)."</admincrypted>");
    }

    /**
     * GetDeCryptA
     *
     * @param string
     * @returns string decrypted value of $ss according to admin cryptation algorithm
     */
    private function GetDeCryptA($ss)
    {
        if (strstr($ss,"<admincrypted>") === false)
            return($ss);
        $res = strip_tags($ss);
        // TODO: Add a test for a specific right
        return $this->enc('DeCryptA', $res);
    }

    /**
     * GetCryptM
     *
     * @param string
     * @returns string crypted value of $ss according to member cryptation algorithm
     */
    private function GetCryptM($ss, $IsCrypted = "crypted") {
        switch ($IsCrypted) {
             case "crypted" :
             case "always" :
                if (strstr($ss,"<membercrypted>") !== false)
                    return($ss);
                // TODO: Add a test for a specific right
                return ("<membercrypted>".$this->enc('CryptM',$ss)."</membercrypted>");
                break;
             case "not crypted" :
                return(strip_tags($ss));
                break ;
             default : // we should never come here
                $strlog="function MOD_crypt::GetCryptM() Problem to crypt ".$ss." IsCrypted=[".$IsCrypted."]" ;
                error_log($strlog) ;
                die ("Major problem with crypting issue");
        }
    } // end of GetCryptM


    /**
     * GetDeCryptM
     *
     * @param string
     * @returns string decrypted value of $ss according to member cryptation algorithm
     */
    private function GetDeCryptM($ss) {
        if (strstr($ss, "<membercrypted>") === false)
            return ($ss);
        $res = strip_tags($ss);
        // todo add right test
        return $this->enc('DeCryptM',$res);
    } // end of GetDeCryptM

} // end of MOD_crypt
?>
