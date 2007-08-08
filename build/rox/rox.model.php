<?php
/**
 * rox model
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class Rox extends PAppModel {
    protected $dao;
    
    // supported languages for translations; basis for flags in the footer
	private $_langs = array();
	/*
	    = array(
	    'en', 'fr', 'esp', 'de', 'it', 'ru', 'espe', 'pl', 'tr', 'lt', 'nl', 'dk',
	    'cat', 'cat', 'fi', 'pt', 'hu', 'lv', 'gr', 'no', 'srp', 'bg', 'br', 'ge'
		);
	*/
    
    
    public function __construct() {
        parent::__construct();
        
        // this logic is taken from SwitchToNewLang in lang.php
        // TODO: it is fun to offer the members the language of the volunteers, i.e. 'prog',
        // so I don't make any exceptions here; but we miss the flag - the BV flag ;-)
        // TODO: is it consensus we use "WelcomeToSignup" as the decision maker for languages?
        $query = '
SELECT `ShortCode`
FROM `words`
WHERE code = \'WelcomeToSignup\'';
        $result = $this->dao->query($query);
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $this->_langs[] = $row->ShortCode;
        }
        
    }
    
    /**
     * set defaults
     */
    public function loadDefaults() {
        if (!isset($_SESSION['lang']) || !file_exists(SCRIPT_BASE.'text/'.$_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        PVars::register('lang', $_SESSION['lang']);
        
        $loc = array();
        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php';
        setlocale(LC_ALL, $loc);
        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/page.php';
        
        return true;
    }
    
    public function getLangNames() {
        
        $l =  '';
		foreach ($this->_langs as $lang) {
		    $l .= '\'' . $lang . '\',';
		}
		$l = substr($l, 0, (strlen($l)-1));
		
        $query = '
SELECT `EnglishName`, `ShortCode`
FROM `languages`
WHERE `ShortCode` in (' . $l . ')
		';
        $result = $this->dao->query($query);
        
        $langNames = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $langNames[$row->ShortCode] = $row->EnglishName;
        }
        return $langNames;
    }
    
}
?>