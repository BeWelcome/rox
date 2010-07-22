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
        $handle = $this->dao->escape($handle);
        $q = $this->dao->query(
            "
SELECT
    id,
    auth_id,
    pw
FROM
    `$this->tableName`
WHERE
    handle = '$handle'  AND
    active = 1
            "
        );
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
        
        $s = $this->dao->prepare(
            "
UPDATE
    `$this->tableName`
SET
    lastlogin = NOW()
WHERE
    id = ?
            "
        );
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }


    protected function doBWLogin($handle) {
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle))
            return false;
        $handle = $this->dao->escape($handle);
        $q = $this->dao->query(
            "
SELECT
    id,
    auth_id
FROM
    `$this->tableName`
WHERE
    handle = '$handle'  AND
    active = 1
            "
        );
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
        $q = $this->dao->prepare(
            "
SELECT
    auth_id
FROM
    `$this->tableName`
WHERE
    id = ?
            "
        );
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

    public static function getTranslations($idMember) {
        
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        $result = $localDao->query(
            "
SELECT DISTINCT
    memberstrads.IdLanguage,
    languages.ShortCode
FROM
    memberstrads,
    languages
WHERE
    memberstrads.IdLanguage = languages.id  AND
    IdOwner = $idMember
            "
        );

        $a = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            array_push($a, $row);
        }
        return $a;
    }
    
    public static function getImage($paramIdMember=0)
    {
        if ($paramIdMember==0) {
            $IdMember=$_SESSION['IdMember'];
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
        
        $result = $localDao->query(
            "
SELECT
    FilePath
FROM
    membersphotos
WHERE
    IdMember = $IdMember  AND
    SortOrder = 0
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        
        if (isset($record->FilePath)) {
            return $path = PVars::getObj('env')->baseuri . $record->FilePath;
        } else {
            $result = $localDao->query(
                "
SELECT
    Gender,
    HideGender
FROM
    members
WHERE
    id = $IdMember
                "
            );
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
    static function getDummyImage($Gender='IDontTell', $HideGender='Yes')
    {
            return PVars::getObj('env')->baseuri . 'images/misc/empty_avatar.png';
           global $_SYSHCVOL ; // To be usable $_SYSHCVOL must be declared as global in functions 
        // TODO: skipped while porting code to platform PT (correct???):
        // global $_SYSHCVOL;
        // $_SYSHCVOL['IMAGEDIR']
        
        $path = PVars::getObj('env')->baseuri . 'memberphotos/';
        
        if ($HideGender=='Yes') {
            return $path . 'et.jpg';
        }
        if ($Gender=='male') {
            return $path . 'et_male.jpg';
        }
        if ($Gender=='female') {
            return $path . 'et_female.jpg';
        }
        return $path . 'et.gif';
    }
    
    /**
     * Updates environment variables: WhoIsOnlineCount, GuestOnlineCount
     * WhoIsOnlineCount: number according to table online
     * GuestOnlineCount: number according to table guestsonline minus WhoIsOnlineCount
	 *
	 * Important this function also refresh the $_SESSION["MemberStatus"] variable and 
	 * test it against Rejected and Banned status
     */
    public static function updateSessionOnlineCounter()
    {
        global $_SYSHCVOL ; // To be usable $_SYSHCVOL must be declared as global in functions 
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
        if (isset($_SYSHCVOL['WhoIsOnlineDelayInMinutes'])) {
            $interval = $_SYSHCVOL['WhoIsOnlineDelayInMinutes'];
        } else {
            $interval = 5;
        }
        
        $result = $localDao->query(
            "
SELECT
    COUNT(*) AS cnt
FROM
    online
WHERE
    online.updated > DATE_SUB( NOW(), INTERVAL $interval minute )  AND
            online.Status in ('Active','Pending','NeedMore','OutOfRemind')
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        $_SESSION['WhoIsOnlineCount'] = $record->cnt;
        
        $result = $localDao->query(
            "
SELECT
    COUNT(*) AS cnt
FROM
    guestsonline
WHERE
    guestsonline.updated > DATE_SUB( NOW(), INTERVAL $interval minute )
            "
        );
        
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


				// Added by JeanYves to be able to manage in a dynamic way the changes in Param Table
				$result = $localDao->query("SELECT * FROM  `params` limit 1");
        $_SESSION["Param"] = $result->fetch(PDB::FETCH_OBJ);

        $ip_string="196.168.1.1";
        // prior to any updates, the entry in the table guestsonline 
        // is always deleted

        //if we are on localhost and use IPv6 we must omit this
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '::1') {
        	$ip_string = (string)$_SERVER['REMOTE_ADDR'];
        }
        if (!is_int($ip_int = ip2long($ip_string))) {
            // grmmm
            // ip -1 means that we could not determine the ip
            $ip_int = -1;
        }      
        @$localDao->query(
            "
DELETE FROM
    guestsonline
WHERE
    IpGuest = $ip_int
            "
        );

        
        // TODO: check for logged in user should be accomplished somewhere else
        // in a unified manner
        if ((
            empty($_SESSION['MemberCryptKey']) ||
            empty($_SESSION['IdMember'])
        ) && (
            isset($_SERVER['REMOTE_ADDR'])
        )) {
						
						
				// Added by JeanYves to be able to manage in a dynamic way the status of a member 
								
				if (isset($_SESSION["IdMember"])) { // if the user is a known member
$result = $localDao->query(
                    "
SELECT
    Username,Status 
FROM
    members
WHERE
		id=".$_SESSION["IdMember"]
                );
										$row=$result->fetch(PDB::FETCH_OBJ);
										$_SESSION["MemberStatus"]=$row->Status ;
							
				} // End if the user is a known member
								
								             
            /*
             * we don't want this!!!
             * Privacy!
             * See http://www.bevolunteer.org/trac/ticket/535
             *
             */
            // Update by JeanYves :
            // for not logged members or bots activity will be displayable in whoisonline
						
						
						// Added by JeanYves to be able to manage in a dynamic way the status of a member and teh changes in Param Table
						
$result = $localDao->query(
                    "
SELECT
    * 
FROM
    params
                    "
                );
                $_SESSION["Param"] = $result->fetch(PDB::FETCH_OBJ);
								
								if (isset($_SESSION["IdMember"])) { // if the user is a known member
$result = $localDao->query(
                    "
SELECT
    Username,Status 
FROM
    members
WHERE
		id=".$_SESSION["IdMember"]
                );
										$row=$result->fetch(PDB::FETCH_OBJ);
										$_SESSION["MemberStatus"]=$row->Status ;
							
								} // End if the user is a known member
								
								           
            // For admin save also activity parameters
            if (isset($_SERVER['QUERY_STRING'])) {
                $lastactivity=$_SERVER['SERVER_NAME'].' '.$_SERVER['PHP_SELF'];
                if ($_SERVER['QUERY_STRING']!="") {
                    $lastactivity=$lastactivity.'?'.$_SERVER['QUERY_STRING'];
                }
                foreach($_POST as $keyname=>$value) {
                    if ((strpos($keyname,"password")===false)and(strpos($keyname,"login-p")===false))  { // We will not show the password
                        $lastactivity=$lastactivity." POST['.$keyname.']=".$value ;
                    }
                    else {
                       $lastactivity=$lastactivity." POST['.$keyname.']="."******" ;
                    }
                } // end foreach
                $lastactivity= mysql_escape_string($lastactivity) ;
            }

            $localDao->query(
                "
REPLACE INTO
    guestsonline (IpGuest, appearance, lastactivity)
VALUES
    ($ip_int, '$ip_string', '$lastactivity')
                "
            );
            
        } else if (
            // This test is because of ticket #408,
            // mailbot when not interactive must not run there
            isset($_SERVER['REMOTE_ADDR'])
        ) {
            $lastactivity = 'notmybusiness'; // Logged member activity is not displayable
            $member_id = (int)$_SESSION['IdMember'];
            $username = $localDao->escape($_SESSION['Username']);
            $status = $localDao->escape($_SESSION['Status']);
            
            $localDao->query(
                "
REPLACE INTO
    online (IdMember, appearance, lastactivity, Status)
VALUES
    ($member_id, '$username', '$lastactivity', '$status')
                "
            );

            // TODO: does the table params and its idea really make sense???
            // TODO: is this an appropriate place to do the check?
            // Check, if a record is established
            if (!empty($_SESSION['WhoIsOnlineCount'])) {
                $result = $localDao->query(
                    "
SELECT
    recordonline
FROM
    params
                    "
                );
                $row = $result->fetch(PDB::FETCH_OBJ);
                if ($_SESSION['WhoIsOnlineCount'] > $row->recordonline) {
                    MOD_log::get()->write(
                       'New record established, '.$_SESSION['WhoIsOnlineCount'].' members online!',
                       'Record'
                    );
                    $who_is_online_count = (int)$_SESSION['WhoIsOnlineCount'];
                    $localDao->query(
                        "
UPDATE
    params
SET
    recordonline = $who_is_online_count
                        "
                    );
                }
            }
        }
        return;
    }
}



?>
