<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * MySQL DB layer
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_mysql.lib.php 184 2006-12-07 18:56:48Z roland $
 */
/**
 * MySQL DB layer
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_mysql.lib.php 184 2006-12-07 18:56:48Z roland $
 */
class PDB_mysql extends PDB 
{
    /**
     * static instance
     * 
     * @var PDB_mysql
     */
    private static $_instance;
    /**
     * Connection resource
     * 
     * @var resource
     */
    private $_cr;
    /**
     * current database name
     * 
     * @var string
     */
    private $_dbname;
    /**
     * result history
     * 
     * @var array
     */
    private $_results;
    
    /**
     * @param void
     * @access private
     */
    private function __construct() 
    {
        if (!PPHP::assertExtension('mysql')) {
            throw new PException('MySQL backend error!');
        }
    }
    
    /**
     * @param void
     */
    public function __destruct() 
    {
        if (is_resource($this->_cr)) {
            if (!@mysql_close($this->_cr)) {
                throw new PException('Connection could not be closed!');
            }
        }
    }
    
    /**
     * overloading
     * 
     * @param string $name property name
     * @return mixed
     */
    public function __get($name) 
    {
        $name = '_'.$name;
        if (!isset($this->$name))
            return FALSE;
        return $this->$name;
    }
    
    /**
     * connector
     * 
     * there will be only one connection instance
     * 
     * @param array $args
     * @param string $user
     * @param string $password
     * @return PDB_mysql
     */
    protected static function connect($args, $user = false, $password = false) 
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
            if (PVars::get()->debug) {
                $t = microtime();
                PSurveillance::setPoint('connect'.$t);
            }
            if (!isset($args['host'])) {
                throw new PException('Host not set!');
            }
            if (!isset($args['dbname'])) {
                throw new PException('DB name not set!');
            }
            $cr = @mysql_connect($args['host'], $user, $password, true);
            if (!$cr) {
                throw new PException('Could not connect!');
            }
            self::$_instance->_cr = $cr;
            if (!@mysql_select_db($args['dbname'])) {
                throw new PException('Could not select DB: '.$args['dbname'].'!');
            }
            self::$_instance->_dbname = $args['dbname'];
            $queries = array (
                "SET NAMES 'utf8'", 
                "SET CHARACTER SET 'utf8'", 
                "SET collation_connection='utf8_general_ci'",
            );
            foreach ($queries as $query) {
                $q = self::$_instance->query($query);
                if (!$q) {
                    throw new PException('MySQL collation error!', 1000);
                }
            }
            if (PVars::get()->debug) {
                PSurveillance::setPoint('eoconnect'.$t);
            }
        }
        return self::$_instance;
    }
    
    /**
     * creates a sequence with given name
     * 
     * sequence is represented here in MySQL through a AUTO_INCREMENT field in a seperate table
     * 
     * @param string $name
     * @return boolean
     */
    public function createSequence($name) 
    {
        try {
            $name = $this->getSequenceName($name);
            $query = '
CREATE TABLE
`'.$name.'` (
    `id` INTEGER AUTO_INCREMENT NOT NULL, 
    PRIMARY KEY(id)
)';
            $this->exec($query);
            $query = 'INSERT INTO `'.$name.'` (`id`) VALUES (-1)';
            $q = $this->query($query);
            return true;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->getErrNo().') '.$this->getErrMsg());
            }
            // table already exists
            if ($this->getErrNo() == 1050) {
                return true;
            }
            throw $e;
        }
    }
    
    /**
     * removes a existing sequence with given name
     * 
     * @param string $name
     * @return boolean
     */
    public function dropSequence($name) {
        $name = $this->getSequenceName($name);
        $query = 'DROP TABLE IF EXISTS `'.$name.'`';
        try {
            if ($this->exec($query) != -1)
                return false;
            return true;
        } catch (PException $e) {
            throw $e;
        }
    }

    /**
     * escaping a string
     * 
     * @param string $str
     * @return string
     */
    public function escape($str) 
    {
        if (!isset($this->_cr) || !$this->_cr || !is_resource($this->_cr)) {
            return mysql_escape_string($str);
        } else {
            return mysql_real_escape_string($str, $this->_cr);
        }
    }
    
    /**
     * executes a statement and returns the no of affected rows
     * 
     * @param string $statement
     * @return int
     */
    public function exec($statement) 
    {
        try {
            if (!$this->ready()) {
                throw new PException('MySQL connection not ready!');
            }
            $q = @mysql_query($statement, $this->_cr);
            if (!$q) {
                throw new PException('MySQL error!', 1000);
            }
            $q = PVars::get()->queries + 1;
            PVars::register('queries', $q);
            return mysql_affected_rows($this->_cr);
        } catch (PException $e) {
            throw $e;
        }        
    }
    
    /**
     * returns the error text of the previous statement
     * 
     * @param void
     * @return string
     */
    public function getErrMsg() 
    {
        if (!$this->ready())
            throw new PException('Error could not be retrieved!');
        return mysql_error($this->_cr);
    }

    /**
     * returns the error no of the previous statement
     * 
     * @param void
     * @return int
     */
    public function getErrNo() 
    {
        if (!$this->ready())
            throw new PException('Error could not be retrieved!');
        return mysql_errno($this->_cr);
    }
    
    /**
     * returns the next ID for given sequence name
     * 
     * @param string $name
     * @return int
     */
    public function nextId($name) 
    {
        try {
            $this->createSequence($name);
            $name = $this->getSequenceName($name);
            $query = 'UPDATE `'.$name.'` SET `id` = IF(LAST_INSERT_ID(`id`) = -1, LAST_INSERT_ID(`id` + 2), LAST_INSERT_ID(`id` + 1))';
            $q = $this->query($query);
            return $q->insertId();
        } catch (PException $e) {
            throw $e;
        }
    }
    
    /**
     * prepares a statement
     * 
     * @param string $statement
     * @return PDBStatement_mysql
     */
    public function prepare($statement) 
    {
        $s = new PDBStatement_mysql($this);
        $s->prepare($statement);
        return $s;
    }
    
    /**
     * performs a query
     * 
     * @param string $statement
     * @return PDBStatement_mysql
     */
    public function query($statement) 
    {
        try {
            $s = new PDBStatement_mysql($this);
            if (!$s->query($statement))
                return false;
            return $s;
        } catch (PException $e) {
            throw $e;
        }
    }

    /**
     * returns true when the DB may be used
     * 
     * @param void
     * @return boolean
     */
    public function ready() 
    {
        if (!isset($this->_cr) || !is_resource($this->_cr))
            return false;
        if (!isset($this->_dbname))
            return false;
        return true;
    }
}
?>
