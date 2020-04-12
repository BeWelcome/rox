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

use App\Utilities\SessionTrait;
use App\Utilities\TranslatorSingletonTrait;


/**
 * Enables us to use content from words table of BW from within the platform PT structure.
 * Instantiate in the first lines of your template, then call the "get" method.
 *
 * @see     /htdocs/bw/lib/lang.php
 *
 * FIXME: In need of categories to be able to fetch arrays of texts instead of every
 * single text separately.
 * TODO: tracking of unused words
 * FIXME: integrate $this->session->get('TranslationArray') - but do we really need it?
 */

class MOD_words
{
    use SessionTrait;
    use TranslatorSingletonTrait;

    private $_lang;  // the active language
    private $_trMode;  // the translation mode - can be browse, translate, or edit
    private $_whereCategory = '';
    private $_offerTranslationLink = false;
    private $_langWrite = 0;
    static private $_buffer = array();
    private $_dao;  // database access object
    private $WordMemcache ;


    /**
     * MOD_words constructor.
     * @param null $category
     * @throws Exception
     * @throws PException
     */
    public function __construct($category=null)
    {
        $this->setSession();
        $this->setTranslatorSingleton();

        $this->_lang = \PVars::get()->lang;

		$this->WordMemcache=new MOD_bw_memcache("words","Sentence","code") ;

        if (!empty($category)) {
            $this->_whereCategory = ' `category`=\'' . $category . '\'';
        }
        if ($this->session->has( 'IdLanguage' ))
            $this->_langWrite = $this->session->get('IdLanguage');
        else $this->_langWrite = 0;

        $db_vars = \PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
        $this->_dao =& $dao;

        $R = MOD_right::get();
        if ($R->hasRight("Words", $this->_lang)) {
            $this->_offerTranslationLink = true;
        }

        // read translation mode from $this->session->get('tr_mode')
        if ($this->session->has("tr_mode")) {
            $this->_trMode = $this->session->get('tr_mode');
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

    public function setlangWrite($IdLanguage) {
        $this->_langWrite = $IdLanguage;
    }

    public function getTrMode() {
        // Disable old style translation
        return 'browse';
    }


    public static function trLinkBufferSize()
    {
        return sizeof(self::$_buffer);
    }


    public function translationLinksEnabled() {
        // Disable old style translation.
        return false;
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

    /**
     * does the same as getBuffered($code, ...)
     * the function is here for backwards convenience
     */
    public function getSilent($code)
    {
        // if it DOES the same, then make sure it ACTUALLY does the same instead of duplicating the code
        $args = func_get_args();
        return call_user_func_array(array($this, 'getBuffered'), $args);
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
        // if it DOES the same, then make sure it ACTUALLY does the same instead of duplicating the code
        $args = func_get_args();
        return call_user_func_array(array($this, 'getFormatted'), $args);
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
        $translation = $this->_text_with_tr($word);
        if (($translation == $code) && (!empty($args))) {
            $translation .= " [" . implode($args, ",") . "]";
        }
        return $translation;
    }

    /**
     * Look up texts in words table.
     * No newlines or slashes are replaced. Never add translation links.
     *
     * @param string $code         keyword for finding text, not allowed to be empty
     * @param array  $replacements strings to be inserted into the translation's %s placeholders
     * @param string $language     ShortCode of language, 2 to 4 letter
     *
     * @return string localized text, in case of no hit the word keycode
     */
    public function getRaw($code, $replacements = array(), $language = false)
    {
        $word = $this->_lookup($code, $replacements, $language, true);
        return $word->text();
    }

    /**
     * Get text as is from the database no call to vsprintf
     * (Needed for newsletter that contain links and %username% tags)
     *
     * @param string $code         keyword for finding text, not allowed to be empty
     *
     * @return string localized text, in case of no hit the word keycode
     */
    public function getAsIs($code)
    {
        $lang = $this->_lang;
        $whereCategory = $this->_whereCategory;

        if (is_numeric($code)) {
            $query =
                "SELECT SQL_CACHE `code`,`Sentence`, `donottranslate`, `updated` ".
                "FROM `words` ".
                "WHERE `id`=" . $this->_dao->escape($code)
            ;
        } else {
            // First try in memcache
            if ($value=$this->WordMemcache->GetValue($code,$lang)) {
                return $value;
            }
            $query =
                "SELECT SQL_CACHE `code`,`Sentence`, `donottranslate`, `updated` ".
                "FROM `words` ".
                "WHERE `code`='" . $this->_dao->escape($code) . "' and `ShortCode`='" . $this->_dao->escape($lang) . "'"
            ;
        }

        $q = $this->_dao->query($query);
        $rows = $q->numRows();
        if ($rows <> 0) {
            $row = $q->fetch(PDB::FETCH_OBJ);
        } else {
            // Try again in English
            $query =
                "SELECT SQL_CACHE `code`,`Sentence`, `donottranslate`, `updated` ".
                "FROM `words` ".
                "WHERE `code`='" . $this->_dao->escape($code) . "' and `ShortCode`='en'"
            ;
            $q = $this->_dao->query($query);
            $rows = $q->numRows();
            if ($rows <> 0) {
                $row = $q->fetch(PDB::FETCH_OBJ);
            } else {
                $row = new StdClass;
                $row->Sentence = $code;
            }
        }
        return $row->Sentence;
    }

    /**
     * Look up texts in words table.
     * Use purifier to add paragraphs and linkify. Never add translation links.
     *
     * @param string $code         keyword for finding text, not allowed to be empty
     * @param array  $replacements strings to be inserted into the translation's %s placeholders
     * @param string $language     ShortCode of language, 2 to 4 letter
     *
     * @return string localized text, in case of no hit the word keycode
     */
    public function getPurified($code, $replacements = array(), $language = false)
    {
        $text = $this->getRaw($code, $replacements, $language);
        // hack to work around a problem during signup
        require_once SCRIPT_BASE . '/modules/htmlpurify/lib/htmlpurify.lib.php';
        $purifier = MOD_htmlpure::get()->getAdvancedHtmlPurifier();
        return $purifier->purify($text);
    }

    /**
     * Looks up (localized) texts in BW words table.
     * Newlines are replaced by HTML breaks, backslashes are stripped off.
     * Takes a variable number of arguments as c-style formatted string.
     *
	 * Second parametter is the language
     * @see wwinlang in /lib/lang.php
     * @param   string  $code keyword for finding text, not allowed to be empty
     * @param   string  $? formatted according to a variable number of arguments
     * @param   ... arguments to be inserted in the string
     * @return  string  localized text, in case of no hit the word keycode, evtl with tr links
     */
    public function getFormattedInLang($code,$lang)
    {
        $args = func_get_args();
        array_shift($args);  // need a second array shift, because of 2 default arguments in function
        array_shift($args);

        $word = $this->_lookup($code, $args,$lang);

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
    private function _lookup($code, $args, $lang = false, $get_raw = false)    {
        if($lang == false) {
            $lang = $this->_lang;
        }

        $row = $this->_lookup_row($code, $lang);
        // select the English wordcode only if not archived
        $row_en = $this->_lookup_row($code, 'en','AND (isarchived=0 or isarchived is null)');
        $R = MOD_right::get();

        // for normal people no translation stuff
        if(!$row_en) {
            // if English doesn't exist, show code
            $lookup_result = $code;
        } elseif (!$row || $row->updated < $row_en->majorupdate) {
            // if no translation or translation is outdated, show English
            $lookup_result = $this->_modified_sentence_from_row($row_en, $args, $get_raw);
        } else {
            // if up-to-date translation present, show translation
            $lookup_result = $this->_modified_sentence_from_row($row, $args, $get_raw);
        }

        if ($this->_offerTranslationLink) {
            // make sure translation shows up in new translation interface
            $trans = $this->_translator->trans(strtolower($code));
        }
        return new LookedUpWord($code, $lang, $lookup_result);
    }



    /**
     * Reads the (modified) translation sentence from a row in the database.
     * Modifications (if $get_raw is false):
     *  - stripslashes
     *  - n12br
     *
     * @param dbrow $row
     * @param array $args
     * @param boolean $get_raw true for raw string, false for modified string
     * @return string modified sentence from db
     */
    private function _modified_sentence_from_row($row, $args, $get_raw = false)
    {
        $row_sentence = $row->Sentence;
        if ($get_raw) {
            $lookup_string = $row_sentence;
        } else {
            $lookup_string = nl2br(stripslashes($row_sentence));
        }
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
    private function _lookup_row($code, $lang, $extraWhere = '')
    {
        if (is_numeric($code)) {
            $query =
                "SELECT SQL_CACHE `code`,`Sentence`, `donottranslate`, `updated`, majorupdate ".
                "FROM `words` ".
                "WHERE `id`=" . $this->_dao->escape($code)
            ;
        } else {
        	// TODO: store translation quality in database!
            $query =
                '
SELECT SQL_CACHE code, Sentence, donottranslate, updated, majorupdate
FROM words
WHERE code = "' . $this->_dao->escape(strtolower($code)) . '" AND ShortCode = "' . $this->_dao->escape($lang) .'"'. $extraWhere;
        }

        $q = $this->_dao->query($query);
        $row = $q->fetch(PDB::FETCH_OBJ);
		// update the statistic about the use of this word only if the option ToggleStatsForWordsUsage is active
		if (($this->session->has('Param'))
		&& ($this->session->get('Param')->ToggleStatsForWordsUsage=="Yes")
		&& (isset($row->code))) {
			$query ="CALL IncWordUse('".$row->code."')" ;
			$s=$this->_dao->query($query);
            if (!$s) {
                throw new PException('Failed to IncWordUse for code ['.$row->code.']');
            }
		}

        return $row;
    }

    /**
    * deleteMTrad function
    *
	* This delete a translations
    *
    */
    public function deleteMTrad($IdTrad, $IdOwner, $IdLanguage) {
        $IdMember = $this->session->get('IdMember');


        $str = <<<SQL
SELECT
    *
FROM
    memberstrads
WHERE
    IdTrad = '{$IdTrad}' AND
    IdOwner = '{$IdOwner}' AND
    IdLanguage = '{$IdLanguage}'
SQL;

        $s = $this->_dao->query($str);
        if (!$s) {
            return false;
        }

        if ($s->numRows() == 0) {
            return false;
        }

        $Trad = $s->fetch(PDB::FETCH_OBJ);
        $BW_Right = new MOD_right();
        if ($IdOwner != $IdMember && !$BW_Right->hasRight('Admin'))  {
            return false;
        }

        $this->MakeRevision($Trad->id, "memberstrads"); // create revision before the delete

        // If the IdTrad for this language was already deleted
        // SQL will throw an exception as the triple IdTrad, IdOwner and IdLanguage is already set
        // live DB has an index on this.
        $query = "
DELETE FROM
    memberstrads
WHERE
    IdTrad = '" . (-$IdTrad) . "' AND
    IdOwner = '{$IdMember}' AND
    IdLanguage = '{$IdLanguage}'";
        $this->_dao->query($query);

        // Mark the tradId as deleted by turning it into -IdTrad
        $query = "
UPDATE
    memberstrads
SET
    IdTrad = '" . (-$IdTrad) . "'
WHERE
    IdTrad = '{$IdTrad}' AND
    IdOwner = '{$IdMember}' AND
    IdLanguage = '{$IdLanguage}'";
        $this->_dao->query($query);


        return false;
    } // end of deleteMTrad


    /**
	 * retuns a string where
     * @param $ss the string where to replace \n
     * @param $RepalceWith a boolean to say wether the replace shoud occur or not
     * @return string where \n are replaced with <br \> if the ReplaceWith parameter was true
     * @todo STOP WRITING CODE LIKE THIS! IF YOU KNOW THAT NO REPLACING SHOULD TAKE PLACE
     *       THEN DONT CALL THE FUNCTION!!!
     */
	 private function ReplaceWithBr($ss,$ReplaceWith=false) {
		if ($ReplaceWith) {
            return(str_replace(array("\\r\\n","\r\n","\\n","\n"),"<br />",$ss)) ;
        }
        else {
            return(str_replace(array("\\r\\n","\r\n","\\n","\n"),"\n",$ss)) ;
        }
	 }


    /**
     * @param $IdTrad the id of a memberstrads.IdTrad record to retrieve
	 * @param $IdLanguage, prefered language to use, beware if ommitted, english is used !
	 * @param $ReplaceWithBr allows
     * @return string translated according to the best language find
     */
    public function mInTrad($IdTrad,$IdLanguage=0,$ReplaceWithBr=false) {

	 		$AllowedTags = "<b><i><br><br/><p><u>"; // This define the tags wich are not stripped inside a membertrad
			if (empty($IdTrad)) {
			   return (""); // in case there is nothing, return an empty string
			}
			else  {
			   if (!is_numeric($IdTrad)) { // Logging anomalie things to detect database problem if any
					$sBug="it look like you are using MOD_WORD::mInTrad with and allready translated word [".$IdTrad."], a memberstrads.IdTrad is expected and it should be numeric !" ;
					MOD_log::get()->write($sBug,"Bug");
					die ($sBug) ;
			   }
			}

			// Try default chosen language
        	$query ="SELECT SQL_CACHE `Sentence` FROM `memberstrads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$IdLanguage." ORDER BY updated DESC" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
				}
				else {
                    return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			// Try default en
        	$query ="SELECT SQL_CACHE `Sentence` FROM `memberstrads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=0 ORDER BY updated DESC" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
				} else {
                    return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
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
                    return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			MOD_log::get()->write("mInTrad Anomaly : no entry found for IdTrad=#".$IdTrad, "Bug");
			return (""); // If really nothing was found, return an empty string
	 } // end of mInTrad

    /**
     * @param $IdTrad the id of a memberstrads.IdTrad record to retrieve
     * @param $ReplaceWithBr allows
     * @return string translated according to the best language find
     */
    public function mTrad($IdTrad,$ReplaceWithBr=false) {
		if ($this->session->has( 'IdLanguage' )) {
	 	   	$IdLanguage= $this->session->get( 'IdLanguage');
		}
		else {
	 		$IdLanguage=0 ; // by default language 0
		}
		return ($this->mInTrad($IdTrad,$IdLanguage,$ReplaceWithBr)) ;
	 } // end of mTrad

    /**
	 * @param integer $IdTrad the id of a translations.IdTrad record to retrieve
	 * @parame integer $IdForceLanguage optional can be use to force the routine to try to choose a specific language
     * @param boolean $ReplaceWithBr allows
	 * @return string translated according to the best language find
	 */
    public function fTrad($IdTrad,$IdForceLanguage=-1, $ReplaceWithBr=false) {

			global $fTradIdLastUsedLanguage ; // Horrible way of returning a variable you forget when you designed the method (jyh)
			$fTradIdLastUsedLanguage=-1 ; // Horrible way of returning a variable you forget when you designed the method (jyh)
																					// Will receive the choosen language

	 		$AllowedTags = "<b><i><br><br/><p><img><ul><li><strong><a>"; // This define the tags wich are not stripped inside a translations
			if (empty($IdTrad)) {
			   return (""); // in case there is nothing, return and empty string
			}
			else  {
			   if (!is_numeric($IdTrad)) {
			   	  die ("it look like you are using forum::fTrad with and allready translated word, a translations.IdTrad is expected and it should be numeric ! IdTrad=[".$IdTrad."]") ;
			   }
			}

			if ($IdForceLanguage<=0) {
				if ($this->session->has( 'IdLanguage' )) {
					$IdLanguage= $this->session->get( 'IdLanguage' );
				}
				else {
					$IdLanguage=0 ; // by default language 0
				}
			}
			else {
				$IdLanguage=$IdForceLanguage ;
			}
			// Try default language
        	$query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `translations` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$IdLanguage ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language " . $IdLanguage . " with translations.IdTrad=" . $IdTrad, "Bug");
				}
				else {
					$fTradIdLastUsedLanguage=$row->IdLanguage ;
                    return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			// Try default eng
        	$query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `translations` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=0" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence for language 1 (eng) with translations.IdTrad=" . $IdTrad, "Bug");
				} else {
					 $fTradIdLastUsedLanguage=$row->IdLanguage ;
                    return ($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr));
				}
			}
			// Try first language available
            $query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `translations` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
			$q = $this->_dao->query($query);
			$row = $q->fetch(PDB::FETCH_OBJ);
			if (isset ($row->Sentence)) {
				if (isset ($row->Sentence) == "") {
					MOD_log::get()->write("Blank Sentence (any language) translations.IdTrad=" . $IdTrad, "Bug");
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
     * 3) the content of the current $this->session->get('IdLanguage') of the current membr if it set
     * 4) The default language (0)
     *
     * returns the id of the created trad
	 *
	 * Improvment: if the value is empty then nothing is inserted but 0 is returned
	 *
	 *
     *
     */
    function InsertInMTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
        if ($ss=="") { // No need to insert an empty record in memberstrads
            return(0) ;
        }

        if ($_IdMember == 0) { // by default it is current member
            $IdMember = $this->session->get('IdMember');
        } else {
            $IdMember = $_IdMember;
        }

        if ($_IdLanguage == -1)
            $IdLanguage = $this->_langWrite;
        else
            $IdLanguage = $_IdLanguage;

        $IdOwner = $IdMember;
        $IdTranslator = $this->session->get('IdMember'); // the recorded translator will always be the current logged member
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

        $str = "LOCK TABLES memberstrads WRITE";
        $s = $this->_dao->query($str);
        // \todo: Check result?
        if ($IdTrad <=0) {
            // Compute a new IdTrad
            $s = $this->_dao->query("Select max(IdTrad) as maxi, min(IdTrad) as mini from memberstrads");
            if (!$s) {
                // Unlock table before throwing exception!
                $this->_dao>query("UNLOCK TABLES");
                throw new PException('Failed in InsertInMTrad searching Next max IdTrad');
            }
            $rr=$s->fetch(PDB::FETCH_OBJ) ;
            if (isset ($rr->maxi)) {
                // get
                $IdTrad = max(abs($rr->mini), $rr->maxi) + 1;
            } else {
                $IdTrad = 1;
            }
        }

        $str = "insert into memberstrads(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
        $str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
        $s = $this->_dao->query($str);
        if (!$s) {
            // Unlock table before throwing exception!
            $this->_dao>query("UNLOCK TABLES");
            throw new PException('Failed in InsertInMTrad inserting in membertrads');
        }
        // unlock membertrads table, the other table can be updated without lock.
        $this->_dao->query("UNLOCK TABLES");

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
    * - the content of the current $this->session->get('IdLanguage') of the current member
    *
    */
    function ReplaceInMTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
        // temporary hack to undo the damage done by escaping in other places
        // todo: find all references to ReplaceInMTrad and fix them
        // Change by jeanyves on AUgust 18 2009: \r\n are kept, but \' are replaced by '
        while (strpos($ss,"\\'")!==false) {
            $ss=str_replace("\\'","'",$ss) ;
        }
        $ss=str_replace("\r\n","\n",$ss) ;
        $ss = $this->_dao->escape($ss) ; // jy : I think we came here with an already escaped string.
        // judging from the exception logs this is NOT TRUE. Instead we now have a massive sql injection exploit vector

        if ($IdOwner == 0) {
            $IdMember = $this->session->get('IdMember');
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
        $IdTranslator = $this->session->get('IdMember'); // the recorded translator will always be the current logged member
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
    } // end of ReplaceInMTrad



    /**
    * InsertInfTrad function
    *
    * This InsertInFTrad create a new translatable text in translations
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
    * 3) the content of the current $this->session->get('IdLanguage') of the current membr if it set
    * 4) The default language (0)
    *
    * returns the id of the created trad
    *
	* improvment if the text value is empty, nothing is inserte din the table, and 0 is retruned as an IdTrad
	*
    */
    function InsertInFTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
        $DefLanguage=$this->GetLanguageChoosen() ;
        if ($_IdMember == 0) { // by default it is current member
            $IdMember = $this->session->get('IdMember');
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
			if ($ss=="") { // No need to insert an empty record in translations
				return(0) ;
			}
            // Compute a new IdTrad
            $s = $this->_dao->query("select max(IdTrad)+1 AS maxi from translations");
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
        $IdTranslator = $this->session->get('IdMember'); // the recorded translator will always be the current logged member
        $Sentence = $ss;
        $str = "insert into translations(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
        $str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
        $s = $this->_dao->query($str);
        if (!$s) {
            throw new PException('Failed in InsertInFTrad for inserting in translations!');
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
       if ($this->session->has( 'IdLanguage' )) {
           $DefLanguage= $this->session->get( 'IdLanguage' );
        }
        if (isset($_POST['IdLanguage'])) { // This will allow to consider a Language specified in the form
           $DefLanguage=$_POST['IdLanguage'] ;
        }
        return($DefLanguage) ;
    } // end of GetLanguageChoosen


    /**
    * ReplaceInFTrad function
    *
    * This ReplaceInFTrad replace or create translatable text in translations
    * @$ss is for the content of the text
    * @$TableColumn refers to the table and column the trad is associated to
    * @$IdRecord is the num of the record in this table
    * $IdTrad is the record in translations to replace (unique for each IdLanguage)
    * @$Owner ; is the id of the member who own the record
    *
    * Warning : as default language this function will use by priority :
    * 1) the content of $_IdLanguage if it is set to something else than -1
    * 2) the content of an optional $_POST[IdLanguage] if it is set
    * 3) the content of the current $this->session->get('IdLanguage') of the current membr if it set
    * 4) The default language (0)
    *
    */
    function ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
        $DefLanguage=$this->GetLanguageChoosen() ;
    //	echo " ReplaceInFTrad \$DefLanguage=".$DefLanguage ;
        if ($IdOwner == 0) {
            $IdMember = $this->session->get('IdMember');
        } else {
            $IdMember = $IdOwner;
        }
        if (empty($IdTrad)) {
            return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember,$DefLanguage)); // Create a full new translation
        }
        $IdTranslator = $this->session->get('IdMember'); // the recorded translator will always be the current logged member
        $s = $this->_dao->query("SELECT * FROM translations WHERE IdTrad=" . $IdTrad . " AND IdLanguage=" . $DefLanguage." /* in forum->ReplaceInFTrad */");
        if (!$s) {
           throw new PException('Failed in ReplaceInFTrad searching previous IdTrad=#'.$IdTrad.' for IdLanguage='.$DefLanguage);
        }
        $rr=$s->fetch(PDB::FETCH_OBJ) ;
        if (!isset ($rr->id)) {
            //	  echo "[$str] not found so inserted <br />";
            return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember, $DefLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
        } else {
            if ($ss != addslashes($rr->Sentence)) { // Update only if sentence has changed
                $this->MakeRevision($rr->id, "translations"); // create revision
                $str = "UPDATE translations SET TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' WHERE id=" . $rr->id;
            $s = $this->_dao->query($str);
            if (!$s) {
                   throw new PException('Failed in ReplaceInFTrad for updating in translations!');
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
        '>'.$this->textWithoutLinks().'</a></span>';
    }


    public function standaloneTrLink()
    {
        return '<span class="tr_span"><a '.
            'class = "standalone '.$this->_trLinkClass().'" '.
            'title = "'.$this->_trLinkTitle().'" '.
            'target = "new" '.
            'href = "'.$this->_trLinkURL().'"'.
        '>'.$this->_trLinkLanguage().'</a></span>';
    }

    static $_action_strings = array(
        self::NO_TR_LINK => 'do nothing',
        self::MISSING_WORD => 'define',
        self::MISSING_TR => 'translate',
        self::OBSOLETE => 'update',
        self::SUCCESSFUL => 'edit'
    );

    private function _trLinkURL()
    {
        if($this->_tr_success == self::MISSING_WORD){
            return PVars::getObj('env')->baseuri.'admin/word/createcode/'.$this->_code;
        } else {
            return PVars::getObj('env')->baseuri.'admin/word/edit/'.$this->_code.'/'.$this->_trLinkLanguage();
        }
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
