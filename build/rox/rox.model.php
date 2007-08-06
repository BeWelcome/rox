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
	// TODO: these were taken from a list in BW footer template; but eg. "ge"
	// does not work; the list itself should be fetched from DB
	private $_langs = array(
	    'en', 'fr', 'esp', 'de', 'it', 'ru', 'espe', 'pl', 'tr', 'lt', 'nl', 'dk',
	    'cat', 'cat', 'fi', 'pt', 'hu', 'lv', 'gr', 'no', 'srp', 'bg', 'br', 'ge'
		);
    
    
    public function __construct() {
        parent::__construct();
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