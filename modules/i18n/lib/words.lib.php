<?php
/**
 * Enables us to use content from words table of BW from within the platform PT structure.
 * Instantiate in the first lines of your template, then call the "get" method.
 * 
 * @author		Felix van Hove <fvanhove@gmx.de>
 * @license		http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 * 
 * FIXME: This is just a temporary solution to be able to get ww@TB.
 * FIXME: No beauty, no tracking of unused words, no language switches, nothing static.
 * FIXME: Language isn't supposed to work.
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
     * Looks up (localized) texts in BW words table. No HTML formatting, no
     * escaping, no security - plainly fetching strings. Compare with
     * bw/lib/lang.php and its functions.
     * 
     * @param	string	keyword for finding text
     * @param	string	page name or meta keyword, where text belongs to
     * @return	string	localized text, in case we had no hit a small HTML comment
     */
    public function get($code, $category=null) {
        
        $whereCategory = $this->_whereCategory;
        if (!empty($category)) {
            $whereCategory = ' `category`=\'' . $category . '\''; 
        }
        
        if (is_numeric($code)) {
            $query = '
SELECT SQL_CACHE `Sentence`, `donottranslate`
FROM `words`
WHERE `id`=' . $this->dao->escape($code);
        } else {
            $query = '
SELECT SQL_CACHE `Sentence`, `donottranslate`
FROM `words`
WHERE `code`=\'' . $code . '\' and `IdLanguage`=\'' . $this->_lang . '\'';
        }
        
        $q = $this->dao->query($query);
        $words = $q->fetch(PDB::FETCH_OBJ);
        if (!$words) {
            return '<!-- empty -->';
        }
        return $words->Sentence;
    }
     
}
?>