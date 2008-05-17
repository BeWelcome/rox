<?php


class RoxModelBase extends PAppModel
{
    public function bulkLookup($query_string, $keynames = false)
    {
        $rows = array();
        if (!is_array($keynames)) {
            $keynames = array($keynames);
        }
        try {
            $sql_result = $this->dao->query($query_string);
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