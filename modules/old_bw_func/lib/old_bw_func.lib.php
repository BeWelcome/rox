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
 * This module define various standars way to produce various links, according to
 * provide parameters.
 * Typically link to profile with username
 * or link with the picture of the username
 * It is VERY IMPORTANT According to understand that according to the parameters the shape of the link
 * may look different. This is very useful to see quickly and graphically teh status of a member for example 
 * @author JeanYves 
 */

 class MOD_old_bw_func {
    

    /**
     * Singleton instance
     * 
     * @var MOD_old_bw_func
     * @access private
     */
    private static $_instance;
    
    public function __construct()
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
} // end of MOD_old_bw_func

$dir="../htdocs/bw/lib/" ;
require_once ($dir."config.php");
require_once($dir."FunctionsTools.php");
require_once($dir."session.php");
require_once($dir."bwdb.php");
require_once($dir."lang.php");
require_once("../htdocs/bw/layout/layouttools.php");
?>