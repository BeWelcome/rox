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

		$this->WordMemcache=new MOD_bw_memcache("word","Sentence","code") ;

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
        if ($translation == $code) {
            if (is_array($args)) {
                if (!empty($args)) {
                    $translation .= " [" . implode(",", $args) . "]";
                }
            } elseif (is_string($args)) {
                $translation .= " [" . $args . "]";
            }
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
                "FROM `word` ".
                "WHERE `id`=" . $this->_dao->escape($code)
            ;
        } else {
            // First try in memcache
            if ($value=$this->WordMemcache->GetValue($code,$lang)) {
                return $value;
            }
            $query =
                "SELECT SQL_CACHE `code`,`Sentence`, `donottranslate`, `updated` ".
                "FROM `word` ".
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
                "FROM `word` ".
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
     * looks up a word keycode, and returns an object of type LookedUpWord.
     * The translations come from the Symfony translator (its catalogues are built from the word
     * table by App\TranslationLoader\DatabaseLoader, which also falls back to English for
     * missing or outdated translations). If there is no translation, the keycode itself is used.
     *
     * @param unknown_type $code the key code for the lookup
     * @return LookedUpWord information that is created from the word lookup
     */
    private function _lookup($code, $args, $lang = false, $get_raw = false)
    {
        if ($lang === false) {
            $lang = $this->_lang;
        }

        $trans = $this->_translator->trans(strtolower($code), [], null, $lang);

        if ($trans === strtolower($code)) {
            $lookup_result = $code;
        } else {
            $lookup_result = $this->_modified_sentence($trans, $args, $get_raw);
        }

        return new LookedUpWord($code, $lang, $lookup_result);
    }

    /**
     * Reads the (modified) translation sentence.
     * Modifications (if $get_raw is false):
     *  - stripslashes
     *  - nl2br
     *
     * @param string  $sentence
     * @param array   $args
     * @param boolean $get_raw true for raw string, false for modified string
     * @return string modified sentence
     */
    private function _modified_sentence($sentence, $args, $get_raw = false)
    {
        if ($get_raw) {
            $lookup_string = $sentence;
        } else {
            $lookup_string = nl2br(stripslashes($sentence));
        }

        // pad the args array with ' -x- ' so that vsprintf doesn't fail
        $args = array_pad($args, 10, ' -x- ');
        $translation =  $lookup_string;

        try {
            $translation = vsprintf($lookup_string, $args);
        } catch (ValueError) {
            // A stray % in the translation string tripped vsprintf try to fix by replacing
            // every % that does not start a valid conversion with an escaped version %%, then retry.
            $escaped = preg_replace('/%(?!%|(?:\d+\$)?[-+ 0]*(?:\'.)?\d*(?:\.\d+)?[bcdeEfFgGosuxX])/', '%%', $lookup_string);
            try {
                $translation = vsprintf($escaped, $args);
            } catch (ValueError) {
                // do nothing here $translation is returned as is.
            }
        }

        return $translation;
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
        if (empty($IdTrad)) {
            return ""; // in case there is nothing, return an empty string
        }
        if (!is_numeric($IdTrad)) { // Logging anomalie things to detect database problem if any
            $sBug = "it look like you are using MOD_WORD::mInTrad with and allready translated word [" . $IdTrad . "], a member_translation.id is expected and it should be numeric !";
            MOD_log::get()->write($sBug, "Bug");
            die($sBug);
        }

        // memberstrads is gone: group descriptions and membership comments are stored as single
        // rows in member_translation and the legacy columns (groups.IdDescription,
        // membersgroups.comment) hold that row's id. There is one row per id, so the preferred
        // language can't be applied any more.
        $query = "SELECT `content` FROM `member_translation` WHERE `id` = " . (int) $IdTrad;
        $q = $this->_dao->query($query);
        $row = $q ? $q->fetch(PDB::FETCH_OBJ) : false;
        if ($row && isset($row->content)) {
            return $this->ReplaceWithBr($row->content, $ReplaceWithBr);
        }
        MOD_log::get()->write("mInTrad Anomaly : no entry found for member_translation.id=#" . $IdTrad, "Bug");
        return ""; // If really nothing was found, return an empty string
	 } // end of mInTrad

    /**
     * @param $IdTrad the id of a member_translation record to retrieve
     * @param $ReplaceWithBr allows
     * @return string translated according to the best language find
     */
    public function mTrad($IdTrad,$ReplaceWithBr=false) {
		return ($this->mInTrad($IdTrad, $this->languageShortCode(), $ReplaceWithBr)) ;
	 } // end of mTrad

    /**
     * Resolves a language given in one of the legacy forms to a ShortCode that exists in the
     * `language` table: a ShortCode, a numeric id of the old `languages` table, or 0, -1, '' or
     * null for "the current member's language". Falls back to English.
     *
     * The translations table references languages by ShortCode only (IdLanguage is gone).
     */
    public function languageShortCode($language = null): string
    {
        static $known = [];

        $candidates = [];
        if (is_string($language) && '' !== $language && !is_numeric($language)) {
            $candidates[] = $language;
        } elseif (is_numeric($language) && (int) $language > 0) {
            $q = $this->_dao->query("SELECT `ShortCode` FROM `languages` WHERE `id` = " . (int) $language);
            $row = $q ? $q->fetch(PDB::FETCH_OBJ) : false;
            if ($row && !empty($row->ShortCode)) {
                $candidates[] = $row->ShortCode;
            }
        }
        if ($this->session->has('lang')) {
            $candidates[] = (string) $this->session->get('lang');
        }
        $candidates[] = 'en';

        foreach ($candidates as $shortCode) {
            if (!isset($known[$shortCode])) {
                $q = $this->_dao->query("SELECT `ShortCode` FROM `language` WHERE `ShortCode` = '" . $this->_dao->escape($shortCode) . "'");
                $known[$shortCode] = (bool) ($q && $q->fetch(PDB::FETCH_OBJ));
            }
            if ($known[$shortCode]) {
                return $shortCode;
            }
        }

        return 'en';
    }


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

        if (strpos($ss,"\\'")!==false || strpos($ss,'\\"')!==false) {
            $page = $_SERVER["PHP_SELF"] ?? "";
            MOD_log::get()->write("in module word->InsertInMTrad, for IdTrad=".$IdTrad. " The sentence is already escaped page [".$page."]", "Bug");
            $ss = stripslashes($ss);
        }

        // memberstrads is gone. Like App\Model\GroupModel, store the text as one member_translation
        // row without a member (object_id NULL) and hand out its id as the "IdTrad".
        $locale = $this->_dao->escape($this->languageShortCode(-1 == $_IdLanguage ? $this->_langWrite : $_IdLanguage));
        $field = $this->_dao->escape($this->memberTranslationField($TableColumn));
        $str = "INSERT INTO member_translation (object_id, locale, field, content) "
            . "VALUES (NULL, '" . $locale . "', '" . $field . "', '" . $this->_dao->escape($ss) . "')";
        $s = $this->_dao->query($str);
        if (!$s) {
            throw new PException('Failed in InsertInMTrad inserting in member_translation');
        }
        $IdTrad = $s->insertId();

        // update the IdTrad in the original table (if the TableColumn was given properly and the IdRecord too)
        if (!empty($TableColumn) and !empty($IdRecord)) {
            [$table, $column] = explode(".", $TableColumn) + [1 => ''];
            $str = "UPDATE `" . $table . "` SET `" . $column . "` = " . (int) $IdTrad . " WHERE id = " . (int) $IdRecord;
            $s = $this->_dao->query($str);
            if (!$s) {
                throw new PException('Failed in InsertInMTrad updating table column ' . $TableColumn);
            }
        }
        return ($IdTrad);
    } // end of InsertInMTrad

    /**
     * member_translation.field for texts that used to live in memberstrads (same names as
     * App\Model\GroupModel uses).
     */
    private function memberTranslationField($TableColumn): string
    {
        return match (strtolower((string) $TableColumn)) {
            'groups.iddescription' => 'GroupDescription',
            'membersgroups.comment' => 'GroupMembership',
            default => substr((string) $TableColumn, 0, 32),
        };
    }


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

        if ($IdOwner == 0) {
            $IdMember = $this->session->get('IdMember');
        } else {
            $IdMember = $IdOwner;
        }
        if (empty($IdTrad)) {
            return ($this->InsertInMTrad($ss,$TableColumn,$IdRecord, $IdMember)); // Create a full new translation
        }

        // $IdTrad is the id of a member_translation row (see InsertInMTrad)
        $s = $this->_dao->query("SELECT id, content FROM member_translation WHERE id = " . (int) $IdTrad);
        if (!$s) {
            throw new PException('Failed in ReplaceInMTrad retrieving IdTrad='.$IdTrad);
        }
        $rr = $s->fetch(PDB::FETCH_OBJ);
        if (!isset($rr->id)) {
            return ($this->InsertInMTrad($ss,$TableColumn,$IdRecord, $IdMember)); // the row is gone, create a new one
        }
        if ($ss !== (string) $rr->content) { // Update only if sentence has changed
            $str = "UPDATE member_translation SET content = '" . $this->_dao->escape($ss) . "' WHERE id = " . (int) $rr->id;
            $s = $this->_dao->query($str);
            if (!$s) {
                throw new PException('Failed in ReplaceInMTrad updating Sentence for IdTrad=#'.$IdTrad);
            }
        }
        return ($IdTrad);
    } // end of ReplaceInMTrad




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
        // the translation tool (App\Controller\Admin\TranslationController); edit leads to the add page when the
        // language has no translation yet
        $code = rawurlencode((string) $this->_code);
        if($this->_tr_success == self::MISSING_WORD){
            return PVars::getObj('env')->baseuri.'admin/translations/create/en/'.$code;
        } else {
            return PVars::getObj('env')->baseuri.'admin/translations/edit/'.$this->_trLinkLanguage().'/'.$code;
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
