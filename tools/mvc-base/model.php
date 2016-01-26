<?php

    /**
     * Acts as the base for all MVC-models and entities
     * implements various database calls and makes sure the model and entities
     * will always have a dao object (accessed using $this->dao)
     *
     * @author Lemon-head, Fake51
     */

class RoxModelBase extends RoxComponentBase
{

    /**
     * stores the currently logged in member
     *
     * @var object
     */
    private static $logged_in_member = false;
    /**
     * Holds an object of the entity factory, used for instantiating entities (obviously)
     * Loaded by __construct() - so all descendants of RoxModelBase has access to it
     *
     * @var object RoxEntityFactory
     * @access protected
     */
    protected $_entity_factory;

    protected $pdo;

    /**
     * used to instantiate an RoxEntityFactory - other than that, just calls the parent
     *
     * @access public
     */
    public function __construct()
    {
        MOD_params::get()->loadParams();
        $this->_entity_factory = new RoxEntityFactory;
        parent::__construct();
    }

    public function __destruct() {
        $this->pdo = null;
    }

    /**
     * returns the currently logged in member
     *
     * @access public
     * @return object|false
     */
    public function getLoggedInMember()
    {
        if ($this->logged_in_member)
        {
            return $this->logged_in_member;
        }

        if (!isset($_SESSION['IdMember']))
        {
            return false;
        }
        $this->logged_in_member = $this->createEntity('Member')->findById($_SESSION['IdMember']);

        return $this->logged_in_member;
    }

    /**
     * calls the entity factory to create an entity, passes along any arguments
     *
     * @param string - first parameter must be the name of the entity to create
     * @return object
     * @access protected
     */
    protected function createEntity(/* args */)
    {
        $args = func_get_args();
        return call_user_func_array(array($this->_entity_factory, 'create'), $args);
    }

    /**
     *
     * Restore session if memory cookie exists
     */
    public function restoreLoggedInMember()
    {
        if ($memoryCookie = $this->getMemoryCookie())  {
            // try using memory cookie
            $tmpMember = $this->createEntity('Member')->findById($memoryCookie[0]);
            if ($tmpMember->refreshMemoryCookie() === true) {
                $this->logged_in_member = $tmpMember;
            } else {
                return false;
            }
        }
        return $this->logged_in_member;
    }

    /**
     * Reads the contents of the memory cookie (for "stay logged in")
     *
     * @return array/boolean Contents of cookie or FALSE
     */
    protected function getMemoryCookie() {
        if (!empty($_COOKIE['bwRemember'])
        && $_COOKIE['bwRemember'] != 'hijacked') {
            return unserialize($_COOKIE['bwRemember']);
        } elseif (!empty($_COOKIE['bwRemember'])
        && $_COOKIE['bwRemember'] == 'hijacked') {
            $_SESSION['flash_error'] = 'Your last session seems to have been hijacked and was cancelled.';
            $this->setMemoryCookie(false);
        }
        return false;
    }

    /**
     * Sets the contents of the memory cookie (for "stay logged in")
     *
     * @return array/boolean Contents of cookie or FALSE
     */
    protected function setMemoryCookie($id,$seriesToken='',$authToken='') {
        if ($id !== false) {
            // set new cookie
            setcookie('bwRemember', serialize(array($id, $seriesToken, $authToken)), time() + 1209600, '/'); // cookie expires after 14 days
        } else { // unset cookie
            setcookie('bwRemember', false, 1, '/');
        }
    }

