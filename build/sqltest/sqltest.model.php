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
    
    public function getAll()
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
        return $all;
    }
}


?>