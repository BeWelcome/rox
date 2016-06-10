<?php


class MOD_online
{
    protected $dao;
    protected $online_interval;
    private $_members_online_count;
    private $_guests_online_count;
    
    static private $_instance;
    
    private function __construct()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
        global $_SYSHCVOL;
        if (isset($_SYSHCVOL['WhoIsOnlineDelayInMinutes'])) {
            $this->online_interval = $_SYSHCVOL['WhoIsOnlineDelayInMinutes'];
        } else {
            $this->online_interval = 5;
        }
    }
    
    /**
     * as long as we have no decent dependency injection whatever,
     * we make this a singleton.
     */
    public static function get()
    {   
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    
    /**
     * Update activity trackers
     * in table membersonline and/or guestsonline
     * 
     * This function should be called in the bootstrap.
     *
     * @param int $ip
     * @param int $member_id
     */
    public function iAmOnline($ip, $member_id = false)
    {
        $time = time();
        $update_interval = $this->online_interval * 60 / 8;
        if (!$this->_session->has( 'last_online_counter_update_time' )) {
            // new session, so need an update
        } else if ($_SESSION['last_online_counter_update_time'] + $update_interval < $time) {
            // last update is more than one minute ago, so need a new one
        } else if (!$this->_session->has( 'last_online_counter_update_member_id' ) && $member_id) {
            // just logged in, so needs update
        } else if ($_SESSION['last_online_counter_update_member_id'] != $member_id) {
            // logged in as someone else? or already logged out?
            // need an update!
        } else {
            // no update necessary
            return;
        }
        
        $this->getSession->set( 'last_online_counter_update_member_id', $member_id );
        $this->getSession->set( 'last_online_counter_update_time', $time );
        if (!$member_id) {
            // not logged in
            $this->_guestIsOnline($ip);
        } else {
            // logged in
            $this->_memberIsOnline($ip, $member_id);
        }
    }
    
    
    private function _guestIsOnline($ip)
    {
        // REMOTE_ADDR is not set when run via CLI
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $appearance = $_SERVER['REMOTE_ADDR'];
        } else {
            $appearance = '127.0.0.1';
        }
        $lastactivity = 'whocares';
        
        $this->dao->query(
            "
REPLACE INTO guestsonline (IpGuest, appearance, lastactivity)
VALUES ('$ip', '$appearance', '$lastactivity')
            "
        );   
    }
    
    
    private function _memberIsOnline($ip, $member_id)
    {
        $lastactivity = 'whocares';
        
        // delete from guestsonline, because user is no longer a guest.
        $this->dao->query(
            "
DELETE FROM guestsonline
WHERE IpGuest = $ip
            "
        );
        
        $IdMember = $_SESSION['IdMember'];
        $appearance = $this->dao->escape($_SESSION['Username']);
        $Status = $_SESSION['Status'];
        
        $this->dao->query(
            "
REPLACE INTO online (`IdMember`, `appearance`, `lastactivity`, `Status`)
VALUES ('$IdMember', '$appearance', '$lastactivity', '$Status')
            "
        );
        
        $this->_checkIfMoreMembersThanEverAreOnline();
    }
    
    
    private function _checkIfMoreMembersThanEverAreOnline()
    {
        // TODO: does the table params and its idea really make sense???
        // TODO: is this an appropriate place to do the check?
        // Check, if a record (more members than ever before) is established
        if (!$result = $this->dao->query(
            "
SELECT recordonline
FROM params
            "
        )) {
            // eek, something wrong with query..
        } else if (!$row = $result->fetch(PDB::FETCH_OBJ)) {
            // ok, nothing found.. what to do?
        } else {
            // check if this is a new record!
            if ($this->howManyMembersOnline() > $row->recordonline) {
                // more members than ever before are online!!
                MOD_log::get()->write(
                   'New record established, '.$_SESSION['WhoIsOnlineCount'].' members online!',
                   'Record'
                );
                $recordonline = $_SESSION['WhoIsOnlineCount'];
                $this->dao->query(
                    "
UPDATE params
SET recordonline = $recordonline
                    "
                );
            }
        }
    }
    
    
    /**
     * find out how many members have been online
     * in the last $interval minutes
     *
     * @param int $interval
     * @return int
     */
    public function howManyMembersOnline($interval = false)
    {
        if (!$interval) {
            $interval = $this->online_interval; 
        }
        
        if (!isset($this->_members_online_count)) {
            // count online members
            if (!$result = $this->dao->query(
                "
SELECT COUNT(*) AS cnt
FROM online
WHERE online.updated > DATE_SUB(now(), INTERVAL $interval minute)
AND (online.Status in ('Active','Pending','NeedMore'))
                "
            )) {
                // didn't work
                $this->_members_online_count = 0;
            } else if (!$record = $result->fetch(PDB::FETCH_OBJ)) {
                // nothing found
                $this->_members_online_count = 0;
            } else {
                // found!!
                $this->_members_online_count = $record->cnt;
            }
        }
        return $this->_members_online_count; 
    }
    
    
    /**
     * find out how many guests have been online
     * in the last $interval minutes
     *
     * @param int $interval
     * @return int
     */
    public function howManyGuestsOnline($interval = false)
    {
        if (!$interval) {
            $interval = $this->online_interval; 
        }
                
        if (!isset($this->_guests_online_count)) {
            // count online guests
            if (!$result = $this->dao->query(
                "
SELECT COUNT(*) as cnt
FROM guestsonline
WHERE guestsonline.updated > DATE_SUB(now(), INTERVAL $interval minute)
                "
            )) {
                // hmm
                $this->_guests_online_count = 0;
            } else if (!$record = $result->fetch(PDB::FETCH_OBJ)) {
                // we are not amused, it didn't work
                $this->_guests_online_count = 0;
            } else {
                // yeah, it worked.
                $this->_guests_online_count = $record->cnt;
            }
        }
        return $this->_guests_online_count; 
    }
}


?>
