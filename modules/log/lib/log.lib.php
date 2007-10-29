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
 * Writes and reads from the logs table.
 * Replacement for old BW LogStr method.
 * @author Felix van Hove, <fvanhove@gmx.de>
 */
class MOD_log {
    
    /**
     * if LOG2FILE is true, stuff gets additionally loged
     * to file
     */
    const LOG2FILE = false;

    /**
     * points to the file, which can be used for additionally
     * loging
     */
	const LOG_FILE = '/tmp/bw.log';
    
    /**
     * Singleton instance
     * 
     * @var MOD_log
     * @access private
     */
    private static $_instance;
    
    private function __construct()
    {
    
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
    }
    
    /**
     * singleton getter
     * 
     * @param void
     * @return PApps
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
     * Add one entry to log table. If log2file
     * is enabled write to logfile before write
     * to table. addslashes is executed on the message
     * string inside the method.
     * 
     * @param string $message the parameter to print
     * @param string $type the event, which causes the log
     * 				 method to be called
     */
    public function write($message = "", $type = "")
    {
        $message = addslashes($message);
        
        $idMember = 0;
        if (isset($_SESSION['IdMember'])) {
            $idMember = isset($_SESSION['IdMember']);
        }
        
        $ip = "127.0.0.1";
        if (isset ($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ip = ip2long($ip);
        
        if (MOD_log::LOG2FILE) {
            $text = date("c") . "|" .
                     $ip . "|" .
                     $idMember . "|" .
                     $type . "|" .
                     $message . "\n";
            error_log($text, 3, MOD_log::LOG_FILE);
        }
		
        $query = '
INSERT INTO
	`logs`
(
	`IdMember`,
	`Str`,
	`Type`,
	`created`,
	`IpAddress`
)
VALUES(
	' . $idMember . ',
	\'' . $message . '\',
	\'' . $type . '\',
	now(),
	\'' . $ip . '\'
)';
        $s = $this->dao->query($query);
   
    }
    
}
?>