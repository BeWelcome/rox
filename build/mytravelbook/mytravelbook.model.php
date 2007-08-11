<?php
/**
 * myTravelbook model
 *
 * @package mytravelbook
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: mytravelbook.model.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * myTravelbook model
 *
 * @package mytravelbook
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class Mytravelbook extends PAppModel {
    protected $dao;
    
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
}
?>