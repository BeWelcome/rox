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
 * - class MOD_words: One global object of this type, to handle all the translation requests.
 * - 
 */


/**
 * Enables us to use content from words table of BW from within the platform PT structure.
 * Instantiate in the first lines of your template, then call the "get" method.
 *
 * @see     /htdocs/bw/lib/lang.php
 *
 * FIXME: In need of categories to be able to fetch arrays of texts instead of every
 * single text separately.
 * TODO: tracking of unused words
 * FIXME: integrate $_SESSION['TranslationArray'] - but do we really need it?
 */

class MOD_words
{
    private $_lang;  // the active language
    private $_trMode;  // the translation mode - can be browse, translate, or edit
    private $_whereCategory = '';
    private $_offerTranslationLink = false;
    private $_langWrite = 0;
    /*private $_prepared = array();*/
    static private $_buffer = array();
    private $_dao;  // database access object
    
    
    /**
     * @param string $category optional value to set the page of the texts
     * 				 we're looking for (this needs an additional column in the
     * 				 words table)
     */
    public function __construct($category=null)
    {
        $this->_lang = PVars::get()->lang;

        if (!empty($category)) {
            $this->_whereCategory = ' `category`=\'' . $category . '\'';
        }
        if (isset($_SESSION['IdLanguage']))
            $this->_langWrite = $_SESSION['IdLanguage'];
        else $this->_langWrite = 0;

        $db_vars = PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
        $this->_dao =& $dao;

        $R = MOD_right::get();
        if ($R->hasRight("Words", $this->_lang)) {
            $this->_offerTranslationLink = true;
        }
        
        // read translation mode from $_SESSION['tr_mode']
        if (array_key_exists("tr_mode", $_SESSION)) {
            $this->_trMode = $_SESSION['tr_mode'];
        } else if (array_key_exists("tr_mode", $_SESSION)) {
            $this->_trMode = $_SESSION['tr_mode'];
        } else if ($this->_offerTranslationLink) {
            $this->_trMode = 'translate';
        } else {
            $this->_trMode = 'browse';
        }
        switch ($this->_trMode) {
            case 'browse':
            case 'proofread':  // not yet implemented
                break;
            case 'edit':
            case 'translate':
                if ($this->_offerTranslationLink) break;
            default:
                if ($this->_offerTranslationLink) {
                    $this->_trMode = 'translate'; 
                } else {
                    $this->_trMode = 'browse';
                }
        }
    }
    
    
    public function getTrMode() {
        return $this->_trMode;
    }
    
    
    public static function trLinkBufferSize()
    {
        return sizeof(self::$_buffer);
    }
    
    
    public function translationLinksEnabled() {
        return $this->_offerTranslationLink;
    }
    

    /**
     * Returns a translation for the keycode, and puts a translation link on the buffer. 
     * @return string the translated word
     */
    public function getBuffered($code)
    {
        $args = func_get_args();
        array_shift($args);
        
        $word = $this->_lookup($code, $args);
        
        return $this->_text_and_buffer($word);
    }
    
    function getForScript($code)
    {
        return addslashes($this->getBuffered($code));
    }
    
    function __call($code, $args) {
        return $this->_text_with_tr($this->_lookup($code, $args));
    }
    
    
    function __get($code) {
        return $this->_text_with_tr($this->_lookup($code, array()));
    }
    
    
    /**
     * does the same as getBuffered($code, ...)
     * the function is here for backwards convenience
     */
    public function getSilent($code)
    {
        $args = func_get_args();
        array_shift($args);
        
        $word = $this->_lookup($code, $args);
        
        return $this->_text_and_buffer($word);
    }
    
    
    
    
    /**
     * any translation items that were remembered in the buffer are now flushed!
     */
    public function flushBuffer()
    {
        
        $result = "";
        if($this->_offerTranslationLink) {
            foreach(self::$_buffer as $tr_link_string) {
                $result .= $tr_link_string;
            }
        }
        // make the buffer empty
        self::$_buffer = array();
        return $result;
    }

    
    /**
     * does the same as getFormatted($code, ...)
     */    
    public function get($code)
    {
        $args = func_get_args();
        array_shift($args);
        
        $word = $this->_lookup($code, $args);
        
        return $this->_text_with_tr($word);
    }
    
    
    
