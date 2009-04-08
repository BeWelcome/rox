<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * MySQL statement object
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement_mysql.lib.php 127 2006-07-14 11:13:31Z kang $
 */
/**
 * MySQL statement object
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement_mysql.lib.php 127 2006-07-14 11:13:31Z kang $
 */
class PDBStatement_mysql extends PDBStatement 
{
    /**
     * @var PDB_mysql
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
        return @mysql_affected_rows($this->_dao->cr);
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
    public function execute($params = false) 
    {
        $stmt = $this->_statement[$this->_i];
        if (count($stmt[0]) == 1) {
            $stmt = $stmt[0][0];
        } else {
            $tokencnt = count($stmt[1]);
            if (is_array($params)) {
                if ($tokencnt != count($stmt[1])) {
                    throw new PException('Wrong parameter count!');
                }
            } elseif(is_array($this->_bound) && count($this->_bound) > 0) {
                if ($tokencnt != count($this->_bound)) {
                    $e = new PException('Wrong parameter count!');
                    $e->addInfo(print_r($this->_bound, true));
                    throw $e;
                }
                $params =& $this->_bound;
            } else {
                throw new PException('No parameters provided!');
            }
            $i = 0;
            foreach ($stmt[1] as $pos=>$val) {
                if ($val == '?') {
                   $val = $i;
                   $i++; 
                }
                if (!array_key_exists($val, $params) || !isset($params[$val])) {
                    $e = new PException('Parameter "'.$val.'" not set!');
                    $e->addInfo(print_r($this->_bound, true));
                    throw $e;
                }
                if ($params[$val] === null) {
                    $m = 'NULL';
                } else {
                    $m = is_int($params[$val]) ? $params[$val] : '\''.$this->_dao->escape($params[$val]).'\'';
                }
                $stmt[0][$pos] = $m;
            }
            $stmt = implode(' ', $stmt[0]);
        }
        if (PVars::get()->debug) {
            $start_time = microtime(true);
        }
        $q = @mysql_query($stmt, $this->_dao->cr);
        if (!$q) {
            $e = new PException('MySQL error!', 1000);
            $e->addInfo('Statement: '.$stmt);
            $e->addInfo($this->_dao->getErrNo());
            $e->addInfo($this->_dao->getErrMsg());
            throw $e;
        }
        $this->result = $q;
        $this->pos = 0;
        $q = PVars::get()->queries + 1;
        PVars::register('queries', $q);
        if (PVars::get()->debug) {
            $q = PVars::get()->query_history;
            $query_time = sprintf("%.1f", (microtime(true) - $start_time) * 1000);
            $q[] = "($query_time ms) $stmt";
            PVars::register('query_history', $q);
        }
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
        switch ($style) {
            case PDB::FETCH_BOTH:
            default:
                $res = @mysql_fetch_array($this->result, MYSQL_BOTH);
                break;
                
            case PDB::FETCH_ASSOC:
                $res = @mysql_fetch_array($this->result, MYSQL_ASSOC);
                break;
                
            case PDB::FETCH_NUM:
                $res = @mysql_fetch_array($this->result, MYSQL_NUM);
                break;
                
            case PDB::FETCH_OBJ:
                $res = @mysql_fetch_object($this->result);
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
    public function fetchColumn($pos = 0) 
    {
        $row = @mysql_fetch_row($this->result);
        if (!$row)
            return false;
        if (!array_key_exists($pos, $row))
            return false;
        return $row[$pos];
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
        $q = mysql_query($query, $this->_dao->cr);
        if (!$q)
            return false;
        $d = mysql_fetch_object($q);
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
        if (!isset($this->result) || !$this->result)
            return false;
        return mysql_num_rows($this->result);
    }
    
    /**
     * prepares a statement
     * 
     * returns the key of the statement
     * 
     * @param string $statement
     * @return int
     */
    public function prepare($statement) 
    {
        if (PVars::get()->debug) {
            $tm = microtime();
            PSurveillance::setPoint('statement_prepare'.$tm);
        }
        $tokens = preg_split('%((?<!\\\)(?:\?|:[a-z]+))%', $statement, -1, PREG_SPLIT_DELIM_CAPTURE);
        $newtokens = array();
        $rep = array();
        foreach ($tokens as $pos=>$t) {
            switch(true) {
                case preg_match('%^:[a-z]+$%', $t):
                case $t == '?':
                    $rep[$pos] = $t;
                    $newtokens[$pos] = $t;
                    break;
                
                default:
                    $newtokens[$pos] = preg_replace('%\\\(\?|:[a-z]+)%', '\\1', $t);
                    break;
            }
        }
        $this->_statement[] = array($newtokens, $rep);
        end($this->_statement);
        $k = key($this->_statement);
        $this->_bound = array();
        if (PVars::get()->debug) {
            PSurveillance::setPoint('eostatement_prepare'.$tm);
        }
        return $k;
    }
    
    public function query($query) {
        if (PVars::get()->debug) {
            $start_time = microtime(true);
        }
        $q = @mysql_query($query, $this->_dao->cr);
        if (!$q) {
            $e = new PException('MySQL error!', 1000);
            $e->addInfo('Statement: '.$query);
            $e->addInfo($this->_dao->getErrNo());
            $e->addInfo($this->_dao->getErrMsg());
            throw $e;
        }
        $this->result = $q;
        $this->pos = 0;
        $q = PVars::get()->queries + 1;
        PVars::register('queries', $q);
        if (PVars::get()->debug) {
            $q = PVars::get()->query_history;
            $query_time = sprintf("%.1f", (microtime(true) - $start_time) * 1000);
            $q[] = "($query_time ms) $query";
            PVars::register('query_history', $q);
        }
        return true;
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
        if(@mysql_data_seek($this->result, $pos)) {
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
            @mysql_freeresult($this->result);
            unset($this->result);
        }
        $this->_bound = array();
        if (!array_key_exists($k, $this->_statement)) {
            return false;
        }
        $this->_i = $k;
        return true;
    }
}
?>