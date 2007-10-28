<?php
/**
 * Default controller
 *
 * @package default
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: default.ctrl.php 68 2006-06-23 12:10:27Z kang $
 */
class PDefaultController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new PDefault();
        $this->_view  = new PDefaultView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() {
        $this->is404();
    }
    
    public function output($raw = false) {
        PSurveillance::setPoint('starting_output');
        
        return $this->_view->doOutput($raw);
    }

    public function is404() {
        PPHP::PExit();
    }
}
?>