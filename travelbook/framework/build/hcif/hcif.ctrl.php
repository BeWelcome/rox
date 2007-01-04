<?php
/**
 * HC Interface controller
 *
 * @package hcif
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: hcif.ctrl.php 68 2006-06-23 12:10:27Z kang $
 */
class HcifController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Hcif();
        $this->_view  = new HcifView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function exAuth() {
        $username = $_GET['username'];
        $key      = $_GET['p'];
        $OnePad   = $_GET['OnePad'];
        $picpath  = $_GET['pp'];
        if ($key != "t22abul8950arasa") { 
            PPHP::PExit();
        }
        $this->_model->updateTanTable($username, $OnePad, $picpath);
        PPHP::PExit();
    }
    
    public function index() {
    }
    
    public function topMenu() {
        $this->_view->hcTopmenu();
    }
}
?>