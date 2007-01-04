<?php
/**
 * contains Cal controller
 *
 * @package cal
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: cal.ctrl.php 89 2006-06-30 11:05:28Z kang $
 */
/**
 * Cal controller
 *
 * @package cal
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class CalController extends PAppController 
{
    /**
     * @var Cal
     * @access private
     */
    private $_model;
    /**
     * @var CalView
     * @access private
     */
    private $_view;
    
    /**
     * @param void
     */
    public function __construct() 
    {
        parent::__construct();
        $this->_model = new Cal();
        $this->_view  = new CalView($this->_model);
    }
    
    /**
     * @param void
     */
    public function __destruct() 
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * index
     * 
     * @param void
     */
    public function index() 
    {
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        $matches = array();
        switch ($request[1]) {
            case 'acal':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($request[2]))
                    $request[2] = date('Ym');
                if (!preg_match('/^(\d{4})(\d{2})$/', $request[2], $matches))
                    break;
                $y = (int)$matches[1];
                $m = (int)$matches[2];
                ob_start();
                $this->_view->aCalMonth($y, $m);
                $str = ob_get_contents();
                ob_end_clean();
                echo $str;
                PPHP::PExit();
                break;
        }
        // matches a month
        if (preg_match('/^(\d{4})(\d{2})$/', $request[1], $matches)) {
            $_SESSION['APP_cal_currentyear']  = (int)$matches[1];
            $_SESSION['APP_cal_currentmonth'] = (int)$matches[2];
            if (isset($_GET['raw'])) {
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                $this->displayCalMonth();
            }
        // matches a day
        } elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $request[1], $matches)) {
            $_SESSION['APP_cal_currentyear']  = (int)$matches[1];
            $_SESSION['APP_cal_currentmonth'] = (int)$matches[2];
            $_SESSION['APP_cal_currentday']   = (int)$matches[3];
        }
    }
    
    public function displayCalDay() 
    {
        $y = isset($_SESSION['APP_cal_currentyear'])  ? $_SESSION['APP_cal_currentyear']  : idate('Y'); 
        $m = isset($_SESSION['APP_cal_currentmonth']) ? $_SESSION['APP_cal_currentmonth'] : idate('m');
        $d = isset($_SESSION['APP_cal_currentday'])   ? $_SESSION['APP_cal_currentday']   : idate('d');
        ob_start();
        $this->_view->calDay($y, $m, $d);
        $str = ob_get_contents();
        ob_end_clean();
        $P = PVars::getObj('page');
        $P->content .= $str;
    }
    
    public function displayCalMonth() 
    {
        $raw = isset($_GET['raw']);
        $y = isset($_SESSION['APP_cal_currentyear']) ? $_SESSION['APP_cal_currentyear'] : idate('Y'); 
        $m = isset($_SESSION['APP_cal_currentmonth']) ? $_SESSION['APP_cal_currentmonth'] : idate('m');
        if ($raw) {
            ob_start();
        }
        $this->_view->calMonth($y, $m);
        if ($raw) {
            $str = ob_get_contents();
            ob_end_clean();
            echo $str;
            PPHP::PExit();
        } 
    }
}
?>