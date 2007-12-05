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
 * rox model
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class Rox extends PAppModel {
    protected $dao;
    
    // supported languages for translations; basis for flags in the footer
	private $_langs = array();
    
	/**
	 * @see /htdocs/bw/lib/lang.php
	 */
    public function __construct()
    {
        parent::__construct();
        
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
     * TODO: check: how do we replace the files base.php and page.php? do we need a
     * replacement at all?
     * @see loadDefault in /build/mytravelbook/mytravelbook.model.ctrl
     * @see __construct in /build/rox/rox.model.ctrl
     * @param
     * @return true
     */
    public function loadDefaults()
    {
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        PVars::register('lang', $_SESSION['lang']);
        
        if (file_exists(SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php')) {
	        $loc = array();
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php';
	        setlocale(LC_ALL, $loc);
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/page.php';
        }
        
        return true;
    }
    
    /**
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return boolean if language is supported true, otherwise false
     */
    public function isValidLang($lang)
    {
        return in_array($lang, $this->_langs);
    }
    
    /**
     * @param
     * @return associative array mapping language abbreviations to 
     * 			long, English names of the language
     */
    public function getLangNames()
    {
        
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
    
    public function getNewMessagesNumber($_idUser)
    {
        $query = '
SELECT COUNT(*) AS n
FROM `messages`
WHERE `IdReceiver` = ' . $_idUser . '
AND `WhenFirstRead` = 0';
        $result = $this->dao->query($query);
        $record = $result->fetch(PDB::FETCH_OBJ);
		return $record->n;
    }
}
?>