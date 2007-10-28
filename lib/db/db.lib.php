<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Database access object
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Database access object
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db.lib.php 68 2006-06-23 12:10:27Z kang $
 */
abstract class PDB implements PDB_frame
{
    /**
     * Result fetch type selector (associative array) 
     */
    const FETCH_ASSOC = 101;
    /**
     * Result fetch type selector (indexed array) 
     */
    const FETCH_NUM   = 102;
    /**
     * Result fetch type selector (associative and indexed array) 
     */
    const FETCH_BOTH  = 103; 
    /**
     * Result fetch type selector (to bound variables)
     * 
     * @deprecated not yet implemented
     */
    const FETCH_BOUND = 104;
    /**
     * Result fetch type selector (stdClass object) 
     */
    const FETCH_OBJ   = 105;
    
    /**
     * parse the given DSN
     * 
     * @see http://www.php.net/manual/en/ref.pdo.php
     * @param string $dsn DSN
     * @return array contains the class name (index:0) and all args in the DSN in another array (index:1)
     */
    private static function _parseDSN($dsn) 
    {
        $dsn = preg_split('%[:;=]%', $dsn, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($dsn) || count($dsn) % 2 != 1) {
            throw new PException('DSN parse error!');
        }
        $c = __CLASS__.'_'.$dsn[0];
        if (!class_exists($c)) {
            throw new PException('Backend type not found!');
        }
        $args = array();
        for ($i = 1; $i < count($dsn); $i++) {
            if ($i % 2 == 0)
                continue;
            $args[$dsn[$i]] = $dsn[$i+1];
        }
        return array($c, $args);
    }
    
    /**
     * returns a database access object for given config
     * 
     * @param string $dsn DSN - see: http://www.php.net/manual/en/ref.pdo.php
     * @param string $user username
     * @param string $pw password
     */
    public static function get($dsn, $user = false, $pw = false) 
    {
        try {
            $conn = self::_parseDSN($dsn);
            $class = $conn[0];
            // determine if the connect method should be called statically
            $Ref = new ReflectionMethod($class, 'connect');
            if ($Ref->isStatic()) {
                $DB = call_user_func(array($class, 'connect'), $conn[1], $user, $pw);
            } else {
                $DB = new $conn[0];
                $DB->connect($conn[1], $user, $pw);
            }
            return $DB;
        } catch (PException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * returns the current backend as set in the configuration
     * 
     * @param void
     * @return string
     */
    public static function backend() 
    {
        $dsn = PVars::getObj('config_rdbms')->dsn;
        $dsn = self::_parseDSN($dsn);
        return $dsn[0];
    }
    
    /**
     * returns if the current backend as set in the config supports real prepared statements throgh an API
     * 
     * @param void
     * @return boolean
     */
    public function truePrepared() 
    {
        $backend = self::backend();
        return ($backend == __CLASS__.'_mysqli');
    }

    /**
     * returns a name for a sequence
     * 
     * currently appending string "_seq"
     * 
     * @param string $name table name
     * @return string
     */
    protected function getSequenceName($name) 
    {
        $name = preg_replace('%[^a-z0-9_.]%i', '_', $name);
        return $name.'_seq';
    }
}
?>