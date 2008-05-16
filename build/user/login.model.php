<?php


class LoginModel extends RoxModelBase
{
    const KEY_IN_SESSION = 'App_User_id';
    
    public function __construct()
    {
        parent::__construct();
        $this->loggedIn = isset($_SESSION['Username']);
    }
    
    protected function __get($key)
    {
        echo "<br>LoginModel::$key not found!<br>";
        return "return LoginModel::$key not found!";
    }
    
    
    public function login($username, $pw)
    {
        if (!$this->loggedIn) {
            $this->doLogin($username, $pw);
        }

        // did it work?
        if (!$this->loggedIn) {
            // ouch, it did not.
            return false;
        } else {
            // yeah, it worked!
            return true;
        }
    }
    
    public function loggedIn()
    {
        return $this->loggedIn;
    }
    
    
    protected function doLogin($username, $pw) 
    {
        $this->logout();
    
        $handle = trim($username);
        $password = trim($pw);
    
        if (empty($handle)) {
            return false;
        }
        
        if ($this->doBWLogin( $handle, $password )) {
            
            $this->parent_doLogin( $handle, $password);
            $this->setupBWSession( $handle );
            $this->updateUser( $handle, $password );
            $this->parent_doLogin( $handle, $password);
            
            // Sanity check
            if (!$this->isBWLoggedIn()) {
                throw new PException('Login sanity check failed miserably!');
            }           
            
            return true;            
        }
        return true;
    }
    
    

