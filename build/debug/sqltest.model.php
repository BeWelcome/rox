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
        $all_by_username = array();
        $by_id = array();
        foreach ($this->getBWMembers() as $m) {
            $all_by_username[$m->Username] = new stdClass;
            $all_by_username[$m->Username]->m = $m;
            $all_by_username[$m->Username]->uu = array();
            $by_id[$m->id] = new stdClass;
            $by_id[$m->id]->m = $m;
        }
        foreach ($this->getTBUsers() as $u) {
            if (!isset($all_by_username[$u->handle])) {
                $all_by_username[$u->handle] = new stdClass;
                $all_by_username[$u->handle]->uu = array();
            }
            $all_by_username[$u->handle]->u = $u;
            $all_by_username[$u->handle]->uu[] = $u;
            if (!isset($by_id[$u->id])) {
                $by_id[$u->id] = new stdClass;
            }
            $by_id[$u->id]->u = $u;
        }
        $res = new stdClass();
        
        $res->orphan_total = array();
        $res->orphan_m = array();
        $res->orphan_u = array();
        $res->multi_orphan_u = array();
        $res->multi_u = array();
        $res->id_mismatch = array();
        
        foreach ($by_id as $x) {
            if (isset($x->m)) {
                $x->xm = $all_by_username[$x->m->Username];
            }
            if (isset($x->u)) {
                $x->xu = $all_by_username[$x->u->handle];
            }
        }
        
        foreach ($all_by_username as $username => $x) {
            if (isset($x->m)) {
                $x->xm = $by_id[$x->m->id];
            }
            switch (count($x->uu)) {
                case 0:
                    // one too little
                    if (!isset($x->m)) $res->orphan_total[] = $x;
                    else $res->orphan_m[] = $x;
                    break;
                case 1:
                    // exactly the right number :)
                    if (!isset($x->m)) $res->orphan_u[] = $x;
                    else if ($x->u->id != $x->m->id) $res->id_mismatch[] = $x;
                    break;
                default:
                    if (!isset($x->m)) $res->multi_orphan_u[] = $x;
                    else $res->multi_u[] = $x;
            }
        }
        
        $res->orphan_id_m = array();
        $res->orphan_id_u = array();
        $res->orphan_id_total = array();
        $res->username_mismatch = array();
        foreach ($by_id as $id => $x) {
            if (!isset($x->u) && !isset($x->m)) {
                $res->orphan_id_total[] = $x;
            } else if (!isset($x->u)) {
                $res->orphan_id_m[] = $x;
            } else if (!isset($x->m)) {
                $res->orphan_id_u[] = $x;
            } else if ($x->m->Username != $x->u->handle) {
                $res->username_mismatch[] = $x;
            }
        }
        
        $res->all = $res->all_by_username = $all_by_username;
        $res->all_by_id = $by_id;
        return $res;
    }
}


?>