    /**
     * Looks up (localized) texts in BW words table.
     * Newlines are replaced by HTML breaks, backslashes are stripped off.
     * Takes a variable number of arguments as c-style formatted string.
     *
     * @see wwinlang in /lib/lang.php
     * @param   string  $code keyword for finding text, not allowed to be empty
     * @param   string  $? formatted according to a variable number of arguments
     * @param   ... arguments to be inserted in the string
     * @return  string  localized text, in case of no hit the word keycode, evtl with tr links
     */  
    public function getFormatted($code)
    {
        $args = func_get_args();
        array_shift($args);
        
        $word = $this->_lookup($code, $args);
        
        return $this->_text_with_tr($word);
    }
    
    
    /**
     * If we want another than the active language
     *
     * @param string $code the word keycode
     * @param string $lang the language code
     * @return string localized text, evtl with tr link
     */
    public function getInLang($code, $lang)
    {
        $args = func_get_args();
        array_shift($args);  // need a second array shift, because of 2 default arguments in function
        array_shift($args);
        
        $word = $this->_lookup($code, $args, $lang);
        
        return $this->_text_with_tr($word);
    }
    
    
    /**
     * If we want another than the active language
     *
     * @param string $code the word keycode
     * @param string $lang the language code
     * @return string localized text, evtl with tr link
     */
    public function getBufferedInLang($code, $lang)
    {
        $args = func_get_args();
        array_shift($args);  // need a second array shift, because of 2 default arguments in function
        array_shift($args);
        
        $word = $this->_lookup($code, $args, $lang);
        
        return $this->_text_and_buffer($word);
    }
    
    
    /**
     * creates a string that contains the translated word and evtl a tr link. 
     *
     * @param LookedUpWord $word an object of type LookedUpWord, containing all the stuff from DB lookup
     * @param array $args the arguments to be inserted in the translated word
     * @return string the string to be used in the webpage
     */
    private function _text_with_tr($word)
    {
        if (! $this->_offerTranslationLink) {
            return $word->text();
        } else {
            switch($word->get_tr_success()) {
                case LookedUpWord::NO_TR_LINK:
                    return $word->text();
                case LookedUpWord::MISSING_WORD:
                    // string does not contain hyperlinks!
                    return $word->clickableText();
                case LookedUpWord::MISSING_TR:
                case LookedUpWord::OBSOLETE:
                    // need an obvious translation link!
					if (strstr($_SERVER['PHP_SELF'],"/bw/")!==false) { // If we are in an old BW page (todo this is not the perfect solution)
						return $word->text();
					}
					else {
						return $word->clickableText();
					}
                default:
                    // create a tr link behind (that will be hidden) 
                    return $word->text().$word->standaloneTrLink();
            }
        }
    }
    
        
    
    private function _text_and_buffer($word)
    {
        if ($word->get_tr_success() != LookedUpWord::NO_TR_LINK) {
            if(!array_key_exists($word->getCode(), self::$_buffer)) {
                self::$_buffer[$word->getCode()]=$word->standaloneTrLink();
            }
        }
        return $word->text();
    }
    
    
    
    /**
     * looks up a word keycode in the DB, and returns an object of type LookedUpWord.
     * If a translation in the intended language is not found, it uses the English version.
     * If no English definition exists, the keycode itself is used.   
     * 
     * @param unknown_type $code the key code for the db lookup
     * @return LookedUpWord information that is created from the word lookup
     */
    private function _lookup($code, $args, $lang = false)
    {
        if($lang == false) {
            $lang = $this->_lang;
        }
        
        
        if(! $this->_offerTranslationLink) {
            // normal people don't need the tr stuff
            $row = $this->_lookup_row($code, $lang);
            if (!$row && $lang != 'en') {
                // try in English
                $row = $this->_lookup_row($code, 'en');
            }
            if(!$row) {
                // use the plain key code
                $lookup_result = $code;
            } else {
                // use the row that has been found
                $lookup_result = $this->_modified_sentence_from_row($row, $args);
            }
            return new LookedUpWord($code, $lang, $lookup_result);
        } else {
            // for translators, the LookedUpWord object needs more info
            $tr_quality = LookedUpWord::FINE;
            $row = $this->_lookup_row($code, $lang);
            if ($row) {
                $lookup_result = $this->_modified_sentence_from_row($row, $args);
                if($lang == 'en') {
                    $tr_success = LookedUpWord::SUCCESSFUL;
                } else {
                    $row_en = $this->_lookup_row($code, 'en');
                    if($this->_is_obsolete($row, $row_en)) {
                        $tr_success = LookedUpWord::OBSOLETE;
                    } else {
                        $tr_success = LookedUpWord::SUCCESSFUL;
                    }
                }
            } else if($lang != 'en') {
                // try in English
                $row = $this->_lookup_row($code, 'en');
                if($row) {
                    // use English version
                    $tr_success = LookedUpWord::MISSING_TR;  // at least that bad
                	$lookup_result = $this->_modified_sentence_from_row($row, $args);
                } else {
                    // no translation found
                    $tr_success = LookedUpWord::MISSING_WORD;
                    $lookup_result = $code;
 	            }
            } else {
                // no translation found
                $tr_success = LookedUpWord::MISSING_WORD;
                $lookup_result = $code;
            }
            switch ($this->_trMode) {
                case 'browse':
                    $tr_success = LookedUpWord::NO_TR_LINK;
                    break;
                case 'proofread':
                    // does not yet exist.
                    break;
                case 'translate':
                    if($tr_success == LookedUpWord::SUCCESSFUL) {
                        $tr_success = LookedUpWord::NO_TR_LINK;
                    }
                    break;
                case 'edit':
                    // no need to do anything
                    break;
                }
	        return new LookedUpWord($code, $lang, $lookup_result, $tr_success, $tr_quality);
        }
    }
    
    
    
