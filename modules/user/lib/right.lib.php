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
 *
 * @see /htdocs/bw/lib/rights.php
 * @author Felix van Hove <fvanhove@gmx.de>
 *
 * @ modified by JeanYves :
 * MOD_right and MOD_flag, which are singleton will be defined from this class
 */
class MOD_right_flag {

	use SessionTrait;

    private $tableName;

	// These variables are uesed to save the context
	public $nomtable ;
	private $nomtablevolunteer ;
	private $tablescope ;
	private $tablelevel ;
	private $IdSession ;
	private $IdName ;

    protected $dao;

/**
*  By default it will be considerated that we are building a "rights"
*
*/
    function __construct()     {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
		$this->setSession();
    }

    protected function initialize($nomdetable="") {
        if ($nomdetable=='rights') {
    $this->nomtable=$nomdetable ;

    $this->nomtablevolunteer='rightsvolunteers' ;
    $this->tablescope='RightScope_' ;
    $this->tablelevel='RightLevel_' ;
    $this->IdName='IdRight' ;
    $this->IdSession='Right_' ;
        }
        else if ($nomdetable=='flags') {
            $this->nomtable=$nomdetable ;
            $this->nomtablevolunteer='flagsmembers' ;
            $this->tablescope='FlagScope_' ;
            $this->tablelevel='FlagLevel_' ;
            $this->IdName='IdFlag' ;
            $this->IdSession='Flag_' ;
        }
        else {
            die("Wrong table name ".$nomdetable." for MOD_right_flag") ;
        }
    }

    public function __destruct()
    {
        unset($this->_dao);
    }

    /**
     * FIXME: this is (with little exception)
     * copy-paste from /htdocs/bw/lib/rights.php; to be improved!
     *
     * @see /htdocs/bw/lib/rights.php
     */
    // -----------------------------------------------------------------------------

/** return the FlagLevel if the members has the Flag $Name
* optional Scope value can be send if the Scope is set to All then Scope
* will always match if not, the sentence in Scope must be find in RScope
* The function will use a cache in session
*  ($this->session->get('Param')->ReloadRightsAndFlags == 'Yes') is used to force Rights / Flags Reloading
* from scope beware to the "" which must exist in the mysal table but NOT in
* the $Scope parameter
* $OptionalIdMember  allow to specify another member than the current one, in this case the cache is not used
* This function is just an allias of the hasRight
*/
public function hasFlag($Name, $_Scope = "", $OptionalIdMember = 0) {
 return($this->hasRight($Name,$_Scope,$OptionalIdMember)) ;
} // end of hasFlag

/** return the RightLevel if the members has the Right $Name
* optional Scope value can be send if the Scope is set to All then Scope
* will always match if not, the sentence in Scope must be find in RScope
* The function will use a cache in session
* ($this->session->get('Param')->ReloadRightsAndFlags == 'Yes') is used to force Rights / Flags Reloading
* from scope beware to the "" which must exist in the mysal table but NOT in
* the $Scope parameter
* $OptionalIdMember  allow to specify another member than the current one, in this case the cache is not used
*/
public function hasRight($Name, $_Scope = "", $OptionalIdMember = 0)
{
	global $_SYSHCVOL;

	//if (!IsLoggedIn())
	$A = new MOD_bw_user_Auth();
	if (!$A->isBWLoggedIn()) {
		return (0); // No need to search for right if no member logged, he has no right
	}
	if ($OptionalIdMember != 0) { // In case we want to test for the rigt of a specific member, who is not the logged
		$IdMember = $OptionalIdMember;
	} else {
		$IdMember = $this->session->get('IdMember', 0);
	}

	$Scope = rtrim(ltrim($_Scope)); // ensure they are no extra spaces
	if ($Scope != "") {
		if ($Scope[0] != "\"")
		$Scope = "\"" . $Scope . "\""; // add the " " if they are missing
	}

	// First test if this is the logged in member, and if by luck his right is allready cached in his session variable
	if ((!$this->session->has($this->IdSession . $Name)) or
		($this->session->get('Param')->ReloadRightsAndFlags == 'Yes') or
		($OptionalIdMember != 0)) {

		    $str = '
SELECT SQL_CACHE Scope as Scope, Level
FROM '.$this->nomtablevolunteer.', '.$this->nomtable.'
WHERE IdMember=' . $IdMember . ' AND '.$this->nomtable.'.id='.$this->nomtablevolunteer.'.'.$this->IdName.' AND '.$this->nomtable.'.Name=\'' . $Name . '\' ORDER BY '.$this->nomtablevolunteer.'.created desc';

		//$query = mysql_query($str) or bw_error("function HasRight");
		//$row = mysql_fetch_object(mysql_query($str)); // LoadRow not possible because of recusivity
		$qry = $this->dao->query($str);
		$row = $qry->fetch(PDB::FETCH_OBJ);
		if (!isset ($row->Level)) {
			return (0); // Return false if the Right does'nt exist for this member in the DB
		}
		$rlevel = $row->Level;
        if ($rlevel == 0) {
            return (0);
        }
		$rscope = ltrim(rtrim($row->Scope)); // remove extra space
		if ($OptionalIdMember == 0) { // if its current member cache for next research
//			$this->session->get($this->IdSession . $Name)="set" ;  // Caching is not enable if this line is commented (but test are needed before uncomenting it)
			$this->session->set( $this->tablelevel . $Name, $rlevel );
			$this->session->set( $this->tablescope . $Name, $rscope );
		}
	}
	if ($Scope != "") { // if a specific scope is asked
		if ($rscope == "\"All\"") {
			if (($this->session->get("IdMember")) == 1)
				return (10); // Admin has all rights at level 10
			return ($rlevel);
		} else {
			if ((!(strpos($rscope, $Scope) === false)) or ($Scope == $rscope)) {
				return ($rlevel);
			} else
				return (0);
		}
	} else {
		if (($this->session->get("IdMember", 0)) == 1)
			return (10); // Admin has all rights at level 10
		return ($rlevel);
	}
}


/**
 * Checks, if the logged on member has any right by searching her
 * in the table $table.volunteers
 *
 * @return is true if the current user is logged on and
 * exists in table $table.volunteers
 * Improvment by JeanYves : if the member has not any right,
 *  a $this->session->get("hasRightAny")="no" is set, this will allow
 *  for a faster test at next attempt
 */
public function hasRightAny()
{
	global $_SYSHCVOL;

	// Test if in the session cache it is allready said that the member has no right
	if (($this->session->has( 'Param' ) and ($this->session->get('Param')->ReloadRightsAndFlags == 'Yes') and
	     ($this->session->has( 'hasRightAny' ) and
		 ($this->session->get('hasRightAny')=='no'))) ){

		 return false;
	}

    $A = new MOD_bw_user_Auth();
    if (!$A->isBWLoggedIn()) {
        return false;
    }

    $query = '
SELECT SQL_CACHE Level
FROM '.$this->nomtablevolunteer.'
WHERE IdMember=' . $this->session->get('IdMember');
    $qry = $this->dao->query($query);
    $row = $qry->fetch(PDB::FETCH_OBJ);
    if (!isset ($row->Level)) {
	 	 $this->session->set("hasRightAny" , "no"); // Put is session the info that the member has no right
        return false;
    }

    return true;
}


// These are alias name for function for compatibility
public function rightScope($Name, $Scope = "") {
			 return $this->TheScope($Name,$Scope) ;
}

public function flagScope($Name, $Scope = "") {
			 return $this->TheScope($Name,$Scope) ;
}


