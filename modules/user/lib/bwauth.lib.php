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
			if (!$this->isBWLoggedIn())
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

    protected function doBWLogin( $handle, $password )
    {
    	global $_SYSHCVOL;
	
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
	
		$query = "SELECT id,Status,Username,PassWord FROM members WHERE Username='" . $this->dao->escape($handle)."'" ;
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
		$qry_jyh=mysql_query("select password(".$this->dao->escape($password).") as PassMysqlEncrypted")  ; // THis query will not be locked or slow query
		if (!$qry_jyh) {
		   MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
		   return(false) ;
		}
		$res_jyh=mysql_fetch_object($qry_jyh) ; 
		

		if ($m->PassWord!=$res_jyh->PassMysqlEncrypted) { // Testing if password is OK without doing it in a SqlQuery
		   $strlog="Failed to log with username  <b>".$handle."</b> Agent <b>". $_SERVER['HTTP_USER_AGENT'] . "</b>" ;
// do not uncomment !		  $strlog=$strlog." \$m->PassWord=".$m->PassWord." md5(".$password.")=".md5($password) ;
		   MOD_log::get()->write($strlog, "Login");
		   return(false) ;
		}
					
		// Process the login of the member according to his status
		switch ($m->Status) {

			case "ChoiceInactive" :  // in case an inactive member comes back
				MOD_log::get()->write("Successful login, becoming active again, with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				$this->dao->query("UPDATE members SET Status='Active' WHERE members.id=".$m->id." AND Status='ChoiceInactive'") ;
				$_SESSION['Status'] = $m->Status='Active' ;
				break ;
			case "Active" :
			case "ActiveHidden" :
				 MOD_log::get()->write(" MOD_log::get Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				 break ;
			
			case "ToComplete" :
				MOD_log::get()->write("Login with (tocomplete)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				// FIXME: completeprofile.php does not exist - why used here? (steinwinde 2007-12-05)
				header("Location: " . PVars::getObj('env')->baseuri . "bw/completeprofile.php");
				exit(0);
	
			case "MailToConfirm" :  // I just add this here in case someone try to log with maul to confirm
				MOD_log::get()->write("Login with (MailToConfirm)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				return false ;
				// exit(0);
				break;
	
			case "NeedMore" :
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
//				MOD_log::get()->write("Member ".$m->username." is trying to log while in status ".$m->Status." Log has failed","Login") ;
				// !!!!!!!!!!!!!! todo display here (ticket #208) the content of word ApplicationNotYetValid
				return false ;
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
		$_SESSION['Status'] = $m->Status;
		
		if ($_SESSION['IdMember'] != $m->id)
		{ // Check is session work of
			$this->logout();
			throw new PException('Login sanity check failed miserably!');
		}; // end Check is session work of
	
		$_SESSION['MemberCryptKey'] = crypt($m->PassWord, "rt"); // Set the key which will be used for member personal cryptation
		$_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember']); // Set the key for checking id and LohCheck (will be restricted in future)
	
		$this->dao->query("UPDATE members SET LogCount=LogCount+1,LastLogin=now() WHERE id=" . $_SESSION['IdMember']); // update the LastLogin date
	
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
			case "ChoiceInactive" :  // in case an inactive member comes back
				$this->dao->query("UPDATE members SET Status='Active' WHERE members.id=".$m->id." AND Status='ChoiceInactive'") ;
				$_SESSION['Status'] = $m->Status='Active' ;
			case "Active" :
			case "ActiveHidden" :
			case "NeedMore" :
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
INSERT `user`
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`)
VALUES
(
    '.$this->dao->nextId('user').',
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
    
	function isBWLoggedIn() 
	{
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
		return true;
	} 
    
	function logout()
	{
		if (isset($_SESSION['IdMember'])) 
		{
			MOD_log::get()->write("Logout", "Login");

				
			// todo optimize periodically online table because it will be a gruyere 
			// remove from online list
			$query = "delete from online where IdMember=" . $_SESSION['IdMember'];
			$this->dao->query($query);
	
			unset($_SESSION['IdMember']);
			unset($_SESSION['IsVol']);
			unset($_SESSION['Username']);
			unset($_SESSION['Status']);
			unset($_SESSION["stylesheet"]) ;
		}
		if (isset($_SESSION['MemberCryptKey']))
			unset($_SESSION['MemberCryptKey']);
		
		parent::logout();
	}
	
 }
?>