    /**
     * Reads the (modified) translation sentence from a row in the database.
     * Modifications:
     *  - stripslashes
     *  - n12br
     *
     * @param dbrow $row
     * @return string modified sentence from db
     */
    private function _modified_sentence_from_row($row, $args)
    {
        $lookup_string = nl2br(stripslashes($row->Sentence));
        while (!$res = @vsprintf($lookup_string, $args)) {
            // if not enough arguments given, fill up with dummy arguments
            $args[] = ' -x- '; 
        }
        return $res;
    }
    
    
    /**
     * find out if a translation is obsolete - which depends on the timestamps of last update.
     *
     * @param dbrow $row
     * @param dbrow $row_en
     * @return boolean comparison of the word update timestamps.
     */
    private function _is_obsolete($row, $row_en)
    {
        if($row_en && $row) {
            return ($row->updated) < ($row_en->updated);
        } else {
            // English definition is missing
            return true;
        }
    }
    
    
    
    /**
     * looks up only one row in the database
     *
     * Looks up (localized) texts in BW words table according to provided
     * language.
     * 
     * @see wwinlang in /lib/lang.php
     * @param   string  $code keyword for finding text, not allowed to be empty
     * @param   string  $lang 2-letter code for language
     * @return dbrow an object representing one row in the database (?)
     */
    private function _lookup_row($code, $lang)
    {
        $whereCategory = $this->_whereCategory;
        
        /* we still need to find a clear parameter handling for this
        if (!empty($category)) {
            $whereCategory = ' `category`=\'' . $category . '\'';
        }
        */
        
        if (is_numeric($code)) {
            $query =
                "SELECT SQL_CACHE `Sentence`, `donottranslate`, `updated` ".
                "FROM `words` ".
                "WHERE `id`=" . $this->_dao->escape($code)
            ;
        } else {
        	// TODO: store translation quality in database!
            $query =
                "SELECT SQL_CACHE `Sentence`, `donottranslate`, `updated` ".
                "FROM `words` ".
                "WHERE `code`='" . $code . "' and `ShortCode`='" . $lang . "'"
            ;
        }
        
        $q = $this->_dao->query($query);
        $row = $q->fetch(PDB::FETCH_OBJ);
        
        return $row;
    }
    
    
    
    /**
     * should return an array of LookedUpWord objects, for caching purposes.
     * to be implemented!!
     * (hmm, is this really benefitial?)
     *
     * @param array $array_of_codes an array of word keycodes.
     * @return array an array of LookedUpWord objects
     */
    private function _bulk_lookup($array_of_codes)
    {
    	// TODO: implement _bulk_lookup for words from DB
    }
    
    
    private function _bulk_lookup_rows($array_of_codes, $lang)
    {
    	// we assume we have only word keycodes, no word IDs
        // TODO: store translation quality in database!
        $query =
            "SELECT SQL_CACHE `Sentence`, `donottranslate`, `updated` ".
            "FROM `words` ".
            "WHERE `code` IN ('" . implode($array_of_codes, "', '") . "') ".
            "AND `ShortCode`='" . $lang . "'"
        ;
        
        $q = $this->_dao->query($query);
        $row = $q->fetch(PDB::FETCH_OBJ);
        
        return $row;
    }



