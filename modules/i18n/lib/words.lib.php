<?php
/**
 * Enables us to use content from words table of BW from within the platform PT structure.
 * Instantiate in the first lines of your template, then call the "get" method.
 * 
 * @author  Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 * @see     /htdocs/bw/lib/lang.php
 * 
 * FIXME: This is just a temporary solution to be able to get ww@TB.
 * TODO: tracking of unused words
 * FIXME: integrate $_SESSION['TranslationArray'] - but how, if we don't wanna copy it?!
 * FIXME: no editorial stuff yet, compare wwinlang!
 */

class MOD_words
{
    private $_lang;
    private $_whereCategory = '';
        
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
    }
    
    /**
     * Looks up (localized) texts in BW words table.
     * Newlines are replaced by HTML breaks, backslashes are stripped off.
     *  
     * @see wwinlang in /lib/lang.php
     * @param	string	$code keyword for finding text, not allowed to be empty
     * @return	string	localized text, in case of no hit a small HTML comment
     */
    public function get($code) {
        
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
WHERE `code`=\'' . $code . '\' and `ShortCode`=\'' . $this->_lang . '\'';
        }
        
        $q = $this->dao->query($query);
        $words = $q->fetch(PDB::FETCH_OBJ);
        if (!$words) {
            return '<!-- empty -->';
        }
        
        return $this->rework($words->Sentence);
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
    public function getFormatted($code) {
        $plainString = $this->get($code);
        $args = func_get_args();
        if (count($args) > 1) {
            array_shift($args);
            return vprintf($plainString, $args);
        }
        return $plainString;
    }
    
    /**
     * Prepares column output for display on page
     *
     * @param string $s column value
     * @return nl2br'ed-stripslashed column value 
     */
    private function rework($s) {
        return nl2br(stripslashes($s));
    }
     
}
?>