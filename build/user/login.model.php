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
    

    

    protected function doBWLogin( $Username, $password )
    {
        if (!$m = $this->getMemberByUsername($Username)) {
            // no member found
            return false;
        }

        // Hack from jeanyves to avoid being in a bad situation when tables are locked
        // This query will not be locked or slow query
        if (!$qry_jyh = mysql_query(
            "
SELECT
    password('".$this->dao->escape($password)."') AS PassMysqlEncrypted
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
        
        $this->setBWMemberAsLoggedIn($m);
    }
    
    

    
    
    
    protected function parent_doLogin($handle, $pw)
    {
        if (empty($handle) || empty($pw)) {
            return false;
        }
        
        $matches = array();
        $esc_handle = $this->dao->escape($handle);
        
        if (!$tb_user = $this->getTBUserByHandle($handle)) {
            echo 'no such user found in user table';
            return false;
        }
        
        if (!$this->checkTBPassword($tb_user, $pw)) {
            return false;
        }
        
        $this->authId = $tb_user->auth_id;
        
        session_regenerate_id();
        $_SESSION[self::KEY_IN_SESSION] = (int)$tb_user->id;
        $this->loggedIn = true;
        
        $s = $this->dao->prepare(
            "
UPDATE
    user
SET
    lastlogin = NOW()
WHERE
    id = ?
            "
        );
        
        $s->bindParam(0, $tb_user->id);
        $s->execute();
        
        return true;
    }


    

    

    protected function updateUser($handle, $password)
    {
        $esc_handle = $this->dao->escape($handle);
        $esc_pwenc = $this->dao->escape($this->passwordEncrypt($password));
        $member_id = $_SESSION['IdMember'];
        $int_authId = (int)($this->checkAuth('defaultUser'));
        
        if ($this->dao->exec(
            "
UPDATE
    user
SET
    auth_id = $int_authId,
    pw      = '$esc_pwenc'
WHERE
    handle  = '$esc_handle'
            "
        )) {
            // cool
        } else if ($this->dao->query(
            "
REPLACE INTO
    user (id, auth_id, handle, email, pw, active)
VALUES
    ($member_id, $int_authId, '$esc_handle', '', '$esc_pwenc', 1)
            "
        )) {
            // cool again?
        } else {
            // not so cool?
        }
    }
    
    
    public static function passwordEncrypt($password) {
        if (CRYPT_MD5) {
            $salt = '$1$' . self::randomString (9);
        } else if (CRYPT_EXT_DES) {
            $salt = self::randomString (9);
        } else {
            $salt = self::randomString (2);
        }
        return crypt ($password, $salt);
    }
    
    
    public static function randomString($len) {
        $times = ($len % 40) + 1;
        $random = '';
        for ($i = 0; $i < $times; $i++) {
            mt_srand((double)microtime()*1000000);
            $r = mt_rand();
            $random .= sha1(uniqid($r,TRUE));
        }
        return substr ($random, 0, $len);   
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
    
    
    
    
    
    public function setMemberAsLoggedIn($member)
    {
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
DELETE FROM
    online
WHERE
    IdMember = $member_id
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

    
    
    
    
    /**
     * check if given auth name exists, creates if it does not
     * 
     * @param string $authName
     * @return mixed id or false
     */
    public function checkAuth($authName) 
    {
        try {
            $query =
                "
SELECT
    id
FROM
    mod_user_auth
WHERE
    name = '".$this->dao->escape($authName)."'
                "
            ;
            $q = $this->dao->query($query);
            if ($q->numRows() == 1) {
                return $q->fetch(PDB::FETCH_OBJ)->id;
            }
            if ($q->numRows() != 0)
                throw new PException('D.i.e.!');
            $query =
                "
INSERT INTO
    mod_user_auth (id, name)
VALUES
    (?, ?)
                "
            ;
            $q = $this->dao->prepare($query);
            $id = $this->dao->nextId('mod_user_auth');
            $q->bindParam(0, $id);
            $q->bindParam(1, $authName);
            $q->execute();
            $id = $q->insertId();
            if (!$id || $id == -1)
                return false;
            return $id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }            
    }
    
    
    
    //--------------------------------------------------------------------
    
    
    
    public function createMissingTBUser($member, $password)
    {
        if ($this->dao->exec(
            "
REPLACE INTO
    user (id, auth_id, handle, email, pw, active)
VALUES
    ($member_id, $int_authId, '$esc_handle', '', '$esc_pwenc', 1)
            "
        )) {
            // cool again?
        } else {
            // not so cool?
        }
    }
    
    
    public function updateTBUser($member, $tb_user, $password)
    {
        $esc_handle = $this->dao->escape($handle);
        $esc_pwenc = $this->dao->escape($this->passwordEncrypt($password));
        $member_id = $_SESSION['IdMember'];
        $int_authId = (int)($this->checkAuth('defaultUser'));
        
        if (!$this->dao->exec(
            "
UPDATE
    user
SET
    auth_id = $int_authId,
    pw = '$esc_pwenc'
WHERE
    id = $tb_user->id
            "
        )) {
            // oh shit..
        } else {
            // cool.
        }
    }
    
    
    
    //--------------------------------------------------------------------
    
    
    
    public function checkBWPassword($member, $password)
    {
        $password = trim($password);
        if (!$pw_enc_lookup = $this->singleLookup(
            "
SELECT
    PASSWORD('".$this->dao->escape($password)."')  AS  PassMysqlEncrypted
            "
        )) {
            MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
            return false;
        } else if ($member->PassWord != $pw_enc_lookup->PassMysqlEncrypted) {
            // pw mismatch
            return false;
        } else {
            // pw match
            return true;
        }
    }
    
    
    public function checkTBPassword($tb_user, $password)
    {
        $password = trim($password);
        if (!preg_match('/^\{([^}]+?)\}(.*)$/', $tb_user->pw, $matches)) {
            if (crypt($password, $tb_user->pw) != $tb_user->pw) {
                return false;
            }
        } else switch ($matches[1]) {
            
            case 'md5':
                if (md5($password) != $matches[2])
                    return false;
                break;
            
            case 'sha1':
                if (sha1($password) != $matches[2])
                    return false;
                break;
                
            case 'crypt':
            default:
                if (crypt($password, $matches[2]) != $matches[2])
                    return false;
                break;
        }
        return true;
    }

    
    //-----------------------------------------------------------------------
    
        
    public function getBWMemberByUsername($Username)
    {
        $Username=$this->dao->escape($Username);
        
        if (!$m = $this->singleLookup(
            "
SELECT
    *
FROM
    members
WHERE
    Username = '$Username'
            "
        )) {
            // no member found
            return false;
        } else {
            // member found,
            // but look for alias (in case username was changed)
            while ($m->ChangedId > 0) {
                if (!$m = $this->singleLookup(
                    "
SELECT
    *
FROM
    members
WHERE
    id = $m->ChangedId
                    "
                )) {
                    return false;
                }
            }
            return $m;
        }
    }
    
    
        
    public function getTBUserForBWMember($member)
    {
        $esc_handle = $this->dao->escape($member->Username);
        if ($tb_user = $this->singleLookup(
            "
SELECT
    *
FROM
    user
WHERE
    handle = '$esc_handle'
            "
        )) {
            // found one!
            return $tb_user;
        } else {
            return false;
        }
    }
    
    
    
    //-----------------------------------------------------------------------
    
    
    
    
    public function setBWMemberAsLoggedIn($m)
    {
        // Process the login of the member according to his status
        switch ($m->Status) {

            case "ChoiceInactive" :  // in case an inactive member comes back
                MOD_log::get()->write("Successful login, becoming active again, with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                $this->dao->query(
                    "
UPDATE members
SET Status = 'Active'
WHERE members.id = $m->id
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
                PPHP::PExit();
    
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
                MOD_log::get()->write("Logging Refused because of unknown status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false;
        }
        return true;
    }    

    
    
    public function setupBWSession( $m )
    {
        $member_id = $m->id;
        
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
        $_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $m->id); // Set the key for checking id and LohCheck (will be restricted in future)
        
        
        $this->dao->query(  
            "
UPDATE
    members
SET
    LogCount  = LogCount+1,
    LastLogin = NOW()
WHERE
    id = $member_id
            "
        ); // update the LastLogin date
    
        // Load language prederence (IdPreference=1)
        
        if ($preference_language = $this->singleLookup(
            "
SELECT
    memberspreferences.Value  AS language_id,
    ShortCode                 AS language_code
FROM
    memberspreferences,
    languages
WHERE
    IdMember                 = $member_id    AND
    memberspreferences.Value = languages.id  AND
    IdPreference             = 1
            "
        )) {
            $_SESSION['IdLanguage'] = $preference_language->language_id;
            $_SESSION['lang']       = $preference_language->language_code;
        }

        
        // Process the login of the member according to his status
        switch ($m->Status) {
            case "ChoiceInactive" :  // in case an inactive member comes back
                $this->dao->query(
                    "
UPDATE
    members
SET
    Status = 'Active'
WHERE
    members.id = $m->id      AND
    Status     = 'ChoiceInactive'
                    "
                );
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
    
    

    public function setTBUserAsLoggedIn($tb_user)
    {
        $this->authId = $tb_user->auth_id;
        
        session_regenerate_id();
        $_SESSION[self::KEY_IN_SESSION] = (int)$tb_user->id;
        $this->loggedIn = true;
        
        $this->dao->query(
            "
UPDATE user
SET lastlogin = NOW()
WHERE id = $tb_user->id
            "
        );
    }
    
    
    //-----------------------------------------------------------------------
    
    
    public function setBWMemberAsLoggedOut($member)
    {
        
    }
    
    
    public function setTBUserAsLoggedOut($tb_user)
    {
        
    }
    
}


?>