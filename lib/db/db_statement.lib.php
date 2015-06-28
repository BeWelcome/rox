<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Base statement object
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Base statement object
 * 
 * All statements implement an iterator
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db_statement.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PDBStatement implements Iterator 
{
    /**
     * current value
     * 
     * @var mixed
     * @access private
     */
    private $_currentVar;
    /**
     * current position
     * 
     * @var int
     * @access protected
     */
    protected $pos;

    /**
     * iterator function
     * 
     * sets cursor to 0
     * 
     * @param void
     * @return mixed
     */
    public function rewind() 
    {
        if (!$this->result)
            return false;
        $this->seek(0);
        $this->_currentVar = $this->fetch(PDB::FETCH_OBJ);
        $this->pos = 0;
    }

    /**
     * iterator function
     * 
     * returns current value
     * 
     * @param void
     * @return mixed
     */
    public function current() 
    {
        if (!$this->result)
            return false;
        return $this->_currentVar;
    }

    /**
     * returns current position
     * 
     * @param void
     * @return int
     */
    public function key() 
    {
        if (!$this->result || !isset($this->pos))
            return false;
        return $this->pos;
    }

    /**
     * returns the next value or false
     * 
     * @param void
     * @return mixed
     */
    public function next() 
    {
        if (!$this->result || !isset($this->pos))
            return false;
        $this->_currentVar = $this->fetch(PDB::FETCH_OBJ);
        return $this->_currentVar;
    }

    /**
     * returns if current position is valid
     * 
     * @param void
     * @return boolean
     */
    public function valid() 
    {
        if (!$this->result || !isset($this->pos))
            return false;
        if (!$this->_currentVar)
            return false;
        return true;
    }

    /**
     * returns all rows from a query result
     *
     * @param bool $style
     * @return array
     */
    public function fetchAll($style = false) {
        $rows = array();
        while($rows[] = $this->fetch($style));
        array_pop($rows);  // pop the last row off, which is an empty row
        return $rows;
    }


}
?>