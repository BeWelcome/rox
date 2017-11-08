<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * MySQL improved DB layer
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_mysqli.lib.php 159 2006-08-31 12:43:56Z kang $
 */
/**
 * MySQL improved DB layer
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_mysqli.lib.php 159 2006-08-31 12:43:56Z kang $
 */
class PDB_mysqli extends PDB {
    /**
     * static instance
     * 
     * @var PDB_mysqli
     */
    private static $_instance;
    /**
     * MySQLi object
     * 
     * @var mysqli
     */
    private $_MySQLi;
     
    /**
     * @param void
     * @access private
     */
    private function __construct() 
    {
        if (!PPHP::assertExtension('mysqli')) {
            throw new PException('MySQLi backend error!');
        }
    }
    
    /**
     * @param void
     */
    public function __destruct() 
    {
        if (self::$_instance->_MySQLi)
            @self::$_instance->_MySQLi->close();
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
     * @return PDB_mysqli
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
            $mysqli = @new mysqli($args['host'], $user, $password, $args['dbname']);
            if (!$mysqli || mysqli_connect_errno()) {
                $E = new PException('Could not connect!');
                $E->addInfo(mysqli_connect_error());
                throw $E;
            }
            self::$_instance->_MySQLi = $mysqli;
            self::$_instance->_dbname = $args['dbname'];
            $queries = array (
                "SET NAMES 'utf8'", 
                "SET CHARACTER SET 'utf8'", 
                "SET collation_connection='utf8_general_ci'",
            );
            foreach ($queries as $query) {
                $q = self::$_instance->exec($query);
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
    `id` INTEGER UNSIGNED AUTO_INCREMENT NOT NULL, 
    PRIMARY KEY(id)
)';
            $this->exec($query);
            $query = 'INSERT INTO `'.$name.'` (`id`) VALUES (0)';
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
    public function dropSequence($name) 
    {
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
        if (!$this->ready())
            return false;
        return $this->_MySQLi->escape_string($str);
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
            $q = $this->_MySQLi->query($statement);
            if (!$q) {
                throw new PException('MySQL error!', 1000);
            }
            $qcount = PVars::get()->queries + 1;
            PVars::register('queries', $qcount);
            if (is_object($q))
                return $q->affected_rows;
            else
                return $q;
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
        return $this->_MySQLi->error;
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
        return $this->_MySQLi->errno;
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
            $query = 'UPDATE `'.$name.'` SET `id` = LAST_INSERT_ID(`id` + 1)';
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
     * @return PDBStatement_mysqli
     */
    public function prepare($statement) 
    {
        $s = new PDBStatement_mysqli($this);
        $s->prepare($statement);
        return $s;
    }
    
    /**
     * performs a query
     * 
     * @param string $statement
     * @return PDBStatement_mysqli
     */
    public function query($statement) 
    {
        try {
            $s = new PDBStatement_mysqli($this);
            if (!$s->query($statement)) {
            	$e = new PException('MySQL Error!', 1000);
                $e->addInfo($this->getErrNo());
                $e->addInfo($this->getErrMsg());
                throw $e;
            }
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
        if (!isset($this->_MySQLi))
            return false;
        if (!isset($this->_dbname))
            return false;
        return true;
    }
}
?>