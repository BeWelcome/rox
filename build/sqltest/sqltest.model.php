<?php


class SqltestModel extends RoxModelBase
{
    public function getBWMembers()
    {
        return $this->bulkLookup(
            "
SELECT *
FROM members
            "
        );
    }
    
    public function getTBUsers()
    {
        return $this->bulkLookup(
            "
SELECT *
FROM user
            "
        );
    }
    
    public function analyse()
    {
        $all = array();
        foreach ($this->getBWMembers() as $m) {
            $all[$m->Username] = new stdClass;
            $all[$m->Username]->m = $m;
        }
        foreach ($this->getTBUsers() as $u) {
            if (!isset($all[$u->handle])) $all[$u->handle] = new stdClass;
            $all[$u->handle]->u = $u;
        }
        $res = new stdClass();
        $res->all = $all;
        
        $res->orphan_total = array();
        $res->orphan_m = array();
        $res->orphan_u = array();
        $res->id_mismatch = array();
        
        foreach ($all as $username => $x) {
            if (!isset($x->u) && !isset($x->m)) {
                $res->orphan_total[] = $x;
            } else if (!isset($x->u)) {
                $res->orphan_m[] = $x->m;
            } else if (!isset($x->m)) {
                $res->orphan_u[] = $x->u;
            } else if ($x->u->id != $x->m->id) {
                $res->id_mismatch[] = $x;
            }
        }
        
        
        return $res;
    }
}


?>