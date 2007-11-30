<?php
/**
 * @author Philipp Hunstein & Seong-Min Kang <info@respice.de>
 * @version v2.0.0 Pre-Alpha
 */
abstract class MOD_user {
    protected $authId;
    protected $sessionName;
    protected $tableName;
    protected $dao;
    
    protected function __construct($sessionName = false, $tableName = false) {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
        if ($sessionName)
            $this->sessionName = $sessionName;
        if ($tableName)
            $this->tableName = $tableName;
    }
    
    public function __destruct() {
        unset($this->_dao);
    }
    
    public function getAuth() {
        if (!$this->loggedIn)
            return false;
        if (!$this->authId)
            return false;
        $A = new MOD_user_Auth($this->sessionName, $this->tableName, $this->authId);
        return $A;
    }

    public function hasRight($right) {
        $Auth = $this->getAuth();
        if (!$Auth)
            return false;
        return $Auth->hasRight($right);
    }
    
    protected function setAuth($authId) {
        $this->authId = $authId;
    }

    protected function doLogin($handle, $pw) {
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle) || empty($pw))
            return false;
        $query = '
SELECT `id`, `auth_id`, `pw` 
FROM `'.$this->tableName.'` WHERE `handle` = \''.$this->dao->escape($handle).'\' AND `active` = 1';
        $q = $this->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);
        if (!$d)
            return false;
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
        } elseif (crypt($pw, $d->pw) != $d->pw) {
            return false;
        }
        $this->setAuth($d->auth_id);
        session_regenerate_id();
        $_SESSION[$this->sessionName] = (int)$d->id;
        $this->loggedIn = true;

        $s = $this->dao->prepare('UPDATE `'.$this->tableName.'` SET `lastlogin` = NOW() WHERE `id` = ?');
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }


    protected function doBWLogin($handle) {
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle))
            return false;
        $query = '
