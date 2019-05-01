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

use App\Utilities\SessionTrait;

/**
 * Writes and reads from the logs table.
 * Replacement for old BW LogStr method.
 * @author Felix van Hove, <fvanhove@gmx.de>
 */
class MOD_log {
    use SessionTrait;
    
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

    /** @var PDB */
    private $dao;

    /**
     * MOD_log constructor.
     * @throws Exception
     * @throws PException
     */
    private function __construct()
    {
        $this->setSession();
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
     * @return MOD_log
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
     * @param int $IdMember is the id of the member the log will be applied to
     * @param string $message the parameter to print
     * @param string $type the event, which causes the log
     *                 method to be called
     *
     * BeWare this function does a nasty parameter change with the $this->_session->get("IdMember")
     * but it restores it as it was before in any cases
     *
     * @throws PException
     */
    public function writeIdMember($IdMember,$message = "", $type  = "Log")
    {
			if ($this->_session->has( "IdMember" )) {
				$IdMemberBefore=$this->_session->get("IdMember") ;
				$this->_session->set("IdMember", $IdMember);
				$this->write($message, $type) ;
				$this->_session->set("IdMember", $IdMemberBefore) ;
			}
			else {
				$this->_session->set("IdMember", $IdMember);
				$this->write($message, $type) ;
				$this->_session->remove("IdMember") ;
			}
		} // end writeIdMember


    /**
     * Add one entry to log table. If log2file
     * is enabled write to logfile before write
     * to table. addslashes is executed on the message
     * string inside the method.
     *
     * @param string $message the parameter to print
     * @param string $type the event, which causes the log
     *                 method to be called
     * @throws PException
     */
    public function write($message = "", $type  = "Log")
    {
	  	 global $_SYSHCVOL; // will be needed to retrieve the database used for logs

        $message = addslashes($message);
		if ($type=="Bug") {
			$message=$message." <font color=\"red\">".__FILE__ ." Line <b>".__LINE__."</b></font>" ;
		}
        
        $idMember = 0;
        if ($this->_session->has( 'IdMember' )) {
            $idMember = $this->_session->get('IdMember');
        }
        
        if (MOD_log::LOG2FILE) {
            $text = date("c") . "|" .
                     $idMember . "|" .
                     $type . "|" .
                     $message . "\n";
            error_log($text, 3, MOD_log::LOG_FILE);
        }
		 if (!empty($_SYSHCVOL['ARCH_DB'])) {
		 	$DB_ARCH='`'.$_SYSHCVOL['ARCH_DB'].'`.' ;		 	
		 }
		 else {
		 	$DB_ARCH='' ;
		 }
        $query = 'INSERT INTO '. $DB_ARCH.'`logs`
(
	`IdMember`,
	`Str`,
	`Type`,
	`created`
) 
VALUES(
	' . $idMember . ',
	\'' . $message . '\',
	\'' . $type . '\',
	now()
)';
 
       $res = $this->dao->query($query);
		if (!$res) 	{ // If the query has failed, log something in the text log file, and after rais the exception
            $text = "Execption raised : in MOD_Log->Write() ".date("c") . "|" .
                     $idMember . "|" .
                     $type . "|" .
                     $message . "\n";
            error_log($text, 3, MOD_log::LOG_FILE);
			 throw new PException('MOD_Log->Write() failed !');
		}
   
    }
    
}
