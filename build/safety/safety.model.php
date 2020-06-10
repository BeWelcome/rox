<?php
/**
 * Safety model class.
 *
 * @author sitatara
 */
class SafetyModel extends RoxModelBase
{
    /**
     * Default constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    public function getListOfMembers() {
        $query = "
            SELECT
                username
            FROM
                members, rights, rightsvolunteers
            WHERE
                members.Status = 'Active'
                AND members.Username <> 'SafetyTeam'
                AND members.id = rightsvolunteers.IdMember
                AND rights.`Name` = 'SafetyTeam'
                AND rightsvolunteers.IdRight = rights.id
                AND rightsvolunteers.Level > 0
            ORDER BY
                username
                ";
        $res = $this->dao->query($query);
        if (!$res) {
            return "";
        }
        $list = '<ul class="bullet">';
        while($row = $res->fetch(PDB::FETCH_OBJ)) {
            $list .= '<li>' . htmlspecialchars($row->username) . '</li>';
        }
        $list .= '</ul>';
        return $list;
    }
}
