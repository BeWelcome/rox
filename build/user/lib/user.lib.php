<?php
/**
 * user library
 *
 * @package user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright( c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License( GPL)
 * @version $Id: user.lib.php 181 2006-11-30 19:07:03Z kang $
 */
/**
 * The user library
 * 
 * @package user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright( c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License( GPL)
 */
class APP_User extends MOD_bw_user_Auth 
{
    /**
     * single instance
     * 
     * @var APP_User
     * @access private
     */
    private static $_instance;
    /**
     * @var array
     * @access private
     */
    private $_settings;
    /**
     * current user id
     * 
     * @var int
     * @access private
     */
    private $_userId;
    /**
     * current user handle
     * 
     * @var string
     * @access private
     */
    private $_userHandle;
    /**
     * is logged in?
     * 
     * @var boolean
     * @access protected
     */    
    protected $loggedIn = false;
    
    /**
     * @param void
     * @access protected
     */
    public function __construct() 
    {
        parent::__construct('APP_User_id', 'user');
        $this->setSession();
        // if an Id is set, then the user is logged in, simple and smooth
        if( $this->_session->has( 'APP_User_id' )) {
            $this->_getUser($this->_session->get('APP_User_id'));
        }
    }
    
    public function __get($name) {
        if( !$this->loggedIn)
            return false;
        $name = '_'.$name;
        if( !isset($this->$name))
            return false;
        // create a copy, not a reference
        $copy = $this->$name;
        return $copy;
    }
    
    /**
     * perform a login through a cookie
     * 
     * @param void
     * @return boolean
     * @access private
     */
    private function _cookieLogin() 
    {
        if( !isset($_COOKIE) || !is_array($_COOKIE))
            return false;
        $env = PVars::getObj('env');
        if( !array_key_exists($env->cookie_prefix.'userid', $_COOKIE))
            return false;
        if( !array_key_exists($env->cookie_prefix.'userkey', $_COOKIE))
            return false;
        $key = self::getSetting($_COOKIE[$env->cookie_prefix.'userid'], 'skey');
        if( !$key)
            return false;
        $key = $key->value;
        if( $key != $_COOKIE[$env->cookie_prefix.'userkey']) {
            $this->removeCookie();
            return false;
        }
        $this->_session->set( 'APP_User_id', $_COOKIE[$env->cookie_prefix.'userid'] );
        $this->loggedIn = true;
        $this->setCookie();
        return true;
    }
    

    private function _BWcookieLogin() 
    {
        if( !isset($_COOKIE) || !is_array($_COOKIE))
            return false;
            
        $env = PVars::getObj('env');
        if( !array_key_exists('ep', $_COOKIE))
            return false;
        
        if( !array_key_exists('MyBWusername', $_COOKIE))
            return false;
         
       
        $o=intval($_COOKIE['ep']);
		$query='SELECT Username FROM `tantable` WHERE `OnePad` = '. $o;
		setcookie($env->cookie_prefix.'ep', '', time()-3600, '/');
		$q = $this->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);				
		if( !$d)
        	return false;
		
		if( $d->Username != $_COOKIE['MyBWusername']) {
			return false;
		}
		//error_log("name check success found",0);
		$removefromTantable= 'DELETE FROM `tantable` WHERE `OnePad` = '. $o;
        $q = $this->dao->query($removefromTantable);
        
