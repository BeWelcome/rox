<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * MySQL improved statement object
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement_mysqli.lib.php 150 2006-07-26 12:06:23Z kang $
 */
/**
 * MySQL improved statement object
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement_mysqli.lib.php 150 2006-07-26 12:06:23Z kang $
 */
class PDBStatement_mysqli extends PDBStatement {
    /**
     * @var PDB_mysqli
     * @access private
     */    
    private $_dao;
    /**
     * array of statements
     * 
     * @var array
     * @access private
     */
    private $_statement;
    /**
     * index of current statement
     * 
     * @var int
     * @access private
     */
    private $_i = 0;
    /**
     * array of bound params (are references)
     * 
     * @var array
     * @access private
     */
    private $_bound = array();
    /**
     * result resource
     * 
     * @var resource
     * @access protected
     */
    protected $result;
        
    /**
     * @access private
     * @param PDB_mysql
     */    
    public function __construct(&$dao) 
    {
        $this->_dao =& $dao;
    }

    /**
     * returns the number of affected rows of the preceding statement
     * 
     * @param void
     * @return int
     */
    public function affectedRows() 
    {
        if (isset($this->result) && is_object($this->result))
            return $this->result->affected_rows;
        if (isset($this->_statement[$this->_i]))
            return $this->_statement[$this->_i]->affected_rows;
        return false;
    }
    
    /**
     * bind a paramter
     * 
     * @param string $name the name (usually the index starting with "1" for the "?" parameters in prepared statements)
     * @param mixed $val
     */
    public function bindParam($name, &$val) 
    {
        $this->_bound[$name] =& $val;
    }
    
    /**
     * execute a prepared statement
     * 
     * the $params parameter is used for backwards compatibility
     */
    public function execute() 
    {
        if (is_array($this->_bound) && count($this->_bound) > 0) {
            $bstring = '';
            $args = array();
            foreach ($this->_bound as $val) {
                $bstring .= is_int($val) ? 'i' : is_float($val) ? 'd' : 's';
                $args[] = $val;
            }
            $tmp = array();
            foreach($args as $key => $value) {
                $tmp[$key] = &$args[$key];
            }

            $callback = array($this->_statement[$this->_i], 'bind_param');
            $newargs = array_merge([$bstring], $tmp);
            if (!call_user_func_array($callback, $newargs )) {
            	print_r($callback);print_r($this);exit();
            }
/*            $offs = 0;
            if (substr_count($args[0], 'b') > 0) {
                while ($pos = strpos($args[0], 'b', $offs)) {
                    $blob = str_split($args[$pos], ini_get('max_allowed_packet'));
                    foreach ($blob as $b) {
                        $this->_statement[$this->_i]->send_long_data($pos, $b);
                    }
                    $offs = $pos;
                }
            }
  */      }
        $q = @$this->_statement[$this->_i]->execute();
        if (!$q) {
            $e = new PException('MySQL error!', 1000);
            if (isset($args)) {
                $e->addInfo(print_r($args, true));
                $e->addInfo(print_r($this->_bound, true));
            }
            $e->addInfo($this->_dao->getErrNo());
            $e->addInfo($this->_dao->getErrMsg());
            throw $e;
        }
        if (is_object($q)) {
            $this->result = $q;
        }
        $this->pos = 0;
        $q = PVars::get()->queries + 1;
        PVars::register('queries', $q);
        return true;
    }

    /**
     * fetch one row
     * 
     * you can provide one of the PDB::* constants to set the type
     * PDB::FETCH_BOTH
     * PDB::FETCH_ASSOC
     * PDB::FETCH_NUM
     * PDB::FETCH_OBJ
     * 
     * @param mixed $style
     * @return mixed
     */
    public function fetch($style = false) 
    {
        if (!$this->result)
            return false;
        switch ($style) {
            case PDB::FETCH_BOTH:
            default:
                $res = $this->result->fetch_array(MYSQLI_BOTH);
                break;
                
            case PDB::FETCH_ASSOC:
                $res = $this->result->fetch_array(MYSQLI_ASSOC);
                break;
                
            case PDB::FETCH_NUM:
                $res = $this->result->fetch_array(MYSQLI_NUM);
                break;
                
            case PDB::FETCH_OBJ:
                $res = $this->result->fetch_object();
                break;
        }
        if ($res)
            $this->pos++;
        return $res;
    } 

    /**
     * fetches one column in the row
     * 
     * @param int $pos
     * @return mixed
     */
    public function fetchColumn() 
    {
        if (!$this->result)
            return false;
        return $this->result->fetch_field(); 
    }
    
    /**
     * returns current statement object
     * 
     * @return mysqli_statement
     */
    public function get() 
    {
        return $this->_statement[$this->_i];
    }

    /**
     * returns the insert ID of previous operation
     * 
     * returns false if no matching operation is found
     * 
     * @param void
     * @return int
     */
    public function insertId() 
    {
        $query = 'SELECT LAST_INSERT_ID() AS id';
        $q = $this->_dao->MySQLi->query($query);
        if (!$q)
            return false;
        $d = $q->fetch_object();
        return $d->id;         
    }

    /**
     * returns the number of rows in current result
     * 
     * @param void
     * @return int
     */
    public function numRows() 
    {
        if (isset($this->result) && $this->result) {
            return $this->result->num_rows;
        }
        if (isset($this->_statement[$this->_i]))
            return $this->_statement[$this->_i]->num_rows;
        return false;
    }

    /**
     * performs a query
     * 
     * @param string $query
     * @return mixed boolean or result object
     */
    public function query($query) 
    {
        $q = $this->_dao->MySQLi->query($query);
        if (!$q)
            return false;
        $this->result = $q;
        return true;
    }
        
    /**
     * prepares a statement
     * 
     * returns the key of the statement
     * 
     * @param string $statement
     * @return int
     */
    public function prepare($statement) {
        if (PVars::get()->debug) {
            $tm = microtime();
            PSurveillance::setPoint('statement_prepare'.$tm);
        }
        if (isset($this->result) && $this->result) {
            $this->result->close();
            unset($this->result);
        }
        $statement = $this->_dao->MySQLi->prepare($statement);
        if (!$statement) {
        	$e = new PException('Could not prepare statement!', 1000);
            $e->addInfo($this->_dao->getErrNo());
            $e->addInfo($this->_dao->getErrMsg());
            throw $e;
        }
        $this->_statement[] = $statement;
        end($this->_statement);
        $k = key($this->_statement);
        $this->_bound = array();
        if (PVars::get()->debug) {
            PSurveillance::setPoint('eostatement_prepare'.$tm);
        }
        $this->_i = $k;
        return $k;
    }
    
    /**
     * set the result pointer to offset
     * 
     * @param int $pos
     * @return boolean
     */
    public function seek($pos) 
    {
        if (!$this->result)
            return false;
        if ($pos >= $this->numRows())
            $pos = 0;
        if($this->result->data_seek($pos)) {
            $this->pos = $pos;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * sets the statement cursor to key
     * 
     * @param int $k
     * @return boolean
     */
    public function setCursor($k) 
    {
        if (isset($this->result) && $this->result) {
            $this->result->close();
        }
        if ($k != $this->_i) {
            @$this->_statement[$this->_i]->free_result();
        }
        $this->_bound = array();
        if (!is_array($this->_statement))
            return false;
        if (!array_key_exists($k, $this->_statement)) {
            return false;
        }
        $this->_i = $k;
        return true;
    }
}
?>