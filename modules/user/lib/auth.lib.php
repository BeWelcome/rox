<?php
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
/**
 * Authentication lib
 *
 * @package MOD_user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class MOD_user_Auth extends MOD_user 
{
    /**
     * @param string $sessionName The session key under which the user id may be found
     * @param string $tableName The user table name
     * @param int $authId The authentication id
     */
    public function __construct($sessionName = false, $tableName = false, $authId = false) 
    {
        parent::__construct($sessionName, $tableName);
        $this->authId = $authId;
    }
    
    /**
     * parsing given right
     * 
     * rights are always in the form:
     * RIGHTNAME[@APPLICATION]
     * 
     * if no application is provided, we assume it's a global right
     * 
     * @param string $right
     * @return array with keys "name" and "app" (optional)
     * @access private
     */
    private function _parseRight($right) 
    {
        $matches = array();
        if (!preg_match('/^([a-z0-9\-_.]+)(@([a-z0-9\-_.]+))?$/i', $right, $matches))
            return false;
        $ret = array();
        $ret[0] = $matches[1];
        $ret['name'] = $matches[1];
        if (isset($matches[3])) {
            $ret[1] = $matches[3];
            $ret['app'] = $matches[3];
        }
        return $ret;
    }
    
    /**
     * add a new application with given name
     * 
     * @param string $name
     * @return int application ID
     */
    protected function addApp($name) 
    {
        try {
            if ($appId = $this->appExists($name))
                return $appId;
            $name = strtolower($name);
            $query = '
INSERT INTO `mod_user_apps` 
(`id`, `name`) 
VALUES 
(
    '.$this->dao->nextId('mod_user_apps').',
    \''.$this->dao->escape($name).'\'
)';
            $s = $this->dao->query($query);
            return $s->insertId();
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }
    }
    
    /**
     * add a new group with given name
     * 
     * @param string $group
     * @return int group ID
     */
    public function addGroup($group) 
    {
        if ($id = $this->groupExists($group))
            return $id;
        try {
            $query = '
INSERT INTO `mod_user_authgroups` 
(`id`, `name`) 
VALUES 
(
    '.$this->dao->nextId('mod_user_authgroups').', 
    \''.$this->dao->escape($group).'\'
)';
            $s = $this->dao->query($query);
            return $s->insertId();
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }
    }
    
    /**
     * adds a right to a group
     * 
     * if either is not set, this method will create group and right
     * 
     * @param string $group group name
     * @param string $right right name 
     * @return boolean
     */
    public function addGroupRight($group, $right) 
    {
        $groupId = $this->addGroup($group);
        if (!$groupId)
            return false;
        $rightId = $this->checkRight($right);
        if (!$rightId)
            return false;
        $query = '
SELECT `group_id` FROM `mod_user_grouprights` WHERE `group_id` = '.(int)$groupId.' AND `right_id` = '.(int)$rightId.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 1)
            return true;
        if ($s->numRows() != 0)
            throw new PException('D.i.e.!');
        $s = $this->dao->query('
INSERT INTO `mod_user_grouprights` 
(`group_id`, `right_id`) 
VALUES 
(
    '.(int)$groupId.', 
    '.(int)$rightId.'
)');
        return true;
    }
    
    /**
     * adds an implication for given right
     * 
     * @param string $right right name, which implies
     * @param string $implies right name for right to imply
     * 
     * @return boolean
     */
    public function addImplication($right, $implies) 
    {
        if (!$rightId = (int)$this->checkRight($right))
            return false;
        if (!$impliesId = (int)$this->checkRight($implies))
            return false;
        $s = $this->dao->query('
SELECT `right_id` FROM `mod_user_implications` 
WHERE `right_id` = '.(int)$rightId.' AND `implies_id` = '.(int)$impliesId);
        if ($s->numRows() == 1)
            return true;
        if ($s->numRows() != 0)
            throw new PException('D.i.e.!');
        $s = $this->dao->prepare('INSERT INTO `mod_user_implications` (`right_id`, `implies_id`) VALUES (?, ?)');
        $s->prepare('UPDATE `mod_user_rights` SET `has_implied` = 1 WHERE `id` = ?');
        $s->setCursor(0);
        $s->bindParam(0, $rightId);
        $s->bindParam(1, $impliesId);
        $s->execute();
        $s->setCursor(1);
        $s->bindParam(0, $rightId);
        $s->execute();
        return true;
    }
    
    /**
     * returns the id if app exists or false
     * 
     * @param string $appName
     * @return mixed
     */
    public function appExists($appName) 
    {
        try {
            $appName = strtolower($appName);
            $s = $this->dao->query('SELECT `id` FROM `mod_user_apps` WHERE `name` = \''.$this->dao->escape($appName).'\'');
            if ($s->numRows() == 0)
                return false;
            if ($s->numRows() != 1)
                throw new PException('D.i.e.!');
            return $s->fetch(PDB::FETCH_OBJ)->id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }
    }
    
    /**
     * check if given auth name exists, creates if it does not
     * 
     * @param string $authName
     * @return mixed id or false
     */
    public function checkAuth($authName) 
    {
        try {
            $query = 'SELECT `id` FROM `mod_user_auth` WHERE `name` = \''.$this->dao->escape($authName).'\'';
            $q = $this->dao->query($query);
            if ($q->numRows() == 1) {
                return $q->fetch(PDB::FETCH_OBJ)->id;
            }
            if ($q->numRows() != 0)
                throw new PException('D.i.e.!');
            $query = 'INSERT INTO `mod_user_auth` (`id`, `name`) VALUES (?, ?)';
            $q = $this->dao->prepare($query);
            $id = $this->dao->nextId('mod_user_auth');
            $q->bindParam(0, $id);
            $q->bindParam(1, $authName);
            $q->execute();
            $id = $q->insertId();
            if (!$id || $id == -1)
                return false;
            return $id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }            
    }
    
    /**
     * checks if right exists
     * 
     * @param string $right right name
     * @param int $level level
     * @return mixed id or false
     */
    public function checkRight($right, $level = 0) 
    {
        try {
            if ($rightId = $this->rightExists($right))
                return $rightId;
            $right = $this->_parseRight($right);
            if (!$right)
                return false;
            if (isset($right['app'])) {
                if (!$appId = $this->addApp($right['app'])) {
                    return false;
                }
            } else {
                $appId = null;
            }
            $query = '
INSERT INTO `mod_user_rights` 
(`id`, `app_id`, `name`, `has_implied`, `level`) 
VALUES 
(
    '.$this->dao->nextId('mod_user_rights').', 
    '.(int)$appId.', 
    \''.$right['name'].'\', 
    0, 
    '.(int)$level.'
)';
            $s = $this->dao->query($query);
            return $s->insertId();
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }            
    }
    
    /**
     * checks if group exists
     * 
     * @param string $group
     * @return mixed id or false
     */
    public function groupExists($group) 
    {
        try {
            $s = $this->dao->query('SELECT `id` FROM `mod_user_authgroups` WHERE `name` = \''.$this->dao->escape($group).'\'');
            if ($s->numRows() == 0)
                return false;
            if ($s->numRows() != 1)
                throw new PException('D.i.e.!');
            return $s->fetch(PDB::FETCH_OBJ)->id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }            
    }
    
    /**
     * checks if current user has given right
     * 
     * @param string $right
     * @return boolean
     */
    public function hasRight($right) 
    {
        if (!$this->authId)
            return false;
        if (!$rightId = $this->checkRight($right))
            return false;
        if (!$right = $this->_parseRight($right))
            return false;
        if (PVars::get()->debug) {
            $t = microtime();
            PSurveillance::setPoint('MOD_user_auth'.$t);
        }
        $query = '
SELECT
    r.`id`
FROM `mod_user_auth` AS a
LEFT JOIN `mod_user_authrights` AS ar ON
    ar.`auth_id` = a.`id`
LEFT JOIN `mod_user_rights` AS r ON
    r.`id` = ar.`right_id`
LEFT JOIN `mod_user_groupauth` AS ga ON
    ga.`auth_id` = a.`id`
LEFT JOIN `mod_user_authgroups` AS g ON
    g.`id` = ga.`group_id`
LEFT JOIN `mod_user_grouprights` AS gr ON
    gr.`group_id` = g.`id`
LEFT JOIN `mod_user_implications` AS i ON
    r.`has_implied` = 1 AND i.`right_id` = r.`id`
LEFT JOIN `mod_user_rights` AS r2 ON
    r2.`id` = gr.`right_id`
LEFT JOIN `mod_user_implications` AS i2 ON
    r2.`has_implied` = 1 AND i2.`right_id` = r2.`id`
WHERE 
    a.`id` = '.(int)$this->authId.'
    AND 
    (r.`id` = '.(int)$rightId.' OR gr.`right_id` = '.(int)$rightId.' OR i.`implies_id` = '.(int)$rightId.' OR i2.`implies_id` = '.(int)$rightId.') 
        ';
        $s = $this->dao->query($query);
        if (!isset($right['app']))
            $right['app'] = null;
        if (PVars::get()->debug) {
            PSurveillance::setPoint('eoMOD_user_auth'.$t);
        }
        return $s->numRows();
    }
    
    /**
     * checks if given right exists
     * 
     * @param string $right
     * @return mixed id or false
     */
    public function rightExists($right) 
    {
        try {
            $right = $this->_parseRight($right);
            if (!$right)
                return false;
            if (!isset($right['app'])) {
                $query = '
        SELECT 
            r.`id` AS id,
            r.`name` AS name,
            r.`level` AS level,
            a.`id` AS app_id,
            a.`name` AS app_name
        FROM `mod_user_rights` AS r
        LEFT JOIN `mod_user_apps` AS a ON
            a.`id` = r.`app_id`
        WHERE
            r.`name` = \''.$this->dao->escape($right['name']).'\' AND a.`name` IS NULL 
                ';
                $s = $this->dao->query($query);
            } else {
                $query = '
        SELECT 
            r.`id` AS id,
            r.`name` AS name,
            r.`level` AS level,
            a.`id` AS app_id,
            a.`name` AS app_name
        FROM `mod_user_rights` AS r
        LEFT JOIN `mod_user_apps` AS a ON
            a.`id` = r.`app_id`
        WHERE
            r.`name` = \''.$this->dao->escape($right['name']).'\' AND a.`name` = \''.$this->dao->escape($right['app']).'\'
                ';
                $s = $this->dao->query($query);
            }
            if ($s->numRows() == 0)
                return false;
            if ($s->numRows() != 1)
                throw new PException('D.i.e.!');
            return $s->fetch(PDB::FETCH_OBJ)->id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }
    }
}
?>