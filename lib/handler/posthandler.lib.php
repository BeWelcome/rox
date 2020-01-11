<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains post handling class
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: posthandler.lib.php 179 2006-11-27 22:48:50Z kang $
 */

use App\Utilities\SessionSingleton;
use App\Utilities\SessionTrait;

/**
 * Post handling class
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class PPostHandler {
    use SessionTrait;

    private static $_instance;
    private $_vars = array();
    private $_callback = array();
    private $_postHandling = FALSE;
    private $_activeKey = FALSE;

    private function __construct() {
        $this->setSession();
    }

    public function __sleep() {
        return array('_vars', '_callback');
    }

    public static function get () {
        $session = SessionSingleton::getSession();
        if (!isset(self::$_instance) || get_class(self::$_instance) != __CLASS__) {
            if ($session->has('PostHandler') && !empty ($session->get('PostHandler'))) {
                self::$_instance = unserialize($session->get('PostHandler'));
                self::$_instance->setSession();
                $session->remove('PostHandler');
            } else {
                $c = __CLASS__;
                self::$_instance = new $c();
            }
        }
        if (self::$_instance->_postHandling)
            return false;
        if (is_array ($_POST) && count ($_POST) > 0) {
            self::$_instance->_postHandling = true;
            self::$_instance->save();
            self::$_instance->getSession()->set( 'PostHandler', serialize(self::$_instance) );
            $req = $_SERVER['REQUEST_URI'];
            if (!PVars::get()->cookiesAccepted) {
                $req = parse_url($req);
                $query = array();
                if (isset($request['query'])) {
                    parse_str($request['query'], $query);
                }
                if (is_array($query) && !array_key_exists(session_name(), $query)) {
                    $query[session_name()] = session_id();
                    $queries = array ();
                    foreach ($query as $k=>$q)
                        $queries[] = $k . '=' . $q;
                    $req['query'] = implode('&', $queries);
                }
                $req = PFunctions::glueParsedUrl($req);
            }
            session_write_close();
            header('Location: '.$req);
            PPHP::PExit();
        } else {
            self::$_instance->getSession()->set( 'PostHandler', serialize(self::$_instance) );
            self::$_instance->_postHandling = false;
        }
        return self::$_instance;
    }

    private function save () {
        if ($this->_callback && is_array ($this->_callback)) {
            foreach ($this->_callback as $key=>$callback) {
                if (!array_key_exists($key, $_POST)) {
                    continue;
                }
                foreach ($_POST as $k=>$v) {
                    if (is_string($v)) {
                        $v = trim($v);
                        $v = stripslashes($v);
                    }
                    $this->_vars[$key][$k] = $v;
                }
                $this->_activeKey = $key;
                $callback = $this->_callback[$key];
                unset($this->_callback[$key]);
                break;
            }
            $ret = false;
            $db = PVars::getObj('config_rdbms');
            $dao = PDB::get($db->dsn, $db->user, $db->password);
            $c = new $callback[0]($dao);
            $cbRet = call_user_func(array(&$c, $callback[1]));
            if ($cbRet)
                $ret = $cbRet;
            self::$_instance->getSession()->set( 'PostHandler', serialize($this) );
            if ($ret) {
                $ret = parse_url($ret);
                $query = array();
                if (isset ($ret['query'])) {
                    parse_str($ret['query'], $query);
                }
                if (is_array($query) && array_key_exists(session_name(), $query))
                    return;
                // $query[session_name()] = session_id();
                $queries = array ();
                foreach ($query as $k=>$q)
                    $queries[] = $k . '=' . $q;
                $ret['query'] = implode('&', $queries);
                $ret = PFunctions::glueParsedUrl($ret);

                session_write_close ();
                header ('Location: ' . $ret);
                PPHP::PExit();
            }
            return TRUE;
        }
    }

    public static function varSet($key) {
        if (!isset(self::$_instance)) return false;
        if (!self::$_instance->_activeKey) return false;
        if (!isset(self::$_instance->_vars)) return false;
        if (!array_key_exists(self::$_instance->_activeKey, self::$_instance->_vars)) return false;
        if (!array_key_exists ($key, self::$_instance->_vars[self::$_instance->_activeKey])) return false;
        return true;
    }

    public static function &getVars($key = false) {
        $false = false;
        if (!isset(self::$_instance->_activeKey) || (!self::$_instance->_activeKey && !$key)) return $false;
        if (!$key) {
            if (!array_key_exists(self::$_instance->_activeKey, self::$_instance->_vars)) {
                self::$_instance->_vars[self::$_instance->_activeKey] = false;
            }
            return self::$_instance->_vars[self::$_instance->_activeKey];
        } else {
            if (!array_key_exists($key, self::$_instance->_vars)) {
                self::$_instance->_vars[self::$_instance->_activeKey] = false;
            }
            return self::$_instance->_vars[$key];
        }
    }

    public static function clearVars($key = false) {
        if (isset(self::$_instance->_activeKey) && self::$_instance->_activeKey && array_key_exists(self::$_instance->_activeKey, self::$_instance->_vars))
            self::$_instance->_vars[self::$_instance->_activeKey] = array();
        if ($key && array_key_exists($key, self::$_instance->_vars))
            self::$_instance->_vars[$key] = array();
        self::$_instance->getSession()->set( 'PostHandler', serialize (self::$_instance) );
    }

    public static function setCallback($key, $class, $action) {
        if (!class_exists($class))
            return false;
        if (get_parent_class($class) != 'PAppModel' && $class != 'PAppModel' && get_parent_class($class) != 'PAppController')
            return false;
        self::$_instance->_callback[$key] = array ($class, $action);
        self::$_instance->getSession()->set( 'PostHandler', serialize(self::$_instance) );
        return true;
    }

    public static function showCallback() {
        print_r (self::$_instance->_callback);
    }

    public static function isHandling() {
        if (!isset(self::$_instance))
            return false;
        return self::$_instance->_postHandling;
    }

    public static function isCallback($key) {
        if (!is_array(self::$_instance->_callback))
            return false;
        return array_key_exists($key, self::$_instance->_callback);
    }

    public static function getActiveKey() {
        if (!isset(self::$_instance->_activeKey))
            return false;
        return self::$_instance->_activeKey;
    }

    public static function setActiveKey($key) {
        self::$_instance->_activeKey = $key;
    }
}
?>
