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
            $all[$m->Username]->uu = array();
        }
        foreach ($this->getTBUsers() as $u) {
            if (!isset($all[$u->handle])) {
                $all[$u->handle] = new stdClass;
                $all[$u->handle]->uu = array();
            }
            $all[$u->handle]->u = $u;
            $all[$u->handle]->uu[] = $u;
        }
        $res = new stdClass();
        $res->all = $all;
        
        $res->orphan_total = array();
        $res->orphan_m = array();
        $res->orphan_u = array();
        $res->multi_orphan_u = array();
        $res->multi_u = array();
        $res->id_mismatch = array();
        
        foreach ($all as $username => $x) {
            switch (count($x->uu)) {
                case 0:
                    // one too little
                    if (!$x->m) $res->orphan_total[] = $x;
                    else $res->orphan_m[] = $x;
                    break;
                case 1:
                    // exactly the right number :)
                    if (!$x->m) $res->orphan_u[] = $x;
                    else if ($x->u->id != $x->m->id) $res->id_mismatch[] = $x;
                    break;
                default:
                    if (!$x->m) $res->multi_orphan_u[] = $x;
                    else $res->multi_u[] = $x;
            }
        }
        
        
        return $res;
    }
}


?>