    /**
     * This method fetches a bunch of rows from the database.
     * It has some funny mechanics, which you can usually just ignore.
     *
     * @param string $query_string
     * @param array $keynames
     *   - this will trigger the funny mechanics which sort the results into a hierarchic structure
     * @return array of rows (as objects)
     */
    public function bulkLookup($query_string, $keynames = false)
    {
        $rows = array();
        if (!is_array($keynames)) {
            $keynames = array($keynames);
        }
        $sql_result = $this->dao->query($query_string);
        if (!$sql_result) {
            // sql problem
            return array();
        } else while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            $insertion_point = &$rows;
            $i=0;
            while (true) {
                $keyname = $keynames[$i];
                ++$i;
                if (!$keyname) {
                    $insertion_point[] = $row;
                    break;
                }
                if (!isset($row->$keyname)) {
                    $insertion_point[] = $row;
                    break;
                }
                if ($i >= count($keynames)) {
                    $insertion_point[$row->$keyname] = $row;
                    break;
                }
                if (!isset($insertion_point[$row->$keyname])) {
                    $insertion_point[$row->$keyname] = array();
                }
                $insertion_point = &$insertion_point[$row->$keyname];
            }
            /*
            if ($keyname && isset($row->$keyname)) {
                $rows[$row->$keyname] = $row;
            } else {
                $rows[] = $row;
            }
            */
        }
        return $rows;
    }

    /**
     * This is the same as the above bulkLookup,
     * but the rows are associative arrays instead of objects.
     *
     * @param unknown_type $query_string
     * @return array of rows (as associative arrays)
     */
    public function bulkLookup_assoc($query_string)
    {
        $rows = array();
        if (!$sql_result = $this->dao->query($query_string)) {
            // sql problem
        } else while ($row = $sql_result->fetch(PDB::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function singleLookup($query_string)
    {
        if (!$sql_result = $this->dao->query($query_string)) {
            // sql problem
            return false;
        } else if (!$row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            // nothing found
            return false;
        } else {
            return $row;
        }
    }

    public function singleLookup_assoc($query_string)
    {
        if (!$sql_result = $this->dao->query($query_string)) {
            // sql problem
            return false;
        } else if (!$row = $sql_result->fetch(PDB::FETCH_ASSOC)) {
            // nothing found
            return false;
        } else {
            return $row;
        }
    }

    public function pdoBulkLookup($sql, $values = array()) {
        try {
            $query = $this->get_pdo()->prepare($sql);
            $query->execute($values);
            $query->setFetchMode(PDO::FETCH_OBJ);
            $result = $query->fetchall();
            $query = null;
        }
        catch (PDOException $e) {
            ExceptionLogger::logException($e);
        }
        return $result;
    }
    
    public function pdoSingleLookup($sql, $values = array()) {
        try {
            $query = $this->get_pdo()->prepare($sql);
            $query->execute($values);
            $query->setFetchMode(PDO::FETCH_OBJ);
            $result = $query->fetch();
            $query = null;
        }
        catch (PDOException $e) {
            ExceptionLogger::logException($e);
        }
        return $result;
    }

    public function pdoQuery($sql, $values = array()) {
        try {
            $query = $this->get_pdo()->prepare($sql);
            $query->execute($values);
            if ($query->rowCount() == 0) {
                $result = false;
            } else {
                $result = true;
            }
            $query = null;
        } catch (PDOException $e) {
            ExceptionLogger::logException($e);
        }
        return $result;
    }

    /**
     * normally the $dao should be injected.
     * If it's not, this function creates a new one out of the blue..
     */
    protected function get_dao()
    {
        if (!$dbconfig = PVars::getObj('config_rdbms')) {
            throw new PException('DB config error!');
        }
        return PDB::get($dbconfig->dsn, $dbconfig->user, $dbconfig->password);
    }
    
    /**
     * normally the $pdo should be injected.
     * If it's not, this function creates a new one out of the blue..
     */
    protected function get_pdo()
    {
        if ($this->pdo == null) {
            $dbConfig = PVars::getObj('config_rdbms');
            try {
                $this->pdo = new PDO(
                    $dbConfig->dsn,
                    $dbConfig->user,
                    $dbConfig->password,
                    array(
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    )
                );
            }
            catch(PDOException $e) {
                ExceptionLogger::logException($e);
                $this->pdo = null;
            }
        }
        return $this->pdo;
    }

    protected function getPDO() {
        return $this->pdo;
    }

    protected function getDao() {
        return $this->dao;
    }
}