    /**
	  * retuns a string where 
     * @param $ss the string where to replace \n
     * @param $RepalceWith a boolean to say wether the replace shoud occur or not
     * @return string where \n are replaced with <br \> if the ReplaceWith parameter was true
     */
	 private function ReplaceWithBr($ss,$ReplaceWith=false) {
		if (!$ReplaceWith) return ($ss);
		return(str_replace("\n","<br \>",$ss));
	 }


    /**
     * @param $IdTrad the id of a memberstrads.IdTrad record to retrieve
	  * @param $ReplaceWithBr allows 
     * @return string translated according to the best language find
     */
    public function mTrad($IdTrad,$ReplaceWithBr=false) {

	 		$AllowedTags = "<b><i><br><br/><p>"; // This define the tags wich are not stripped inside a membertrad
			if (empty($IdTrad)) {
			   return (""); // in case there is nothing, return and empty string
			}
			else  {
			   if (!is_numeric($IdTrad)) {
			   	  die ("it look like you are using MOD_WORD::mTrad with and allready translated word, a memebrstrads.IdTrad is expected and it should be numeric !") ;
			   }
			}
		
			if (isset($this->_langWrite)) {
		 	   	$IdLanguage=$this->_langWrite;
			} else {
		 		$IdLanguage=0 ; // by default language 0
			} 
			// Try default language
        	$query ="SELECT SQL_CACHE `Sentence` FROM `memberstrads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$IdLanguage ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
				} 
				else {
			   	    return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
				}
			}
			// Try default eng
        	$query ="SELECT SQL_CACHE `Sentence` FROM `memberstrads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=0" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
				} else {
				   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
				}
			}
			// Try first language available
     	$query ="SELECT SQL_CACHE `Sentence` FROM `memberstrads` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
				} else {
				   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
				}
			}
			MOD_log::get()->write("mTrad Anomaly : no entry found for IdTrad=#".$IdTrad, "Bug");
			return (""); // If really nothing was found, return an empty string
	 } // end of mTrad
	 
    /**
	* @param $IdTrad the id of a forum_trads.IdTrad record to retrieve
	 * @param $ReplaceWithBr allows 
	 * @parame $IdForceLanguage optional can be use to force the routine to try to choose a specific language
	* @return string translated according to the best language find
	*/
    public function fTrad($IdTrad,$ReplaceWithBr=false,$IdForceLanguage=-1) {
		
			global $fTradIdLastUsedLanguage ; // Horrible way of returning a variable you forget when you designed the method (jyh)
			$fTradIdLastUsedLanguage=-1 ; // Horrible way of returning a variable you forget when you designed the method (jyh)

			if (empty($IdTrad)) {
			   return (""); // in case there is nothing, return and empty string
			}
			else  {
			   if (!is_numeric($IdTrad)) {
			   	  die ("it look like you are using forum::fTrad with and allready translated word, a forum_trads.IdTrad is expected and it should be numeric ! IdTrad=[".$IdTrad."]") ;
			   }
			}
		
			if ($IdForceLanguage<=0) {
				if (isset($_SESSION['IdLanguage'])) {
					$IdLanguage=$_SESSION['IdLanguage'] ;
				}
				else {
					$IdLanguage=0 ; // by default language 0
				} 
			}
			else {
				$IdLanguage=$IdForceLanguage ;
			}
			// Try default language
        	$query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$IdLanguage ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language " . $IdLanguage . " with forum_trads.IdTrad=" . $IdTrad, "Bug");
				} 
				else {
							$fTradIdLastUsedLanguage=$row->IdLanguage ;
			   	    return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
				}
			}
			// Try default eng
        	$query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=0" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language 1 (eng) with forum_trads.IdTrad=" . $IdTrad, "Bug");
				} else {
					 $fTradIdLastUsedLanguage=$row->IdLanguage ;
				   return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			// Try first language available
     	$query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence (any language) forum_trads.IdTrad=" . $IdTrad, "Bug");
				} else {
					 $fTradIdLastUsedLanguage=$row->IdLanguage ;
				   return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			$strerror="fTrad Anomaly : no entry found for IdTrad=#".$IdTrad ;
			MOD_log::get()->write($strerror, "Bug");
			return ($strerror); // If really nothing was found, return an empty string
	 } // end of fTrad	 
    
