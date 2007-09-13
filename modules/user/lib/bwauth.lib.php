<?php
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */

require "auth.lib.php";

class MOD_bw_user_Auth extends MOD_user_Auth
{
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
        	
        	return true;        	
        }
        
		return true;
    }

    protected function doBWLogin( $handle, $password )
    {
    	global $_SYSHCVOL;
	
		if (CountWhoIsOnLine() > $_SYSHCVOL['WhoIsOnlineLimit']) {
			refuse_login(ww("MaxOnlineNumberExceeded", $_SESSION['WhoIsOnlineCount']), $nextlink,"");
		}
	
	
		// Deal with the username which may have been reused
		$rr = LoadRow("SELECT Username,ChangedId FROM members WHERE Username='" . $Username . "'");
		$count = 0;
		while ($rr->ChangedId != 0) {
			$rr = LoadRow("SELECT Username,ChangedId FROM members WHERE id=" . $rr->ChangedId);
			$Username = $rr->Username;
			$count++;
			if ($count > 100) {
				LogStr("Infinite loop in Login with " . $Username, "Bug");
				break; // 
			}
		}
		// End of while with the username which may have been reused
	
		$query = "SELECT id,Status FROM members WHERE Username='" . $handle . "' AND PassWord = PASSWORD('" . $password . "')";

    	$s = $this->dao->query($query);
		if (!$s) 
		{
			throw new PException('Weird shit!');
		}
		if (!$m = $s->fetch(PDB::FETCH_OBJ)) {
			return false;
		}		
		
		if (empty($m->id))
			return false;
					
		$_SESSION['IdMember'] = $m->id; // We need to set theses variables right now because they are used by LogStr
		$_SESSION['Username'] = $m->Username;  // We need to set theses variables right now because they are used by LogStr
		// Process the login of the member according to his status
		switch ($m->Status) {
			case "ChoiceInactive" :  // case an inactive member comes back
				 $query = "UPDATE members SET Status='Active' WHERE members.id=".$m->id." and Status='ChoiceInactive'";
    			 $s = $this->dao->query($query);
				 if (!$s) 
				 	throw new PException(' problem updating a member status from ChoiceInative to Active!');
				 $_SESSION['Status'] = $m->Status='Active' ;
				 $WelcomeMessage= ww("BackToActivity",$m->Username) ;
			case "Active" :
			case "ActiveHidden" :
				 LogStr("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
				 if (HasRight("Words"))
				 	$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
				break;

		case "ToComplete" :  // I think this case never happen (JeanyVes on Septempt 2007 13)
			LogStr("Login with (needmore) <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			header("Location: ".bwlink("completeprofile.php"));
			exit (0);

		case "Banned" :
			LogStr("Banned member tried to log <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You are not allowed to log anymore", "index.php",$m->Status);; // This will unlog and remove the session variables
			exit (0);

		case "TakenOut" :
			LogStr("Takenout member want to Login <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You have been taken out at your demand, you will certainly be please to see you back, please contact us to re-active your profile", "index.php",$m->Status); ; // This will unlog and remove the session variables
			exit (0);

		case "CompletedPending" :
		case "Pending" :
			$str = ww("ApplicationNotYetValid") ;
			LogStr("Pending members <b>".$m->Username."</b> try to log", "Login");
			refuse_Login($str, "index.php",$m->Status); // This will unlog and remove the session variables
			exit(0);
			break;

		case "SuspendedBeta" :
			throw new PException("Beta test problem");
			exit (0);
			break;

		case "NeedMore" :
			LogStr("Login for need more <b>".$m->Username."</b> ", "Login");
			header("Location: ".bwlink("updatemandatory.php"));
			exit (0);
			break;

		default :
			LogStr("Unprocessed status=[<b>" . $m->Status . "</b>] in FunctionsLogin.php with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
			refuse_Login("You can't log because your status is set to " . $m->Status . "<br>", $nextlink,$m->Status); ; // This will unlog and remove the session variables
			return false;
			break;
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
	
		// Preparing value for members.Quality, can the member be moved from NeverLog to LogOnce ?
		if ($m->Quality=='NeverLog' and ($m->Status=='Active' or $m->Status=='ActiveHidden')) {
		   $m->Quality='LogOnce' ;
		}
		$this->dao->query("UPDATE members SET LogCount=LogCount+1,LastLogin=now(),Quality='".$m->Quality."' WHERE id=" . $_SESSION['IdMember']); // update the LastLogin date
	
		// Load language prederence (IdPreference=1)
		$s = $this->dao->query("SELECT memberspreferences.Value,ShortCode FROM memberspreferences,languages WHERE IdMember=" . $_SESSION['IdMember'] . " AND IdPreference=1 AND memberspreferences.Value=languages.id");
		if (!$s) 
		{
			throw new PException('Loading member language preferences!');
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
				//if (HasRight("Words"))
				//	$_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
				break;
		
			default:
				throw new PException("SetupBWSession Weird Status! ".$m->Status );
				break;
		}
    }
    
    protected function updateUser($handle,$password)
    {
    	$pwenc = MOD_user::passwordEncrypt($password);
        $Auth = new MOD_user_Auth;
		$authId = $Auth->checkAuth('defaultUser');
		$query = '
REPLACE `user` 
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`) 
VALUES 
(
    '.$this->dao->nextId('user').', 
    '.(int)$authId.', 
    \''.$this->dao->escape($handle).'\', 
    \'\', 
    \''.$this->dao->escape($pwenc).'\', 
    1
)';
		$s = $this->dao->query($query);
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
			// todo optimize periodically online table because it will be a gruyere 
			// remove from online list
			$query = "delete from online where IdMember=" . $_SESSION['IdMember'];
			$this->dao->query($query);
	
			unset($_SESSION['WhoIsOnlineCount']);
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