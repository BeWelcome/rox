<?php


class MembersModel extends RoxModelBase
{
    public function getMemberWithUsername($username)
    {
        return $this->singleLookup(
            "
SELECT *
FROM members
WHERE Username = \"$username\"
            "
        );
    }
    
    public function getMemberWithId($id)
    {
        return $this->singleLookup(
            "
SELECT *
FROM members
WHERE id = \"$id\"
            "
        );
    }
}


class Member extends ReadOnlyObject
{
    public function __construct($vars) {
        parent::__construct($vars);
    }
    
    
}


?>