SELECT `id`, `auth_id` 
FROM `'.$this->tableName.'` WHERE `handle` = \''.$this->dao->escape($handle).'\' AND `active` = 1';
        $q = $this->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);
       
        if (!$d)
            return false;
          
        $this->setAuth($d->auth_id);
        session_regenerate_id();
        $_SESSION[$this->sessionName] = (int)$d->id;
        $this->loggedIn = true;

        $s = $this->dao->prepare('UPDATE `'.$this->tableName.'` SET `lastlogin` = NOW() WHERE `id` = ?');
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }
    


    
    protected function getAuthId($userId) {
        if (!isset($this->tableName))
            return false;
        if (isset($this->authId))
            return $this->authId;
        $query = 'SELECT `auth_id` FROM `'.$this->tableName.'` WHERE `id` = ?';
        $q = $this->dao->prepare($query);
        $q->execute(array(1=>(int)$userId));
        $d = $q->fetch(PDB::FETCH_OBJ);
        if (!$d)
            return false;
        return $d->auth_id;
    }

    public function logout() {
        if (!isset($this->sessionName))
            return false;
        if (!isset($_SESSION[$this->sessionName]))
            return false;
        $this->loggedIn = false;
        unset($_SESSION[$this->sessionName]);
        session_regenerate_id();
        return true;
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

    public static function getImage($paramIdMember=0)
    {
        if ($paramIdMember==0) {
            $IdMember=$_SESSION["IdMember"];
        } else {
            $IdMember=$paramIdMember ;
        }
	
        if ($IdMember==0) {
            return MOD_User::getDummyImage();
        } 

        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        $query = '
SELECT FilePath
FROM membersphotos
WHERE IdMember=' . $IdMember . '
AND SortOrder=0
';
        $result = $localDao->query($query);
		$record = $result->fetch(PDB::FETCH_OBJ);
        
        if (isset($record->FilePath)) {
            return $path = PVars::getObj('env')->baseuri . $record->FilePath;
        } else {
            $query = '
SELECT Gender, HideGender
FROM members
WHERE id=' . $IdMember;
            $result = $localDao->query($query);
            $record = $result->fetch(PDB::FETCH_OBJ);
            return MOD_User::getDummyImage($record->Gender, $record->HideGender);
        }
    }
    
    /**
     * Returns the path to an appropriate dummy image in case
     * no image is found.
     * 
     * 
     *
     * @param unknown_type $Gender
     * @param unknown_type $HideGender
     * @return unknown
     */
    static function getDummyImage($Gender="IDontTell", $HideGender="Yes")
    {
        // TODO: skipped while porting code to platform PT (correct???):
        // global $_SYSHCVOL;
        // $_SYSHCVOL['IMAGEDIR']
        
        $path = PVars::getObj('env')->baseuri . 'memberphotos/';
        
        if ($HideGender=="Yes") {
            return $path . "et.jpg";
        }
        if ($Gender=="male") {
            return $path . "et_male.jpg";
        }
        if ($Gender=="female") {
            return $path . "et_female.jpg";
        }
        return $path . "et.gif";
    }
    
    /**
     * Updates environment variables: WhoIsOnlineCount, GuestOnlineCount
     * WhoIsOnlineCount: number according to table online
     * GuestOnlineCount: number according to table guestsonline minus WhoIsOnlineCount
     */
    public static function updateSessionOnlineCounter()
    {
        // FIXME: skipped the following code while porting to platform PT:
        // if ($_SYSHCVOL['WhoIsOnlineActive'] != "Yes") {
        //     $_SESSION['WhoIsOnlineCount'] = "###";
        //     return;
        // }

        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        // FIXME: While porting code to platform PT, this has been skipped:
        // $interval = $_SYSHCVOL['WhoIsOnlineDelayInMinutes']
        $interval = 5;
        $query = '
SELECT COUNT(*) AS cnt
FROM online
WHERE online.updated>DATE_SUB(now(),INTERVAL ' . $interval . ' minute)
AND online.Status=\'Active\'
';
        $result = $localDao->query($query);
		$record = $result->fetch(PDB::FETCH_OBJ);
		$_SESSION['WhoIsOnlineCount'] = $record->cnt;
        
        $query = '
SELECT COUNT(*) as cnt
FROM guestsonline
WHERE guestsonline.updated>DATE_SUB(now(),INTERVAL ' . $interval . ' minute)
';
        $result = $localDao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
        $_SESSION['GuestOnlineCount'] = $record->cnt - $_SESSION['WhoIsOnlineCount'];
  
        return;
    }
    
    /**
     * Update table guestsonline, used for counting
     * guests (and logged in members?) of the website
     * "now".
     * 
     * FIXME: do I need to mysql_escape_string($_SERVER["PHP_SELF"]) ???
     * FIXME: method is at least called twice with every request to Rox
     * TODO: it's probably not making sense to use $_SERVER['PHP_SELF']
     * under platform PT
     * 
     */
    public static function updateDatabaseOnlineCounter()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        // prior to any updates, the entry in the table guestsonline 
        // is always deleted 
        $query = '
DELETE FROM guestsonline
WHERE IpGuest=' . ip2long($_SERVER['REMOTE_ADDR']) . '';
        @$localDao->query($query);

// For admin save also activity parameters
   		 $lastactivity=$_SERVER['SERVER_NAME']." ".$_SERVER['PHP_SELF'] ;
			 if ($_SERVER['QUERY_STRING']!="") $lastactivity=$lastactivity."?".$_SERVER['QUERY_STRING'] ;
			 foreach($_POST as $keyname=>$value) {
			 		$lastactivity=$lastactivity." POST['.$keyname.']=".$value ;
			 }
			 $lastactivity= mysql_escape_string($lastactivity) ; 

        // TODO: check for logged in user should be accomplished somewhere else
        // in a unified manner
        if (
            empty($_SESSION['MemberCryptKey']) ||
            empty($_SESSION['IdMember'])
        ) {
            


            $query = '
INSERT INTO guestsonline
(IpGuest, appearance, lastactivity)
VALUES(' . ip2long($_SERVER['REMOTE_ADDR']) .
', \'' . $_SERVER['REMOTE_ADDR'] . '\'' .
', \'' . $lastactivity . '\')';   
            $localDao->query($query);
            
        } else {
            
            $query = '
DELETE FROM online
WHERE IdMember=' . $_SESSION['IdMember'];
            $localDao->query($query);
            
            $query = '
INSERT INTO online
(`IdMember`, `appearance`, `lastactivity`, `Status`)
VALUES (' . $_SESSION['IdMember'] . ',\'' . 
                $localDao->escape($_SESSION['Username']) . '\',\'' .
                $lastactivity . '\',\'' . $_SESSION['Status'] . '\')';
                $localDao->query($query);

            // TODO: does the table params and its idea really make sense???
            // TODO: is this an appropriate place to do the check?
            // Check, if a record is established
            if (!empty($_SESSION['WhoIsOnlineCount'])) {
                $query = '
SELECT recordonline
FROM params';
	            $result = $localDao->query($query);
	            $row = $result->fetch(PDB::FETCH_OBJ);
	            if ($_SESSION['WhoIsOnlineCount'] > $row->recordonline) {
	                MOD_log::write("New record established, " .
	                    $_SESSION['WhoIsOnlineCount'] . " members online!", "Record");
	                $query = '
UPDATE params
SET recordonline=' . $_SESSION['WhoIsOnlineCount'];
	                $localDao->query($query);
	            }
            }
            
        }
        
        return;
    }
}
?>