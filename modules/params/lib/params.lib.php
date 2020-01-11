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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Collection of functions that create elements for a page.
 *
 * An example for its use:
 * $layoutbits = MOD_layoutbits::get();  // get the singleton instance
 * $id = $geo->getCityID($cityname);
 *
 * @author Andreas (bw/cs:lemon-head)
 */
class MOD_params
{
    use SessionTrait;

    /**
     * Singleton instance
     *
     * @var MOD_params
     * @access private
     */
    private static $_instance;
    private static $dao;

    public function __construct()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        self::$dao =& $dao;
        $this->setSession();
    }

    /**
     * singleton getter
     *
     * @param void
     * @return MOD_params
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    /**
     * wrapper for lazy loading and instantiating an HTMLPurifier object
     *
     * @access public
     * @return object
     */
    public static function loadParams()
    {
        if (empty(self::$_instance->session->get("Param")))
        {
            // moved from PAppModel
            // todo: move to a PROPER place
            // load the default current param values from the database
            // This must be done at each page reload, but only once, because Session["Param"] might need to be updated
            // It could also be a good idea to make some $this->BW_Param thing with it instead of using SESSION, but there is already many code with session
            $result = self::$dao->query("SELECT * FROM `params`");
            if (!$result) {
                    throw new Exception('Failed to retrieve $this->session->get("Param")!');
            }
            self::$_instance->session->set( "Param", $result->fetch(PDB::FETCH_OBJ) );
        }
    }

}
