<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * Get information about a specific member.
 * A member is someone with a profile on BeWelcome.
 * The user is the person who is currently surfing the site.
 * This module is about members, not users.
 * 
 * 
 * Associated database tables:
 * - members
 * - memberspublicprofiles
 * 
 * 
 * An example for its use:
 * $layoutbits = MOD_layoutbits::get();  // get the singleton instance
 * $id = $geo->getCityID($cityname);
 * 
 * @author Andreas (bw/cs:lemon-head)
 */
class MOD_member
{
    private static $_dao;
    
    private $_userId;
    private $_username;
    
    // TODO: some more variables for caching
    
    
    /**
     * get the static database access object
     * somewhat like singleton design.
     * 
     * @return database_whatever_class dao
     */
    private static function _getDAO()
    {
        if (!isset(self::$_dao)) {
            $db = PVars::getObj('config_rdbms');
            if (!$db) {
                throw new PException('DB config error!');
            }
            self::$_dao = PDB::get($db->dsn, $db->user, $db->password);
        }
        return self::$_dao;
    }
    
    private function __construct($userId, $username)
    {
        $this->_userId = $userId;
        $this->_username = $username;
    }

    /**
     * returns the handle from a TB user, given tb user id
     *
     * @param int $user_id id of user
     *
     * @access public
     * @return string
     * @throws PException
     */
    public static function getUserHandle($user_id)
    {
        // insanity lies in the details. Any design where you have to do
        // boilerplate code like this should be thrown out immediately
        // after design
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $result = $dao->query("SELECT username FROM members WHERE id = ". intval($user_id));
        if ($result && $fetched = $result->fetch(PDB::FETCH_OBJ))
        {
            return $fetched->username;
        }
        return '';
    }

    /**
     * Creates a member object for a given username.
     * 
     * @param string $username
     * @return MOD_member a member object that provides access to member attributes.
     */
    public static function getMember_username($username)
    {
        if ($row = self::_getDAO()->query(
            'SELECT SQL_CACHE id '.
            'FROM members '.
            "WHERE Username='$username' "
        )->fetch(PDB::FETCH_OBJ)) {
            return new MOD_member($row->id, $username); 
        } else {
            return 0;
        }
    }
    
    /**
     * Creates a member object for a given user id.
     *
     * @param int $userId 
     * @return MOD_member a member object that provides access to member attributes.
     */
    public static function getMember_userId($userId)
    {
        if ($row = self::_getDAO()->query(
            'SELECT SQL_CACHE Username '.
            'FROM members '.
            "WHERE id='$userId' "
        )->fetch(PDB::FETCH_OBJ)) {
            return new MOD_member($userId, $row->Username);
        } else {
            return 0;
        }
    }
    
    public function getUsername($userId = false)
    {
        if ($userId) {
            $c = self::_getDAO();
            $query = 'SELECT `Username` FROM `members` WHERE `id` = \''.$c->escape($userId).'\'';
            $q = $c->query($query);
            $d = $q->fetch(PDB::FETCH_OBJ);
            if( !$d)
                return false;
            return $d->Username;
        } else return $this->_username;
    }
    
    public function getUserId()
    {
        return $this->_userId;
    }

    public function getTBuserId()
    {
           $c = self::_getDAO();
           $query = 'SELECT `id` FROM `user` WHERE `handle` = \''.$this->_username.'\'';
           $q = $c->query($query);
           $d = $q->fetch(PDB::FETCH_OBJ);
           if( !$d)
               return false;
           return $d->id;
    }
    
    public function getFromMembersTable($select_string)
    {
        return self::_getDAO()->query(
            'SELECT '.$select_string.' '.
            'FROM members '.
            "WHERE id='$this->_userId' "
        )->fetch(PDB::FETCH_OBJ);
    }
    
    
    public function hasPublicProfile()
    {
        if (self::_getDAO()->query(
            'SELECT '.
            'FROM memberspublicprofiles '.
            "WHERE IdMember='$_userId' "
        )->fetch(PDB::FETCH_OBJ)) {
            return true;
        } else {
            return false;
        }
    }
    
    
    
    public function getPreference_prefId($preference_id)
    {
        if ($row = self::_getDAO()->query(
            'SELECT memberspreferences.Value as value '.
            'FROM preferences,memberspreferences '.
            "WHERE memberspreferences.IdMember='$this->_userId' ".
            "AND preferences.id='$preference_id' ".
            "AND memberspreferences.IdMember='$preference_id'"
        )) {
            return $row->value;
        } else if ($row = self::_getDAO()->query(
            'SELECT DefaultValue '.
            'FROM preferences '.
            "WHERE preferences.id='$preference_id' "
        )) {
            return $row->DefaultValue;
        } else {
            return 0;
        }
    }
    
    
    public function getPreference_prefName($preference_name)
    {
        // TODO: cache the preferences in member object.
        if ($row = self::_getDAO()->query(
            'SELECT memberspreferences.Value as value '.
            'FROM preferences,memberspreferences '.
            "WHERE memberspreferences.IdMember='$this->_userId' ".
            "AND preferences.codeName='$preference_name' ".
            'AND memberspreferences.IdMember=preferences.id'
        )) {
            return $row->value;
        } else if ($row = self::_getDAO()->query(
            'SELECT DefaultValue '.
            'FROM preferences '.
            "WHERE preferences.codeName='$preference_name' "
        )) {
            return $row->DefaultValue;
        } else {
            return 0;
        }
    }
}