/*
* author jeanyves
* The following function are of generic use (for forums, for polls)
* they allow for a update of forum trads
*
*/

	 
/** ------------------------------------------------------------------------------
* function : MakeRevision
* this is a copy of a function allready running in Function tools
* this is not the best place for it, please contact jeanyves if you feel like to change this
* MakeRevision this function save a copy of current value of record Id in table
* TableName for member IdMember with Done By reason
* @$Id : id of the record
* @$TableName : table where the revision is to be done 
* @$IdMemberParam : the member who cause the revision, the current memebr will be use if this is not set
* @$DoneBy : a text to say why the update was done (this must be one of the value of the enum 'DoneByMember','DoneByOtherMember","DoneByVolunteer','DoneByAdmin','DoneByModerator')
*/
function MakeRevision($Id, $TableName, $IdMemberParam = 0, $DoneBy = "DoneByMember") {
	global $_SYSHCVOL; // this is needed to retrieve the optional mem
	$IdMember = $IdMemberParam;
	if ($IdMember == 0) {
		$IdMember = $_SESSION["IdMember"];
	}
	$qry = mysql_query("SELECT * FROM " . $TableName . " WHERE id=" . $Id);
	if (!$qry) {
	  throw new PException("forum::MakeRevision fail to select id=#".$Id." from ".$TableName);
	}

	$count = mysql_num_fields($qry);
	$rr = mysql_fetch_object($qry);

	$XMLstr = "";
	for ($ii = 0; $ii < $count; $ii++) {
		$field = mysql_field_name($qry, $ii);
		$XMLstr .= "<field>" . $field . "</field>\n";
		$XMLstr .= "<value>" . $rr-> $field . "</value>\n";
	}
	$str = "INSERT INTO " . $_SYSHCVOL['ARCH_DB'] . ".previousversion(IdMember,TableName,IdInTable,XmlOldVersion,Type) VALUES(" . $IdMember . ",'" . $TableName . "'," . $Id . ",'" . mysql_real_escape_string($XMLstr) . "','" . $DoneBy . "')";
	if (!$qry) {
	  throw new PException("forum::MakeRevision fail to insert id=#".$Id." for ".$TableName." into ".$_SYSHCVOL['ARCH_DB'] . ".previousversion");
	}
	mysql_query($str);
} // end of MakeRevision



/**
* InsertInFTrad function
*
* This InsertInFTrad create a new translatable text in MemberTrad
* @$ss is for the content of the text
* @$TableColumn refers to the table and coilumn the trad is associated to
* @$IdRecord is the num of the record in this table
* @$_IdMember ; is the id of the member who own the record
* @$_IdLanguage
* @$IdTrad  is probably useless (I don't remmber why I defined it)
* 
* 
* Warning : as default language this function will use by priority :
* 1) the content of $_IdLanguage if it is set to something else than -1
* 2) the content of an optional $_POST[IdLanguage] if it is set
* 3) the content of the current $_SESSION['IdLanguage'] of the current membr if it set
* 4) The default language (0)
*
* returns the id of the created trad
* 
*/ 
function InsertInMTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	if ($_IdLanguage == -1)
		$IdLanguage = $this->_langWrite;
	else
		$IdLanguage = $_IdLanguage;

	if ($IdTrad == -1) { // if a new IdTrad is needed
		// Compute a new IdTrad
		$s = $this->_dao->query("Select max(IdTrad) as maxi from memberstrads");
		if (!$s) {
			throw new PException('Failed in InsertInMTrad searching Next max IdTrad');
		}
		$rr=$s->fetch(PDB::FETCH_OBJ) ;
		if (isset ($rr->maxi)) {
			$IdTrad = $rr->maxi + 1;
		} else {
			$IdTrad = 1;
		}
	}

	$IdOwner = $IdMember;
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	if (strpos($ss,"\\'")!==false) {
		$Sentence=$ss ;
		$page="" ;
		if (isset($_SERVER["PHP_SELF"])) {
			$page=$_SERVER["PHP_SELF"] ;
		}
		MOD_log::get()->write("in module word->InsertInMTrad, for IdTrad=".$IdTrad. " The sentence is already escaped with a quote page [".$page."]", "Bug");
	}
	elseif (strpos($ss,'\\"')!==false) {
		$Sentence=$ss ;
		$page="" ;
		if (isset($_SERVER["PHP_SELF"])) {
			$page=$_SERVER["PHP_SELF"] ;
		}
		MOD_log::get()->write("in module word->InsertInMTrad, for IdTrad=".$IdTrad. " The sentence is already escaped with a double quote page [".$page."]", "Bug");
	}
	else {
		$Sentence = $this->_dao->escape($ss);
	}
	$str = "insert into memberstrads(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
	$str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
	$s = $this->_dao->query($str);
	if (!$s) {
		throw new PException('Failed in InsertInMTrad inserting in membertrads');
	}
	$rr=$s->fetch(PDB::FETCH_OBJ) ;

	// update the IdTrad in the original table (if the TableColumn was given properly and the IdRecord too)
	if (!empty($TableColumn) and !empty($Idrecord)) {
		 $table=explode(".",$TableColumn) ;
		 $str="update ".$table[0]." set ".$TableColumn."=".$IdTrad." where ".$table[0].".id=".$IdRecord ; 
		$s = $this->_dao->query($str);
		if (!$s) {
			throw new PException('Failed in InsertInMTrad updating table column [%s]');
		}
	}
	return ($IdTrad);
} // end of InsertInMTrad