        return  true;
    }


    private function _getUser($userId) {
        if( !isset($this->_userHandle)) {
            $s = $this->dao->query('SELECT `id`, `handle` FROM `user` WHERE `id` = '.(int)$userId);
            if( $s->numRows() == 0) {
                return false;
            } elseif( $s->numRows() != 1) {
                throw new PException('D.i.e.!');
            } else {
                $d = $s->fetch(PDB::FETCH_OBJ);
//                $this->setAuth($d->auth_id);
                $this->_userId     = $d->id;
                $this->_userHandle = $d->handle;
                $this->loggedIn = true;
            }
        }
    }
    
    public static function activate($userId) {
        $c = self::get();
        $query = '
UPDATE `user` SET `active` = 1 WHERE `id` = ?
        ';
        $s = $c->dao->prepare($query);
        $s->bindParam(0, $userId);
        $s->execute();
        return $s->affectedRows();
    }
    
    public static function get() {
        if( !isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    /**
     * Adds/changes a user setting
     * 
     * may be called statically
     * 
     * @param int $userId
     * @param string $setting
     * @param string $value if "null", then a NULL field will be added
     * @param int $valueint if "null", then a NULL field will be added
     * @param int $valuedate UNIX timestamp - if "null", then a NULL field will be added
     * @return boolean
     */
    public static function addSetting($userId, $setting, $value = null, $valueint = null, $valuedate = null) 
    {
        $c = self::get();
        if( $value === null && $valueint === null && $valuedate === null) {
            $c->dao->exec('DELETE FROM `user_settings` WHERE `user_id` = '.(int)$userId.' AND `setting` = \''.$c->dao->escape($setting).'\'');
            return true;
        }
        $s = $c->dao->query('
SELECT 
    `valueint` 
FROM 
    `user_settings` 
WHERE 
    `user_id` = '.(int)$userId.' 
    AND `setting` = \''.$c->dao->escape($setting).'\'
        ');
        if( $s->numRows() > 1) {
            throw new PException('Data inconsistent');
        }
        if( $s->numRows() == 0) {
            $query = '
INSERT INTO `user_settings` 
(`user_id`, `setting`, `value`, `valueint`, `valuedate`) 
VALUES 
(
    '.(int)$userId.',
    \''.$c->dao->escape($setting).'\',
    '.(is_null($value) ? 'NULL' : '\''.$c->dao->escape($value).'\'').',
    '.(is_null($valueint) ? 'NULL' :( int)$valueint).',
    '.(is_null($valuedate) ? 'NULL' : date('YmdHis', $valuedate)).'
)';
        } else {
            $query = '
UPDATE `user_settings` 
SET 
    `value` = '.(is_null($value) ? 'NULL' : '\''.$c->dao->escape($value).'\'').', 
    `valueint` = '.(is_null($valueint) ? 'NULL' :( int)$valueint).', 
    `valuedate` = '.(is_null($valuedate) ? 'NULL' : date('YmdHis', $valuedate)).'
WHERE 
    `user_id` = '.(int)$userId.'
    AND `setting` = \''.$c->dao->escape($setting).'\'
            ';
        }
        $s = $c->dao->query($query);
        return true;
    }
    
    /**
     * returns all existing user settings
     * 
     * this method returns a stdClass object, with the setting names in first level and each "value", "valueint", "valuedate" in second level
     * may be called statically
     * 
     * @param int $userId
     * @return stdClass
     */
    public static function getAllSettings($userId) 
    {
        $c = self::get();
        $query = '
SELECT `setting`, `value`, `valueint`, `valuedate` FROM `user_settings`
WHERE `user_id` = '.(int)$userId.'
        ';
        $s = $c->dao->query($query);
        if( $s->numRows() == 0)
            return false;
        $settings = new stdClass;
        foreach( $s as $d) {
            $settings->{$d->setting} = $d;
        }
        if( self::loggedIn() && $userId == $c->getId()) {
            $c->_settings = $settings;
        }
        return $settings;
    }
    
    /**
     * retrieves value(s) for one setting
     * 
     * may be called statically
     * 
     * @param int $userId
     * @param string $setting
     * @return stdClass
     */
    public static function getSetting($userId, $setting) 
    {
        $c = self::get();
        if( self::loggedIn() && $userId == $c->getId() && isset($c->_settings)) {
            if( isset($c->_settings[$setting])) {
                return $c->_settings[$setting];
            }
        }
        $query = '
SELECT `value`, `valueint`, `valuedate` FROM `user_settings`
WHERE `user_id` = '.(int)$userId.' AND `setting` = \''.$c->dao->escape($setting).'\'
        ';
        $s = $c->dao->query($query);
        if( $s->numRows() == 0)
            return false;
        $d = $s->fetch(PDB::FETCH_OBJ);
        if( self::loggedIn() && $userId == self::$_instance->getId()) {
            $c->_settings->$setting = $d;
        }
        return $d;
    }
    
    /**
     * returns a boolean value if logged in
     * 
     * may be called statically
     * 
     * @param void
     * @return boolean
     */
    public static function loggedIn() 
    {
        return self::get()->loggedIn;
    }
    
    /**
     * login getter
     * 
     * returns either an instance of APP_User or false
     * may be called statically
     * 
     * @param string $handle
     * @param string $pw
     * @return mixed
     */
    public static function login($handle = false, $pw = false) 
    {
        $c = self::get();
        
        /*
        // default login
        if( !$c->loggedIn) {
            $c->doLogin($handle, $pw);
        }
        // check for cookies
        if( !$c->loggedIn) {
            $c->_cookieLogin();
        }
		*/
        if( !$c->loggedIn) 
        {
//         	error_log("trying to login ".$handle,0);
//			if( $c->_BWcookieLogin()) 
//			{
            	$c->doLogin( $handle, $pw );
//			}
        }

        // give up
        if( !$c->loggedIn) {
//			 MOD_log::get()->write("Login Failed for <b>".$handle."</b>","Login") ; // This is needed for debugging !
            return false;
        }
        // depending on load...
        //self::getAllSettings($c->getId());
        return self::$_instance;
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
        return parent::logout();
    }

    /**
     * returns the current id
     * 
     * @param void
     * @return int or false if not logged in
     */
    public function getId() 
    {
        if( !$this->loggedIn)
            return false;
        return( int)$this->_session->get('APP_User_id');
    }
    
    /**
     * returns the current handle
     * 
     * @param void 
     * @return string or false if not logged in
     */
    public function getHandle() 
    {
        if( !$this->loggedIn)
            return false;
        if( isset($this->_userHandle))
            return $this->_userHandle;
        $query = 'SELECT `handle` FROM `user` WHERE `id` = ?';
        $s = $this->dao->prepare($query);
        $s->execute(array(0=>$this->getId()));
        if( $s->numRows() == 0)
            return false;
        if( $s->numRows() != 1)
            throw new PException('D.i.e.!');
        $this->_userHandle = $s->fetch(PDB::FETCH_OBJ)->handle;
        return $this->_userHandle;
    }
    
    /**
     * remove session login cookie
     * 
     * @param void
     * @return boolean
     */
    public function removeCookie() 
    {
        if( !PVars::get()->cookiesAccepted)
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
     * set session login cookie
     * 
     * @param void
     * @return mixed either the session key or false
     */
    public function setCookie() 
    {
        if( !$this->loggedIn)
            return false;
        $key = MOD_user::randomString(60);
        if( !self::addSetting($this->getId(), 'skey', $key))
            return false;
        $env = PVars::getObj('env');
        $loc = parse_url($env->baseuri);
        $expires = time()+60*60*24*14;
        $id = setcookie($env->cookie_prefix.'userid', $this->getId(), $expires, '/');
        if( !$id)
            return false;
        $key = setcookie($env->cookie_prefix.'userkey', $key, $expires, '/');
        return $key;
    }
    
    /**
     * returns the user id for given handle
     * 
     * may be called statically
     * 
     * @param string $handle
     * @return mixed int or false
     */
    public static function userId($handle) 
    {
        $c = self::get();
        $query = 'SELECT `id` FROM `user` WHERE `handle` = \''.$c->dao->escape($handle).'\'';
        $q = $c->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);
        if( !$d)
            return false;
        return $d->id;
    }

    /**
     * returns the member id for given handle
     * 
     * may be called statically
     * 
     * @param string $handle
     * @return mixed int or false
     */
    public static function memberId($handle) 
    {
        $c = self::get();
        $query = 'SELECT `id` FROM `members` WHERE `username` = \''.$c->dao->escape($handle).'\'';
        $q = $c->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);
        if( !$d)
            return false;
        return $d->id;
    }    
    
    /**
     * returns the country code for given handle
     * 
     * may be called statically
     * 
     * @param string $handle
     * @return mixed String or false
     */
    public static function countryCode($handle) 
    {
        $c = self::get();
        $query = sprintf("SELECT `country` 
        	FROM `user` 
        	LEFT JOIN `geonames` ON( `user`.`location` = `geonames`.`geonameid`)
        	WHERE `handle` = '%s'",
        	$c->dao->escape($handle));
        $q = $c->dao->query($query);
        $d = $q->fetch(PDB::FETCH_OBJ);
        if( !$d || !$d->fk_countrycode)
            return false;
        return $d->fk_countrycode;
    }

    /**
     * sets the loggedIn variable to false
     * hack to be able to log out not using this class
     *
     * @todo get rid of this whole class
     */
    public function setLogout()
    {
        $me = self::get();
        $me->loggedIn = false;
    }
}
