<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * "Interface" to PHP
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: phpi.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PPHP {
    public static function os() {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            return 'WIN';
        } else {
            // assuming unix
            return 'UNIX';
        }
    }
    
    public static function assertExtension($name) {
      // stripped by jsfan (2/6/2012)
      // dl() deprecated in PHP 5.3
      /*
        if (!extension_loaded($name)) {
            $dlext = self::os() == 'WIN' ? '.dll' : '.so';
            $dlprefix = self::os() == 'WIN' ? 'php_' : '';
            @dl($dlprefix . $name . $dlext);
            return extension_loaded($name);
        }
        return true; */
      return extension_loaded($name);
    }
    
    public static function PExit() {
        session_write_close();
        exit();
    }
}
?>