    protected function doBWLogin( $handle, $password )
    {
        $Username=$this->dao->escape($handle) ;
    
        if (!$s = $this->dao->query(
            "
SELECT id, Status, Username, PassWord
FROM members
WHERE Username = '$Username'
            "
        )) {
            // db request didn't work..
            throw new PException('Weird shit!');
        } else if (!$m = $s->fetch(PDB::FETCH_OBJ)) {
            // no such member found
            return false;
        } else if (empty($m->id)) {
            // member has empty id ? how can that be??
            return false;
        }

        // Hack from jeanyves to avoid being in a bad situation when tables are locked
        // This query will not be locked or slow query
        if (!$qry_jyh = mysql_query(
            "
SELECT password('".$this->dao->escape($password)."') AS PassMysqlEncrypted
            "
        )) {
            MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
            return false;
        } else {
            $res_jyh = mysql_fetch_object($qry_jyh);
        } 
        
        // Testing if password is OK without doing it in a SqlQuery
        if ($m->PassWord != $res_jyh->PassMysqlEncrypted) {
            $strlog = "Failed to log with username  <b>$Username</b> Agent <b>". $_SERVER['HTTP_USER_AGENT'] . "</b>";
            MOD_log::get()->write($strlog, "Login");
            return false;
        }
                    
        // Process the login of the member according to his status
        switch ($m->Status) {

            case "ChoiceInactive" :  // in case an inactive member comes back
                MOD_log::get()->write("Successful login, becoming active again, with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                $this->dao->query(
                    "
UPDATE members
SET Status = 'Active'
WHERE members.id = ".$m->id."
AND Status='ChoiceInactive'
                    "
                );
                $_SESSION['Status'] = $m->Status='Active' ;
                break ;
            case "Active" :
            case "ActiveHidden" :
                 $_SESSION['IdMember']=$m->id ; // this is needed for MOD_log::get, because if not it will not link the log with the right member
                 MOD_log::get()->write("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b> (".$m->Username.")", "Login");
                 break ;
            
            case "ToComplete" :
                MOD_log::get()->write("Login with (tocomplete)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                // FIXME: completeprofile.php does not exist - why used here? (steinwinde 2007-12-05)
                header("Location: " . PVars::getObj('env')->baseuri . "bw/completeprofile.php");
                exit(0);
    
            case "MailToConfirm" :  // I just add this here in case someone try to log with maul to confirm
                MOD_log::get()->write("Login with (MailToConfirm)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false ;
                break;
    
            case "NeedMore" :
                MOD_log::get()->write("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                $this->_immediateRedirect = PVars::getObj('env')->baseuri . "bw/updatemandatory.php";
                break;
    
            case "Banned" :
            case "TakenOut" :
            case "CompletedPending" :
            case "SuspendedBeta" :
                 MOD_log::get()->write("Loging Refused because of status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false ;
                break ;

            case "Pending" :
                return false ;
                break ;
            default:
                 MOD_log::get()->write("Loging Refused because of unknown status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false;
        }
        
        return true;
    }    

    
    
    protected function parent_doLogin($handle, $pw)
    {
        if (empty($handle) || empty($pw)) {
            return false;
        }
        
        $esc_handle = $this->dao->escape($handle);
        if (!$res = $this->dao->query(
            "
SELECT id, auth_id, pw
FROM user
WHERE handle = '$esc_handle'
AND active = 1
            "
        )) {
            // uuh, problem
            echo 'db did not work';
        } else if (!$d = $res->fetch(PDB::FETCH_OBJ)) {
            echo 'no such user found in user table';
            return false;
        }
        
        $matches = array();
        if (preg_match('/^\{([^}]+?)\}(.*)$/', $d->pw, $matches)) {
            switch ($matches[1]) {
                default:
                case 'crypt':
                    if (crypt($pw, $matches[2]) != $matches[2])
                        return false;
                    break;
                    
                case 'md5':
                    if (md5($pw) != $matches[2])
                        return false;
                    break;
                
                case 'sha1':
                    if (sha1($pw) != $matches[2])
                        return false;
                    break;
            }
        } else if (crypt($pw, $d->pw) != $d->pw) {
            return false;
        }
        
        $this->authId = $d->auth_id;
        
        session_regenerate_id();
        $_SESSION[self::KEY_IN_SESSION] = (int)$d->id;
        $this->loggedIn = true;
        
        $s = $this->dao->prepare(
            "
UPDATE user
SET lastlogin = NOW()
WHERE id = ?
            "
        );
        
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }


    
    protected function setupBWSession( $handle )
    {
        if (!$s = $this->dao->query(
            "
SELECT *
FROM members
WHERE Username = '$handle'
            "
        )) { 
            throw new PException('Weird stuff!');
        }

        if (!$m = $s->fetch(PDB::FETCH_OBJ)) {
            throw new PException('Weird stuff!');
        }
    
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
        
        $member_id = $_SESSION['IdMember'];
        $this->dao->query(  
            "
UPDATE members
SET LogCount = LogCount+1, LastLogin = now()
WHERE id = $member_id
            "
        ); // update the LastLogin date
    
        // Load language prederence (IdPreference=1)
        if (!$s = $this->dao->query(
            "
SELECT memberspreferences.Value, ShortCode
FROM memberspreferences,languages
WHERE IdMember = $member_id
AND IdPreference = 1
AND memberspreferences.Value = languages.id
            "
        )) {
            throw new PException('Weird stuff!');
        }
        $langprefs = $s->fetch(PDB::FETCH_OBJ);
        
        if (isset ($langprefs->Value)) {
            // If there is a member selected preference set it
            $_SESSION["IdLanguage"] = $langprefs->Value;
            $_SESSION["lang"] =       $langprefs->ShortCode;
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
                //  $_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
                break;
            
            default:
                throw new PException('SetupBWSession Weird Status!');
                break;
        }
    }
    

    protected function updateUser($handle, $password)
    {
        $Auth = new MOD_user_Auth;
        
        $esc_handle = $this->dao->escape($handle);
        $esc_pwenc = $this->dao->escape(MOD_user::passwordEncrypt($password));
        $member_id = $_SESSION['IdMember'];
        $int_authId = (int)($Auth->checkAuth('defaultUser'));
        
        if ($this->dao->exec(
            "
UPDATE user
SET auth_id = $int_authId, pw = '$esc_pwenc'
WHERE handle = '$esc_handle'
            "
        )) {
            // cool
        } else if ($this->dao->query(
            "
REPLACE INTO user (id, auth_id, handle, email, pw, active)
VALUES ($member_id, $int_authId, '$esc_handle', '', '$esc_pwenc', 1)
            "
        )) {
            // cool again?
        } else {
            // not so cool?
        }
    }
    
    
    
    function isBWLoggedIn() 
    {
        if (empty($_SESSION['IdMember']) || empty($_SESSION['MemberCryptKey'])) {
            return false;
        } else if ($_SESSION['LogCheck'] != Crc32($_SESSION['MemberCryptKey'] . $_SESSION['IdMember'])) {
            $this->logout();
            return false;
        } else {
            return true;
        }
    }
    
    
    
    
    /**
     * log the current user out
     * 
     * @param void
     * @return boolean
     */
    public function logout() 
    {
        $this->removeCookie();
        
        if (isset($_SESSION['IdMember'])) {
            
            MOD_log::get()->write("Logout", "Login");

                
            // todo optimize periodically online table because it will be a gruyere 
            // remove from online list
            $member_id = $_SESSION['IdMember'];
            $this->dao->query(
                "
DELETE FROM online
WHERE IdMember = $member_id
                "
            );
    
            unset($_SESSION['IdMember']);
            unset($_SESSION['IsVol']);
            unset($_SESSION['Username']);
            unset($_SESSION['Status']);
            unset($_SESSION["stylesheet"]) ;
        }
        
        if (isset($_SESSION['MemberCryptKey'])) {
            unset($_SESSION['MemberCryptKey']);
        }
        
        if (!isset($_SESSION[self::KEY_IN_SESSION]))
            return false;
        $this->loggedIn = false;
        unset($_SESSION[self::KEY_IN_SESSION]);
        session_regenerate_id();
        return true;
    }
    
    
    /**
     * remove session login cookie
     * 
     * @param void
     * @return boolean
     */
    public function removeCookie() 
    {
        if( !PVars::__get('cookiesAccepted'))
            return false;
        if( !isset($_COOKIE) || !is_array($_COOKIE))
            return false;
        $env = PVars::getObj('env');
        if( isset($_COOKIE[$env->cookie_prefix.'userid'])) {
            self::addSetting($_COOKIE[$env->cookie_prefix.'userid'], 'skey');
            setcookie($env->cookie_prefix.'userid', '', time()-3600, '/');
        }
        if( isset($_COOKIE[$env->cookie_prefix.'userkey'])) {
            setcookie($env->cookie_prefix.'userkey', '', time()-3600, '/');
        }
        if( isset($_COOKIE[$env->cookie_prefix.'ep'])) {
            setcookie($env->cookie_prefix.'ep', '', time()-3600, '/');
        }
        return true;
    }
    
}


?>