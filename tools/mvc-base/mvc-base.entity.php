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
    private $_method_cache;
    private $_dao;
    
    public function __construct($store, $dao)
    {
        $this->_store = $store;
        $this->_dao = $dao;
    }
    
    protected function getDao() {
        return $this->_dao;
    }
    
    protected function get_dao() {
        return $this->_dao;
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
    
    public function __call($methodname, $args)
    {
        $args_serialized = serialize($args);
        if (!isset($this->_method_cache[$methodname])) {
            $this->_method_cache[$methodname] = array();
        }
        if (!isset($this->_method_cache[$methodname][$args_serialized])) {
            $this->_method_cache[$methodname][$args_serialized] = call_user_func_array(array($this, 'get_'.$methodname), $args);
            return $this->_method_cache[$methodname][$args_serialized];
        }
        return $this->_method_cache[$methodname][$args_serialized];
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
    
    public function bulkLookup($query_string, $keynames = false)
    {
        $rows = array();
        if (!is_array($keynames)) {
            $keynames = array($keynames);
        }
        try {
            $sql_result = $this->_dao->query($query_string);
        } catch (PException $e) {
            echo '<pre>'; print_r($e); echo '</pre>';
            $sql_result = false;
            // die ('SQL Error');
        }
        if (!$sql_result) {
            // sql problem
            echo '<div>sql error</div>';
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