/**
* ReplaceInMTrad function
*
* This ReplaceInMTrad replace or create translatable text in member Trad
* @$ss is for the content of the text
* @$TableColumn refers to the table and column the trad is associated to
* @$IdRecord is the num of the record in this table
* $IdTrad is the record in member_trads to replace they are several records with the smae IdTrad teh difference is thr language,
* if IdTrad is set to 0 a new record will be created, this is the usual way to insert records
* @$IdOwner ; is the id of the member who own the record, if set to 0 We Will use the current member
* 
* Warning : as default language this function will use:
* - the content of the current $_SESSION['IdLanguage'] of the current member
* 
*/ 
function ReplaceInMTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
//    $ss = $this->_dao->escape($ss) ; // jy : I think we came here with an already escaped string
	if ($IdOwner == 0) {
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $IdOwner;
	}
	//  echo "in ReplaceInMTrad \$ss=[".$ss."] \$IdTrad=",$IdTrad," \$IdOwner=",$IdMember,"<br />";
    if (isset($this->_langWrite)) {
        $IdLanguage=$this->_langWrite;
    } else {
        $IdLanguage=0 ; // by default language 0
    } 
	if ($IdTrad == 0) {
		return ($this->InsertInMTrad($ss,$TableColumn,$IdRecord, $IdMember)); // Create a full new translation
	}
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$str = "select * from memberstrads where IdTrad=" . $IdTrad . " and IdOwner=" . $IdMember . " and IdLanguage=" . $IdLanguage;
	$s = $this->_dao->query($str);
	if (!$s) {
		throw new PException('Failed in ReplaceInMTrad retrieving IdTrad='.$IdTrad);
	}
	$rr=$s->fetch(PDB::FETCH_OBJ) ;
	if (!isset ($rr->id)) {
		return ($this->InsertInMTrad($ss,$TableColumn,$IdRecord, $IdMember, $IdLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
	} else {
		if ($ss != $this->_dao->escape($rr->Sentence)) { // Update only if sentence has changed
			$this->MakeRevision($rr->id, "memberstrads"); // create revision
			$str = "update memberstrads set TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' where id=" . $rr->id;
//			echo "\$str=".$str."<br />\n";
			$s = $this->_dao->query($str);
			if (!$s) {
				throw new PException('Failed in ReplaceInMTrad updating Sentence for IdTrad=#'.$IdTrad);
			}
		}
	}
	return ($IdTrad);
} // end of NewReplaceInMTrad



/**
* InsertInfTrad function
*
* This InsertInFTrad create a new translatable text in forum_trads
* @$ss is for the content of the text
* @$TableColumn refers to the table and coilumn the trad is associated to
* @$IdRecord is the num of the record in this table
* @$_IdMember ; is the id of the member who own the record
* @$_IdLanguage
* @$IdTrad  is probably useless (I don't remmber why I defined it)
* 
* 
* Warning : as default language this function will use by priority :
* 1) the content of $_IdLanguage if it is set to something else than -1
* 2) the content of an optional $_POST[IdLanguage] if it is set
* 3) the content of the current $_SESSION['IdLanguage'] of the current membr if it set
* 4) The default language (0)
*
* returns the id of the created trad
* 
*/ 
function InsertInFTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	$DefLanguage=$this->GetLanguageChoosen() ;
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	if ($_IdLanguage == -1) {
		$IdLanguage = $DefLanguage;
	}
	else {
		$IdLanguage = $_IdLanguage;
	}

	if ($IdTrad <=0) { // if a new IdTrad is needed
		// Compute a new IdTrad
		$s = $this->_dao->query("SELECT Next_Forum_trads_IdTrad() AS maxi FROM forum_trads");
		if (!$s) {
			throw new PException('Failed in InsertInFTrad searching Next_Forum_trads_IdTrad()');
		}
		$rr=$s->fetch(PDB::FETCH_OBJ) ;
		if (isset ($rr->maxi)) {
			$IdTrad = $rr->maxi + 1; // Gets the next MAXTRAD available
		} else {
			$IdTrad = 1;
		}
	}

	$IdOwner = $IdMember;
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$Sentence = $ss;
	$str = "insert into forum_trads(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
	$str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
	$s = $this->_dao->query($str);
	if (!$s) {
		throw new PException('Failed in InsertInFTrad for inserting in forum_trads!');
	}
	// update the IdTrad in the original table (if the TableColumn was given properly and the IdRecord too)
	if (($IdRecord>0) and (!empty($TableColumn))) {
	   $table=explode(".",$TableColumn) ;
	   $str="update ".$table[0]." set ".$TableColumn."=".$IdTrad." where id=".$IdRecord ;
      $s = $this->_dao->query($str);
      if (!$s) {
      	  throw new PException("InsertInFTrad Failed in updating ".$TableColumn." for IdRecord=#".$IdRecord." with value=[".$IdTrad."]");
      }
	   
	}
	return ($IdTrad);
} // end of InsertInFTrad