    /**
     * FIXME: this is (with little exception)
     * copy-paste from /htdocs/bw/lib/rights.php; to be improved!
     *
     * @see /htdocs/bw/lib/rights.php
     */
// -----------------------------------------------------------------------------
// return the Scope in the specific right
// The funsction will use a cache in session
//   ($this->session->get('Param')->ReloadRightsAndFlags == 'Yes') is used to force Rights and Flags Reloading
//  from scope beware to the "" which must exist in the mysal table but NOT in
// the $Scope parameter
public function TheScope($Name, $Scope = "")
{
	global $_SYSHCVOL;

	//if (!IsLoggedIn())
	$A = new MOD_bw_user_Auth();
	if (!$A->isBWLoggedIn()) {
		return false;
	}

	$IdMember = $this->session->get('IdMember', 0);
	if ((!$this->session->has($this->IdSession . $Name) or ($this->session->get('Param')->ReloadRightsAndFlags == 'Yes'))) {
		$str = '
SELECT
	SQL_CACHE
	Scope,
	Level
FROM
	'.$this->nomtablevolunteer.',
	'.$this->nomtable.'
WHERE
	IdMember=' . $IdMember . '
	AND '.$this->nomtable.'.id='.$this->nomtablevolunteer.'.'.$this->IdName.'
	AND '.$this->nomtable.'.Name=\'' . $Name . '\'';

		$qry = $this->dao->query($str);
		$row = $qry->fetch(PDB::FETCH_OBJ);

		if (!isset ($row->Level)) {
			return false;
		}
		$this->session->set( $this->tablelevel . $Name, $row->Level );
		$this->session->set( $this->tablescope . $Name, $row->Scope );
	}
	return ($this->session->get($this->tablescope . $Name));
} // end of TheScope


} // end of MOD_right_flag

class MOD_right extends MOD_right_flag {

    private static $_instance_right;

    function __construct() {
        parent::__construct();
        parent::initialize("rights") ;
    }
    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */

    public static function get()
    {
        if (!isset(self::$_instance_right)) {
            $c = __CLASS__;
            self::$_instance_right = new $c;
        }
        return self::$_instance_right;
    }
} // end of MOD_right

class MOD_flag extends MOD_right_flag {

    private static $_instance_flag;

    function __construct() {
						 parent::__construct();
						 parent::initialize("flags") ;

		}
    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */

    public static function get()
    {
        if (!isset(self::$_instance_flag)) {
            $c = __CLASS__;
            self::$_instance_flag = new $c;
        }
        return self::$_instance_flag;
    }
} // end of MOD_flag

?>
