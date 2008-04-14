<?php


/**
 * Unlike an application model,
 * an "entity" does actually represent a realworld (or imaginary) object!
 * 
 * This could be a group, a member, whatever.
 * 
 * The entity has its own database connection
 * and can answer questions about its associations in the database.
 *  
 */
class RoxEntityBase
{
    private $_store;
    private $_dao;
    
    public function __construct($store, $dao)
    {
        $this->_store = $store;
        $this->_dao = $dao;
    }
    
    protected function getDao() {
        return $model->dao;
    }
    
    public function __get($key)
    {
        if (isset($this->_store[$key])) {
            return $this->_store[$key];
        } else if (method_exists($this, $methodname = 'get_'.$key)) {
            return $this->_store[$key] = $this->$methodname();
        } else {
            return false;
        }
    }
    
    public function refresh_get($key)
    {
        $methodname = 'get_'.$key;
        return $this->_store[$key] = $this->$methodname();
    }
    
    protected function getValues()
    {
        return $this->_store;
    }
    
    
    //-------------------------------------------------------------
    // database queries...
    
    public function bulkLookup($query_string, $keyname = false)
    {
        $rows = array();
        if (!$sql_result = $this->_dao->query($query_string)) {
            // sql problem
        } else while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            if ($keyname && isset($row->$keyname)) {
                $rows[$row->$keyname] = $row;
            } else {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    
    
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
}


?>