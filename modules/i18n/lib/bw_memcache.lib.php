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
 * object types:
 * - class MODS_bw_memcache handles everything to either query the database or find the entry in the memcache array
 * this is aimed to reduce database load for words and trad
 * - 
 * Author : jeanyves
 * April 2009
 */

use App\Utilities\SessionTrait;


/**
 * It needs the memcache extension to be activated in PHP and the $param->memcache entry to be set to true
 */

class trad { // This class will be used for a future advanced use
	public $id ; // will receive the id primary key for the record, this will be used as key
	public $IdLanguage ; // Will receive the language value
	public $Code ; // Will receive the code for the value (for words it is words.code for forum_trads it is forumtrads.IdTrad) ;
	public $StrValue ; // Will receive the value for corresponding $Code in the corresponding IdLanguage
	public $Status ; // 0 means the content is to be refreshed from the database;
					 // 1 means the content is to be considerated as up to date;
					 // 2 means the content is to be search in english
	public $HitCount ; // Used for start to count the hit of cache
}

class MOD_bw_memcache {
	use SessionTrait;

	private $_tablename;  // the name of the table the memcache module is going to refer
	private $_sentencecolumn;  // the name of the column in the table which is suppose to keep the value
	private $_idtradcolumn;  // the name of the column IdTrad (or code for words ...)
    private $_dao;  // database access object
	private $memcache ;
    
    
    /**
     * @tablename is the name of the table to consider
     * @Column is the name of the column which is supposed to have the value in the table
     */
    public function __construct($tablename="words",$Column="Sentence",$Code="code")   {
		$this->setSession();
		$this->_tablename=$tablename ;
		$this->_sentencecolumn=$Column ;
		$this->_idtradcolumn=$Code ;
		
        if ($this->IsToggle()) {
		
			if (!extension_loaded('memcache')) { // Check if the module is active ...
			
				$this->_session->get("Param")->memcache='False' ; // Set to false the memcache toggle to avoid to fail
				die(" extension_loaded('memcache')=".extension_loaded('memcache')." MOD_bw_memcache : memcache extension is not available on this server !") ;
			}

			$this->memcache=new MemCache ;
			$this->memcache->connect('localhost',11211) or die ("MOD_bw_memcache: Could not connect to memcache") ;
		}
	}
	
    /**
     * IsToggle returns true if memcache is active, it returns false elsewher
     */
	private function IsToggle() {
        if (empty($this->_session->get("Param")->memcache)) {
			return(false) ;
		}
        if ($this->_session->get("Param")->memcache!='True') {
			return(false) ;
		}
		return(true) ;
	} // end of IsToggle
	
	
    /**
     * This function is the core one
	 * it tries to find in memchache the given value for $Code in $IdLanguage
	 * if it fails, it then tries to query it from the database
	 *		if it success, 
	 *			if the value is marked as expired, it update it from the database, mark it as uptodate and returns the value to caller
	 *			if the value is not marked as expired it returns to caller (this is expected to be the most common case !)
	 *			it stored it in memcache and returns the value to caller
	 *		if it fails it store in memcache infor
     */
	 function GetValue($Code,$IdLanguage) {
        if ($this->IsToggle()) { // Is the memcache active ?
			$value=$this->memcache->get($Code."_".$IdLanguage) ;
			if ($value) {
				return($value) ;
			}
			else {
				$value=$this->LookUp($Code,$IdLanguage) ;
				if ($value) {
					$this->AddValue($Code,$IdLanguage,$value) ;
					return($value) ;
				}
				else {
					return(false) ;
				}
			}
		}
		else {
			return (false) ;
		}
	} // end of GetValue
	
    /**
     * This function adds a value in memcache
	 * @$Code : the code for the value
	 * @$IdLanguage : the language for the value
	 * @$value : the value
     */
	function AddValue($Code,$IdLanguage,$value) {
		$key=$Code.'-'.$IdLanguage ;
		$struct=new trad ;
		$struct->StrValue=$value ;
		$struct->HitCount=0 ;
		$struct->Status=1 ; // the value is valid
//		$this->memcache->set($Code."_".$IdLanguage,$value, MEMCACHE_COMPRESSED, 80000) ;
		$this->memcache->set($Code."_".$IdLanguage,$value) ;
	} // end of AddValue


    /**
     * This function replace a value in memcache
	 * @$Code : the code for the value
	 * @$IdLanguage : the language for the value
	 * @$value : the value
     */
	function ReplaceValue($Code,$IdLanguage,$value) {
		$key=$Code.'-'.$IdLanguage ;
		$struct=new trad ;
		$struct->StrValue=$value ;
		$struct->HitCount=0 ;
		$struct->Status=1 ; // the value is valid
//		$this->memcache->set($Code."_".$IdLanguage,$value, MEMCACHE_COMPRESSED, 80000) ;
		$this->memcache->replace($Code."_".$IdLanguage,$value,false,80000) ;
	} // end of AddValue

	
    /**
     * This function looks in the database for the value correspondig to a Code and a Language
	 * it returns false if it fails to find something
     */
	function LookUp($Code,$IdLanguage) {
		$SentenceCol=$this->_sentencecolumn ;
		$str="select id,".$SentenceCol." from ".$this->_tablename." where ".$this->_idtradcolumn."='".$Code."' and ShortCode='".$IdLanguage."'" ;

        $qry = mysql_query($str);
        if (!$qry) {
            die('bw_memcache->LookUp("'.$Code.'","'.$IdLanguage.'" str=['.$str.']) failed !');
        }
        $row = mysql_fetch_object($qry);
		if (!empty($row->id)) {
			return($row->$SentenceCol) ;
		}
		return(false) ;
	}
} // end of MOD_bw_memcache