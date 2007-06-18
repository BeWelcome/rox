<?php
/**
 * HC Interface controller
 *
 * @package hcif
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: hcif.ctrl.php 198 2007-02-01 21:16:25Z marco $
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
    	If (!ISSET($_GET['k'])|| !ISSET($_GET['OnePad']))  PPHP::PExit();
        $u = $_GET['u'];
        $key      = $_GET['k'];
        $OnePad   = $_GET['OnePad'];
        $e  = $_GET['e'];
        $p	  = $_GET['p'];
       if ($key != "fh457Hg36!pg29G")  { 
            PPHP::PExit();
        }
          if (is_string($u)) {
                        $u = trim($u);
                        $u = stripslashes($u);
                    }
                    
                 if (is_string($p)) {
                        $p = trim($p);
                        $p = stripslashes($p);
                    }
                    
                  if (is_string($e)) {
                        $e = trim($e);
                        $e = stripslashes($e);
                    }     
                    
                        if (is_string($OnePad)) {
                        $OnePad = trim($OnePad);
                        $OnePad = stripslashes($OnePad);
                    }         
        $this->_model->updateTanTable($u, $OnePad);
        if (!$this->_model->registerextuser($u,$e,$p)) error_log("reg failed",0);
        PPHP::PExit();
    }
    
    public function index() {
    }
    
    public function topMenu() {
        $this->_view->hcTopmenu();
    }
}
?>