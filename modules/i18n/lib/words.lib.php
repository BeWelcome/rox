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
 * Enables us to use content from words table of BW from within the platform PT structure.
 * Instantiate in the first lines of your template, then call the "get" method.
 * 
 * @author  Felix van Hove <fvanhove@gmx.de>
 * @see     /htdocs/bw/lib/lang.php
 * 
 * FIXME: In need of categories to be able to fetch arrays of texts instead of every
 * single text separately.
 * TODO: tracking of unused words
 * FIXME: integrate $_SESSION['TranslationArray'] - but do we really need it?
 */

class MOD_words
{
    private $_lang;
    private $_whereCategory = '';
    private $_offerTranslationLink = false;
    
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
        
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
        $R = MOD_right::get();
        if ($R->hasRight("Words") >= 10) {
            $this->_offerTranslationLink = true;
        }
        
    }
    
    /**
     * Looks up (localized) texts in BW words table.
     * Newlines are replaced by HTML breaks, backslashes are stripped off.
	 * Takes a variable number of arguments as c-style formatted string.
	 * 
     * @see wwinlang in /lib/lang.php
     * @param	string	$code keyword for finding text, not allowed to be empty
     * @param	string	$? formatted according to a variable number of arguments	
     * @param	...
     * @return	string	localized text, in case of no hit a small HTML comment
     */
    public function getFormatted($code)
    {
        $plainString = $this->get($code);
        $args = func_get_args();
        if (count($args) > 1) {
            array_shift($args);
            return vsprintf($plainString, $args);
        }
        return $plainString;
    }
    
    /**
     * 
     */
    public function get($code)
    {
        $word = $this->_getForLang($code, $this->_lang);
        if (empty($word)) {
            if ($this->_lang === 'en') {
                if ($this->_offerTranslationLink) {
                    $word = $this->_buildTranslationLink($code, $this->_lang);
                } else {
                    $word = "<!-- translation code: $code -->";
                }
            } else {
                if ($this->_offerTranslationLink) {
                    $word = $this->_buildTranslationLink($code, $this->_lang);
                } else {
                    $word = $this->_getForlang($code, 'en');
                    if (empty($word)) {
                        $word =  "<!-- translation code: $code -->";
                    }
                }
            }
        }
        return $word;
    }
    
    /**
     * Looks up (localized) texts in BW words table according to provided
     * language.
     * Newlines are replaced by HTML breaks, backslashes are stripped off.
     *  
     * @see wwinlang in /lib/lang.php
     * @param	string	$code keyword for finding text, not allowed to be empty
     * @param	string	$lang 2-letter code for language
     * @return	string	localized text, in case of no hit a small HTML comment
     */
    private function _getForLang($code, $lang)
    {
        
        $whereCategory = $this->_whereCategory;
        
        /* we still need to find a clear parameter handling for this
        if (!empty($category)) {
            $whereCategory = ' `category`=\'' . $category . '\''; 
        }
		*/
        
        if (is_numeric($code)) {
            $query = '
SELECT SQL_CACHE `Sentence`, `donottranslate`
FROM `words`
WHERE `id`=' . $this->dao->escape($code);
        } else {
            $query = '
SELECT SQL_CACHE `Sentence`, `donottranslate`
FROM `words`
WHERE `code`=\'' . $code . '\' and `ShortCode`=\'' . $lang . '\'';
        }
        
        $q = $this->dao->query($query);
        $words = $q->fetch(PDB::FETCH_OBJ);
        if (!$words) {
            return null;
        }
        
        return $this->_rework($words->Sentence);
    }
    
    /**
     * Prepares column output for display on page
     *
     * @param string $s column value
     * @return nl2br'ed-stripslashed column value 
     */
    private function _rework($s)
    {
        return nl2br(stripslashes($s));
    }
    
    /**
     * @return anchor for translation team; to easily
     * get to a page, where the missing expression
     * can be added
     */
    private function _buildTranslationLink($code, $lang)
    {
        $uri = PVars::getObj('env')->baseuri . "bw/admin";
        return "<a target=\"_new\" href=\"$uri/adminwords.php?IdLanguage=" .
            "$lang&code=$code\">Missing word: $code</a>";
    }
     
}
?>