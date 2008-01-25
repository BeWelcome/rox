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
        $word = $this->_lookup($code);
        
        $args = func_get_args();
        array_shift($args);
        
        return $this->_text_and_buffer($word, $args);
    }
    
    
    /**
     * does the same as getBuffered($code, ...)
     * the function is here for backwards convenience
     */
    public function getSilent($code)
    {
        $word = $this->_lookup($code);
        
        $args = func_get_args();
        array_shift($args);
        
        return $this->_text_and_buffer($word, $args);
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
        $word = $this->_lookup($code);
        
        $args = func_get_args();
        array_shift($args);
        
        return $this->_text_with_tr($word, $args);
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
        $word = $this->_lookup($code);
        
        $args = func_get_args();
        array_shift($args);
        
        return $this->_text_with_tr($word, $args);
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
        $word = $this->_lookup($code, $lang);
        
        $args = func_get_args();
        array_shift($args);  // need a second array shift, because of 2 default arguments in function
        array_shift($args);
        
        return $this->_text_with_tr($word, $args);
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
        $word = $this->_lookup($code, $lang);
        
        $args = func_get_args();
        array_shift($args);  // need a second array shift, because of 2 default arguments in function
        array_shift($args);
        
        return $this->_text_and_buffer($word, $args);
    }
    
    
    /**
     * creates a string that contains the translated word and evtl a tr link. 
     *
     * @param Word $word an object of type Word, containing all the stuff from DB lookup
     * @param array $args the arguments to be inserted in the translated word
     * @return string the string to be used in the webpage
     */
    private function _text_with_tr($word, $args)
    {
        if (! $this->_offerTranslationLink) {
            return $word->word($args);
        } else {
            switch($word->get_tr_success()) {
            case Word::MISSING_WORD:
                // string does not contain hyperlinks!
                return $word->word_in_tr_link($args);
            case Word::MISSING_TR:
            case Word::OBSOLETE:
                // need an obvious translation link!
                if(count($args)>0) {
                    // the string could contain hyperlinks
                    return $word->word_then_tr_link($args);
                } else {
                    // the string will most likely not contain hyperlinks
                    return $word->word_in_tr_link($args);
                }
            default:
                // create a tr link behind (that will be hidden) 
                return $word->word_then_tr_link($args);
            }
        }
    }
    
        
    
    private function _text_and_buffer($word, $args)
    {
        if ($this->_offerTranslationLink) {
            if(!array_key_exists($word->getCode(), self::$_buffer)) {
                self::$_buffer[$word->getCode()]=$word->standalone_tr_link();
            }
        }
        return $word->word($args);
    }
    
    
    
    /**
     * looks up a word keycode in the DB, and returns an object of type Word.
     * If a translation in the intended language is not found, it uses the english version.
     * If no english definition exists, the keycode itself is used.   
     * 
     * @param unknown_type $code the key code for the db lookup
     * @return Word information that is created from the word lookup
     */
    private function _lookup($code, $lang = false)
    {
        if($lang == false) {
            $lang = $this->_lang;
        }
        
        
        if(! $this->_offerTranslationLink) {
            // normal people don't need the tr stuff
            $row = $this->_lookup_row($code, $lang);
            if (!$row && $lang != 'en') {
                // try in english
                $row = $this->_lookup_row($code, 'en');
            }
            if(!$row) {
                // use the plain key code
                $lookup_result = $code;
            } else {
                // use the row that has been found
                $lookup_result = $this->_modified_sentence_from_row($row);
            }
            return new Word($code, $lang, $lookup_result);
        } else {
            // for translators, the Word object needs more info
            $tr_quality = Word::FINE;
            $row = $this->_lookup_row($code, $lang);
            if ($row) {
                $lookup_result = $this->_modified_sentence_from_row($row);
                if($lang == 'en') {
                    $tr_success = Word::SUCCESSFUL;
                } else {
                    $row_en = $this->_lookup_row($code, 'en');
                    if($this->_is_obsolete($row, $row_en)) {
                        $tr_success = Word::OBSOLETE;
                    } else {
                        $tr_success = Word::SUCCESSFUL;
                    }
                }
            } else if($lang != 'en') {
                // try in english
                $row = $this->_lookup_row($code, 'en');
                if($row) {
                    // use english version
                    $tr_success = Word::MISSING_TR;  // at least that bad
                	$lookup_result = $this->_modified_sentence_from_row($row);
                } else {
                    // no translation found
                    $tr_success = Word::MISSING_WORD;
                    $lookup_result = $code;
 	            }
            } else {
                // no translation found
                $tr_success = Word::MISSING_WORD;
                $lookup_result = $code;
            }
            switch ($this->_trMode) {
                case 'browse':
                    $tr_success = Word::NO_TR_LINK;
                    break;
                case 'proofread':
                    // does not yet exist.
                    break;
                case 'translate':
                    if($tr_success == Word::SUCCESSFUL) {
                        $tr_success = Word::NO_TR_LINK;
                    }
                    break;
                case 'edit':
                    // no need to do anything
                    break;
                }
	        return new Word($code, $lang, $lookup_result, $tr_success, $tr_quality);
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
    private function _modified_sentence_from_row($row)
    {
        return nl2br(stripslashes($row->Sentence));
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
        return ($row->updated) < ($row_en->updated);
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
     * should return an array of Word objects, for caching purposes.
     * to be implemented!!
     * (hmm, is this really benefitial?)
     *
     * @param array $array_of_codes an array of word keycodes.
     * @return array an array of Word objects
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
}




/**
 * This class stores all the information from looking up a word keycode in the database.
 * Objects of this type are created in MOD_words::_lookup($code[, $lang]), so all the db stuff happens there.
 * Objects of this type do not store the additional arguments from a call to $words->getFormatted($code, ..).
 * 
 * The main purpose is to package information for function arguments and return values,
 * and reduce the number of single variables to deal with in a function.
 */
class Word {
	
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
    private $_lookup_result;  // a string, either in $_lang or in english, with argument placeholders
    private $_tr_success;  // can be 'obsolete', 'missing_translation', or 'missing_word'. Anything else means there is a translation.
    private $_tr_quality;  // can be 'awkward' or 'debatable'. Anything else means the translation is ok.
    
    
    /**
     * The constructor gets the parameters from MOD_words::_lookup($code)_
     *
     * @param string $code
     */
    public function __construct ($code, $lang, $lookup_result, $tr_success = Word::NO_TR_LINK, $tr_quality = Word::FINE) {
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
    
    
    /**
     * @param array $args an array of arguments to be replaced in the lookup string
     * @return string the translated word, without translation links. May contain other hyperlinks.  
     */
    function word($args)
    {
        return vsprintf($this->_lookup_result, $args);
    }
    
    
    /**
     * @param array $args an array of arguments to be replaced in the lookup string
     * @return string the translated word, followed directly by a tr link.
     * This is useful if the tr links are hidden by default, and only made visible by css+javascript.
     */
    function word_then_tr_link ($args)
    {
    	return $this->word($args) . $this->standalone_tr_link();
    }
    
    
    /**
     * @param array $args an array of arguments to be replaced in the lookup string
     * @return string translated word without any <a> tags, to avoid nested hyperlinks or worse things
     */
    function word_without_a_tags($args) {
        return str_replace(
            array("<a ", "<a>", "</a>"),  // replace a-tags
            array("<u ", "<u>", "</u>"),  // with u-tags
            $this->word($args)
        );
    }
    
    
    /**
     * @param array $args an array of arguments to be replaced in the lookup string
     * @return string translated word inside a tr link.
     */
    function word_in_tr_link($args)
    {
        $inner_text = $this->word_without_a_tags($args);
        $uri = PVars::getObj('env')->baseuri . "bw/admin";
        switch($this->_tr_success) {
        case Word::MISSING_WORD:
            // no english definition found
            return '<a '.
                'class="tr_link missing_word" '.
                'title="'.$this->_code .'UNDEFINED in english!" '.
                'target="new" '.
                'href="'.$uri.'/adminwords.php?lang=en&code='.$this->_code.'" '.
            '>'.$inner_text.'</a>';
        case Word::MISSING_TR:
            // no translation found in the intended language
            return '<a '.
                'class="tr_link missing_translation" '.
                'title="' . $this->_code . ' NOT TRANSLATED in '. $this->_lang .'" '.
                'target="new" '.
                'href="' . $uri . '/adminwords.php?lang=' . $this->_lang . '&code=' . $this->_code. '" '.
            '>' . $inner_text . '</a>';
        case Word::OBSOLETE:
        case Word::SUCCESSFUL:
            return $this->word($args) . $this->standalone_tr_link();
        case Word::NO_TR_LINK:
        default:
            return $this->word($args);
        }
    }
    
    
    /**
     * @return string a translation link without the translated word
     */
    function standalone_tr_link()
    {
        $uri = PVars::getObj('env')->baseuri . "bw/admin";
        switch($this->_tr_success) {
            case Word::MISSING_WORD:
                // no english definition found
                return '<a '.
                    'class="tr_link standalone missing_word" '.
                    'title="'.$this->_code.' undefined in english" '.
                    'target="new" '.
                    'href="'.$uri.'/adminwords.php?lang=en&code='.$this->_code.'" '.
                '>en</a>';
            case Word::MISSING_TR:
                // no translation found in the intended language
                return '<a '.
                    'class="tr_link standalone missing_translation" '.
                    'title="' . $this->_code . ' NOT TRANSLATED in '. $this->_lang .'" '.
                    'target="new" '.
                    'href="' . $uri . '/adminwords.php?lang=' . $this->_lang . '&code=' . $this->_code. '" '.
                '>' . $this->_lang . '</a>';
            case Word::OBSOLETE:
                // english translation has been changed, so the intended language needs an update
                return '<a '.
                    'class="tr_link standalone obsolete" '.
                    'title="' . $this->_code . ' OBSOLETE in '. $this->_lang .'" '.
                    'target="new" '.
                    'href="' . $uri . '/adminwords.php?lang=' . $this->_lang . '&code=' . $this->_code. '" '.
                '>' . $this->_lang . '</a>
                ';
            case Word::SUCCESSFUL:
                // translation has been found in the intended language
                switch($this->_tr_quality) {
                    case Word::AWKWARD:
                        // the translation is just terrible! It was written very quickly, with little care.
                        // so far the DB has no field for translation quality!
                        return '<a '.
                            'class="tr_link standalone successful_translation awkward" '.
                            'title="' . $this->_code . ' AWKWARD in '. $this->_lang .'" '.
                            'target="new" '.
                            'href="' . $uri . '/adminwords.php?lang=' . $this->_lang . '&code=' . $this->_code. '" '.
                        '>' . $this->_lang . '</a>';
                    case Word::DEBATABLE:
                        // some people think the translation should be improved.
                        // so far the DB has no field for translation quality!
                        return '<a '.
                            'class="tr_link standalone successful_translation debatable" '.
                            'title="' . $this->_code . ' DEBATABLE in '. $this->_lang .'" '.
                            'target="new" '.
                            'href="' . $uri . '/adminwords.php?lang=' . $this->_lang . '&code=' . $this->_code. '" '.
                        '>' . $this->_lang . '</a>';
                    default:
                        // translation is ok
                        return '<a '.
                            'class="tr_link standalone successful_translation fine" '.
                            'title="EDIT ' . $this->_code . ' in '. $this->_lang .'" '.
                            'target="new" '.
                            'href="' . "$uri/adminwords.php?lang=$this->_lang&code=$this->_code". '" '.
                        '>' . $this->_lang . '</a>';
                }
                break;
            default:
                // assume it is NO_TR_LINK
            case Word::NO_TR_LINK:
                return '';
        }
    }
}
?>