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
require "auth.lib.php";


class MOD_bw_user_Auth extends MOD_user_Auth
{
    private $_immediateRedirect = '';

    /**
     * @param string $sessionName The session key under which the user id may be found
     * @param string $tableName The user table name
     * @param int $authId The authentication id
     */
    public function __construct($sessionName = false, $tableName = false, $authId = false)
    {
        parent::__construct($sessionName, $tableName);
        $this->authId = $authId;
    }

	protected function doLogin( $handle, $password )
 	{
 		$this->logout();

        if (!isset($handle)||!isset($password))
            return false;

        $handle = trim($handle);
        $password = trim($password);

        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle))
            return false;

        if ($this->doBWLogin( $handle, $password ))
        {
        	parent::doLogin( $handle, $password);
        	$this->setupBWSession( $handle );
        	$this->updateUser( $handle, $password );
        	parent::doLogin( $handle, $password);

       		// Sanity check
			if (!$this->isBWLoggedIn('NeedMore,Pending'))
			{
				throw new PException('Login sanity check failed miserably!');
			}

			if ($this->_immediateRedirect !== '') {
			    header("Location: " . $this->_immediateRedirect);
			    exit(0);
			}

        	return true;
        }

		return true;
    }

/*

Note from JeanYves:
I think this routine doBWLogin is obsolete and that now`\build\login\login.model.php does the trick
I have added a Log line to see if it is really obsolete or not
check the logs for "In doBWLogin, jy believe its obsolete"


*/

    protected function doBWLogin1( $handle, $password )
    {
    	global $_SYSHCVOL;

		$Username=$this->dao->escape($handle) ;
		//if (CountWhoIsOnLine() > $_SYSHCVOL['WhoIsOnlineLimit']) {
		//	refuse_login(ww("MaxOnlineNumberExceeded", $_SESSION['WhoIsOnlineCount']), $nextlink,"");
		//}


		// Deal with the username which may have been reused
//		$rr = LoadRow("SELECT Username,ChangedId FROM members WHERE Username='" . $Username . "'");
//		$count = 0;
//		while ($rr->ChangedId != 0) {
//			$rr = LoadRow("SELECT Username,ChangedId FROM members WHERE id=" . $rr->ChangedId);
//			$Username = $rr->Username;
//			$count++;
//			if ($count > 100) {
//				LogStr("Infinite loop in Login with " . $Username, "Bug");
//				break; //
//			}
//		}
		// End of while with the username which may have been reused

		MOD_log::get()->write("In doBWLogin, jy believe its obsolete, if this line appears in logs, it is not", "Login");


		$query = "SELECT id,Status,Username,PassWord FROM members WHERE Username='" . $Username."'" ;
//		. "' AND PassWord = PASSWORD('".$this->dao->escape($password)."')   // note from jy : this I don't want because it can be logged in slow queries !

    	$s = $this->dao->query($query);
		if (!$s)
		{
			throw new PException('Weird shit!');
		}
		if (!$m = $s->fetch(PDB::FETCH_OBJ)) {
			return false;
		}


		if (empty($m->id)) {
			return false;
		}

// Hack from jeanyves to avoid being in a bad situation when tables are locked
		$qry_jyh=mysql_query("select password('".$this->dao->escape($password)."') as PassMysqlEncrypted")  ; // This query will not be locked or slow query
		if (!$qry_jyh) {
		   MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
		   return(false) ;
		}
		$res_jyh=mysql_fetch_object($qry_jyh) ;


		if ($m->PassWord!=$res_jyh->PassMysqlEncrypted) { // Testing if password is OK without doing it in a SqlQuery
		   $strlog="Failed to log with username  <b>".$Username."</b> Agent <b>". $_SERVER['HTTP_USER_AGENT'] . "</b>" ;
// do not uncomment !		  $strlog=$strlog." \$m->PassWord=".$m->PassWord." md5(".$password.")=".md5($password) ;
		   MOD_log::get()->write($strlog, "Login");
		   return(false) ;
		}

		// Write the member's status to the session
		$_SESSION['Status'] = $_SESSION['MemberStatus'] = $m->Status;

		// Process the login of the member according to his status
		switch ($m->Status) {

			case "OutOfRemind" :  // in case an inactive member comes back
				MOD_log::get()->write("Successful login, becoming active again (Was OutOfRemind), with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				$this->dao->query("UPDATE members SET Status='Active' WHERE members.id=".$m->id." AND Status='OutOfRemind'") ;
				$_SESSION['Status'] = $_SESSION['MemberStatus'] = $m->Status='Active' ;
				break ;
			case "Active" :
			case "ActiveHidden" :
            case "ChoiceInactive" :
				 $_SESSION['IdMember']=$m->id ; // this is needed for MOD_log::get, because if not it will not link the log with the right member
				 MOD_log::get()->write("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b> (".$m->Username.")", "Login");
				 break ;

			case "ToComplete" :
				MOD_log::get()->write("Login with (tocomplete)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				// FIXME: completeprofile.php does not exist - why used here? (steinwinde 2007-12-05)
				header("Location: " . PVars::getObj('env')->baseuri . "bw/completeprofile.php");
				exit(0);

			case "MailToConfirm" :  // I just add this here in case someone try to log with mail to confirm
				MOD_log::get()->write("Login with (MailToConfirm)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				return false ;
				// exit(0);
				break;

			case "NeedMore" :
                $_SESSION['IdMember']=$m->id ;
                $_SESSION['Status'] = $_SESSION['MemberStatus'] = $m->Status='NeedMore' ;
				MOD_log::get()->write("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			    $this->_immediateRedirect = PVars::getObj('env')->baseuri . "bw/updatemandatory.php";
				// exit(0);
				break;

			case "Banned" :
			case "TakenOut" :
			case "CompletedPending" :
			case "SuspendedBeta" :
				 MOD_log::get()->write("Loging Refused because of status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				return false ;
			    break ;

			case "Pending" :
                $_SESSION['IdMember']=$m->id ;
//				MOD_log::get()->write("Member ".$m->username." is trying to log while in status ".$m->Status." Log has failed","Login") ;
				// !!!!!!!!!!!!!! todo display here (ticket #208) the content of word ApplicationNotYetValid
				break ;
			default:
				 MOD_log::get()->write("Loging Refused because of unknown status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				return false;
		}

		return true;
    }

    protected function setupBWSession( $handle )
    {
    	$query = "SELECT * FROM members WHERE Username='" . $handle . "'";
    	$s = $this->dao->query($query);
		if (!$s)
			throw new PException('Weird stuff!');

		if (!$m = $s->fetch(PDB::FETCH_OBJ))
			throw new PException('Weird stuff!');

		// Set the session identifier
		$_SESSION['IdMember'] = $m->id;
		$_SESSION['Username'] = $m->Username;
		$_SESSION['Status'] = $_SESSION['MemberStatus'] = $m->Status;

		if ($_SESSION['IdMember'] != $m->id)
		{ // Check is session work of
			$this->logout();
			throw new PException('Login sanity check failed miserably!');
		}; // end Check is session work of

		$_SESSION['MemberCryptKey'] = crypt($m->PassWord, "rt"); // Set the key which will be used for member personal cryptation
		$_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']); // Set the key for checking id and LogCheck (will be restricted in future)

		$this->dao->query("UPDATE members SET LogCount=LogCount+1,LastLogin=now(),NbRemindWithoutLogingIn=0 WHERE id=" . $_SESSION['IdMember']); // update the LastLogin date
				 MOD_log::get()->write("Is this dead code ? We are in bwauth.lib.php setupBWSession, if so it is redudant somewhere ...", "Debug");


		// Load language prederence (IdPreference=1)
		$s = $this->dao->query("SELECT memberspreferences.Value,ShortCode FROM memberspreferences,languages WHERE IdMember=" . $_SESSION['IdMember'] . " AND IdPreference=1 AND memberspreferences.Value=languages.id");
		if (!$s)
		{
			throw new PException('Weird stuff!');
		}
		$langprefs = $s->fetch(PDB::FETCH_OBJ);

		if (isset ($langprefs->Value))
		{ // If there is a member selected preference set it
			$_SESSION["IdLanguage"] = $langprefs->Value;
			$_SESSION["lang"] = 	  $langprefs->ShortCode;
		}

    	// Process the login of the member according to his status
		switch ($m->Status) {
			case "Active" :
			case "ActiveHidden" :
			case "NeedMore" :
			case "Pending" :
            case "ChoiceInactive" :
            	//if (HasRight("Words"))
				//	$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
				break;

			default:
				throw new PException('SetupBWSession Weird Status!');
				break;
		}
    }


    protected function updateUser($handle, $password)
    {
        $pwenc = MOD_user::passwordEncrypt($password);
        $Auth = new MOD_user_Auth;
        $authId = $Auth->checkAuth('defaultUser');
        $query = '
UPDATE `user` SET
    `auth_id`='.(int)$authId.',
    `pw`=\''.$this->dao->escape($pwenc).'\'
WHERE
    `handle`=\''.$this->dao->escape($handle).'\'
';
        if(!$this->dao->exec($query)) {
            $query = '
REPLACE into `user`
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`)
VALUES
(
    '.$_SESSION['IdMember'].',
    '.(int)$authId.',
    \''.$this->dao->escape($handle).'\',
    \'\',
    \''.$this->dao->escape($pwenc).'\',
    1
)
            ';
            $s = $this->dao->query($query);
        }
    }

/**
* check if the user is a logged in member
* @$ExtraAllowedStatus allows for a list, comma separated of extra status which can
*  be allowed for members in addition to the basic Active and ActiveHidden members.Status
* this means that in the default case :
* 		(IsLoggedIn()) will return true only if the member has a session
* 		with an IdMember and a Status like Active or ActiveHidden
* in the extended cases
* 		(IsLoggedIn("Pending")) will also return true if the member has a
*      a status set to Pending, this allow to give specific access to
* 		other members than the one with Active or ActiveHiddend Status
*
* @return boolean
*/
	static function isBWLoggedIn($ExtraAllowedStatus="") {
		if (empty($_SESSION['IdMember'])) {
			return false;
		}

		if (empty($_SESSION['MemberCryptKey'])) {
			return false;
		}

		if ($_SESSION['LogCheck'] != Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']))
		{
			$this->logout();
			return false;
		}

		if (empty($_SESSION["MemberStatus"])) {
			$strerror="Members with IdMember=".$_SESSION["IdMember"]. " has no \$_SESSION[\"MemberStatus\"]" ;
			error_log($strerror) ;
			MOD_log::get()->write($strerror, "Debug");
			die ($strerror) ;
		}

        if ($_SESSION["MemberStatus"]=='Active') {
            return (true) ;
        }

        if ($_SESSION["MemberStatus"]=='ChoiceInactive') {
            return (true) ;
        }

        if ($_SESSION["MemberStatus"]=='ActiveHidden') {
			return (true) ;
		}

		if (!empty($ExtraAllowedStatus)) { // are there allowed exception ?
			if (!isset($_SESSION["MemberStatus"])) {
				$ret=print_r($_SESSION,true) ;
				die("no \$_SESSION[\"MemberStatus\"] in IsLoggedIn() "."<br />\n".$ret) ;
			}
			$tt = explode(",", str_replace(";",",",$ExtraAllowedStatus));
			if ((count($tt)>0) and (in_array($_SESSION["MemberStatus"],$tt))) {
				return(true) ;
			}
		}

		return (false) ;
	} // end of isBWLoggedIn


	function logout()
	{
		if (isset($_SESSION['IdMember'])) {
			MOD_log::get()->write("Logout in bwauth.lib.php", "Login");


			// todo optimize periodically online table because it will be a gruyere
			// remove from online list
			$query = "delete from online where IdMember=" . $_SESSION['IdMember'];
			$this->dao->query($query);
		}

		unset($_SESSION['IdMember']);
		unset($_SESSION['IsVol']);
		unset($_SESSION['Username']);
		unset($_SESSION['MemberStatus']);
		unset($_SESSION['Status']);
		unset($_SESSION["stylesheet"]) ;
		if (isset($_SESSION['Param']))
			unset($_SESSION["Param"]) ;
		if (isset($_SESSION['TimeOffset']))
			unset($_SESSION["TimeOffset"]) ;
		if (isset($_SESSION['PreferenceDayLight']))
			unset($_SESSION["PreferenceDayLight"]) ;
		if (isset($_SESSION['MemberCryptKey']))
			unset($_SESSION['MemberCryptKey']);
		if (isset($_SESSION['LogCheck']))
			unset($_SESSION['LogCheck']);

		foreach ($_SESSION as $key => $name) {
			if (strpos($key,"RightLevel")!==false) {
				unset($_SESSION[$key]) ;
			}
			if (strpos($key,"RightScope")!==false) {
				unset($_SESSION[$key]) ;
			}
			if (strpos($key,"FlagLevel")!==false) {
				unset($_SESSION[$key]) ;
			}
//			if (isset($_SESSION[$key])) print_r( $key ); echo " "; print_r( $name ); echo "<br />\n" ;
		}
//		die(0) ;

		//$_SESSION = array() ; // Raz the session properly , beware not compatible with signup

		parent::logout();
	}

 }
?>
