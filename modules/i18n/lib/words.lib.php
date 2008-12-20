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
                    return $word->clickableText();
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
		
			if (isset($_SESSION['IdLanguage'])) {
		 	   	$IdLanguage=$_SESSION['IdLanguage'] ;
			}
			else {
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
     * @return string translated according to the best language find
     */
    public function fTrad($IdTrad,$ReplaceWithBr=false) {
		
			global $fTradIdLastUsedLanguage ; // Horrible way of returning a variable you forget when you designed the method (jyh)
			$fTradIdLastUsedLanguage=-1 ; // Horrible way of returning a variable you forget when you designed the method (jyh)
																					// Will receive the choosen language

	 		$AllowedTags = "<b><i><br><br/><p><img><ul><li><strong><a>"; // This define the tags wich are not stripped inside a forum_trads
			if (empty($IdTrad)) {
			   return (""); // in case there is nothing, return and empty string
			}
			else  {
			   if (!is_numeric($IdTrad)) {
			   	  die ("it look like you are using forum::fTrad with and allready translated word, a forum_trads.IdTrad is expected and it should be numeric !") ;
			   }
			}
		
			if (isset($_SESSION['IdLanguage'])) {
		 	   	$IdLanguage=$_SESSION['IdLanguage'] ;
			}
			else {
		 		$IdLanguage=0 ; // by default language 0
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
				   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
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
				   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
				}
			}
			$strerror="fTrad Anomaly : no entry found for IdTrad=#".$IdTrad ;
			MOD_log::get()->write($strerror, "Bug");
			return ($strerror); // If really nothing was found, return an empty string
	 } // end of fTrad
	 
    

}




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