/**
* GetLanguageChoosen function
*
* This return the language choosen by the user 
* this function is supposed to be called after a new post, and editpost or a reply
* it return the language choosen if any
*/
function GetLanguageChoosen() {
	$DefLanguage=0 ;
   if (isset($_SESSION['IdLanguage'])) {
	   $DefLanguage=$_SESSION['IdLanguage'] ;
	}
	if (isset($_POST['IdLanguage'])) { // This will allow to consider a Language specified in the form
	   $DefLanguage=$_POST['IdLanguage'] ;
	}
	return($DefLanguage) ;
} // end of GetLanguageChoosen


/**
* ReplaceInFTrad function
*
* This ReplaceInFTrad replace or create translatable text in forum_trads
* @$ss is for the content of the text
* @$TableColumn refers to the table and column the trad is associated to
* @$IdRecord is the num of the record in this table
* $IdTrad is the record in forum_trads to replace (unique for each IdLanguage)
* @$Owner ; is the id of the member who own the record
* 
* Warning : as default language this function will use by priority :
* 1) the content of $_IdLanguage if it is set to something else than -1
* 2) the content of an optional $_POST[IdLanguage] if it is set
* 3) the content of the current $_SESSION['IdLanguage'] of the current membr if it set
* 4) The default language (0)
* 
*/ 
function ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
	$DefLanguage=$this->GetLanguageChoosen() ;
//	echo " ReplaceInFTrad \$DefLanguage=".$DefLanguage ;
	if ($IdOwner == 0) {
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $IdOwner;
	}
	if (empty($IdTrad)) {
		return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember,$DefLanguage)); // Create a full new translation
	}
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
  	$s = $this->_dao->query("SELECT * FROM forum_trads WHERE IdTrad=" . $IdTrad . " AND IdLanguage=" . $DefLanguage." /* in forum->ReplaceInFTrad */");
  	if (!$s) {
  	   throw new PException('Failed in ReplaceInFTrad searching previous IdTrad=#'.$IdTrad.' for IdLanguage='.$DefLanguage);
  	}
	$rr=$s->fetch(PDB::FETCH_OBJ) ;
	if (!isset ($rr->id)) {
		//	  echo "[$str] not found so inserted <br />";
		return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember, $DefLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
	} else {
		if ($ss != addslashes($rr->Sentence)) { // Update only if sentence has changed
			$this->MakeRevision($rr->id, "forum_trads"); // create revision
			$str = "UPDATE forum_trads SET TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' WHERE id=" . $rr->id;
   		$s = $this->_dao->query($str);
   		if (!$s) {
      		   throw new PException('Failed in ReplaceInFTrad for updating in forum_trads!');
   		}
		}
	}
	return ($IdTrad);
} // end of ReplaceInFTrad

} // end of class MOD_word




/**
 * This class stores all the information from looking up a word keycode in the database.
 * Objects of this type are created in MOD_words::_lookup($code[, $lang]), so all the db stuff happens there.
 * Objects of this type do not store the additional arguments from a call to $words->getFormatted($code, ..).
 * 
 * The main purpose is to package information for function arguments and return values,
 * and reduce the number of single variables to deal with in a function.
 */
class LookedUpWord {
	
    // constants for tr success
    const NO_TR_LINK = 0;
    const SUCCESSFUL = 1;
    const OBSOLETE = 2;
    const MISSING_TR = 3;
    const MISSING_WORD = 4;
    
    // constants for tr quality - yet to be implemented in the DB
    const FINE = 5;  // translation quality is ok
    const DEBATABLE = 6;
    const AWKWARD = 7;
    
    // attributes
    private $_code;  // key code for words DB
    private $_lang;  // intended language
    private $_lookup_result;  // a string, either in $_lang or in English, with argument placeholders
    private $_tr_success;  // can be 'obsolete', 'missing_translation', or 'missing_word'. Anything else means there is a translation.
    private $_tr_quality;  // can be 'awkward' or 'debatable'. Anything else means the translation is ok.
    
    
    /**
     * The constructor gets the parameters from MOD_words::_lookup($code)_
     *
     * @param string $code
     */
    public function __construct ($code, $lang, $lookup_result, $tr_success = LookedUpWord::NO_TR_LINK, $tr_quality = LookedUpWord::FINE) {
    	$this->_code = $code;
    	$this->_lang = $lang;
    	$this->_lookup_result = $lookup_result;
    	$this->_tr_success = $tr_success;
    	$this->_tr_quality = $tr_quality;
    }
    
    public function getCode() {
        return $this->_code;
    }
    
    public function get_tr_success() {
    	return $this->_tr_success;
    }
    
    function text()
    {
        return $this->_lookup_result;
    }
    
    
    
    /**
     * @param array $args an array of arguments to be replaced in the lookup string
     * @return string translated word without any <a> tags, to avoid nested hyperlinks or worse things
     */
    function textWithoutLinks() {
        return str_replace(
            array("<a ", "<a\n", "<a>", "</a>"),  // replace a-tags
            array("<u ", "<u\n", "<u>", "</u>"),  // with u-tags
            $this->text()
        );
    }
    
    
    
    public function clickableText()
    {
        $text = str_replace(
            array("<a ", "<a\n", "<a>", "</a>"),  // replace a-tags
            array("<u ", "<u\n", "<u>", "</u>"),  // with u-tags
            $this->text()
        );
        return '<span class="tr_span"><a '.
            'class = "'.$this->_trLinkClass().'" '.
            'title = "'.$this->_trLinkTitle().'" '.
            'target = "new" '.
            'href = "'.$this->_trLinkURL().'"'.
        '>'.$this->textWithoutLinks().'</a>'.$this->_trLinkInfoBox().'</span>';
    }
    
    
    public function standaloneTrLink()
    {
        return '<span class="tr_span"><a '.
            'class = "standalone '.$this->_trLinkClass().'" '.
            'title = "'.$this->_trLinkTitle().'" '.
            'target = "new" '.
            'href = "'.$this->_trLinkURL().'"'.
        '>'.$this->_trLinkLanguage().'</a>'.$this->_trLinkInfoBox().'</span>';
    }
    
    
    
    static $_action_strings = array(
        self::NO_TR_LINK => 'do nothing',
        self::MISSING_WORD => 'define',
        self::MISSING_TR => 'translate',
        self::OBSOLETE => 'update',
        self::SUCCESSFUL => 'edit'
    );
    
    private function _trLinkInfoBox()
    {
        /*
        return '<div class="tr_info_box">'.
            self::$_action_strings[''.$this->_tr_success].' '.
            '<b>'.$this->_code.'</b>'.
            ' in '.$this->_lang.
        '</div>';
        */
        return '';
    }
    
    private function _trLinkURL()
    {
        return PVars::getObj('env')->baseuri.'bw/admin/adminwords_edit.php?lang='.$this->_trLinkLanguage().'&code='.$this->_code;
    }
    
    private function _trLinkLanguage()
    {
        if($this->_tr_success == self::MISSING_WORD) return 'en';
        else return $this->_lang;
    }
    

    private function _trLinkTitle()
    {
        return self::$_action_strings[''.$this->_tr_success].' '.$this->_code.' in '.$this->_lang;
    }
    
    static $_class_strings = array(
        self::NO_TR_LINK => 'whatever',
        self::MISSING_WORD => 'missing_word',
        self::MISSING_TR => 'missing_translation',
        self::OBSOLETE => 'obsolete',
        self::SUCCESSFUL => 'successful_translation'
    );
    
    private function _trLinkClass()
    {
        return 'tr_link '.self::$_class_strings[$this->_tr_success];
    }
}





?>
