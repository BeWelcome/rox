<?php
/**
 * signup controller
 *
 * @package signup
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class SignupController extends PAppController {

    /**
     * Model instance
     * 
     * @var Signup
     */
    private $_model;    // TODO: unused - remove?
    /**
     * View instance
     * 
     * @var View
     */
    private $_view;
    
    /**
     * Constructor
     * 
     * @param void
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new Signup();
        $this->_view =  new SignupView($this->_model);
        if (!PModules::moduleLoaded('user')) {
            throw new PException('Require module "user"!');
        }
    }
    
    /**
     * Destructor
     * 
     * @param void
     */
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * Index function
     * 
     * Currently the index consists of following possible requests:
     * register    - registration form to page content
     * confirm   - confirmation redirect to signup	 
     * 
     * @param void
     */
    public function index() {
        
        $request = PRequest::get()->request;
        
        if (!isset($request[1]))
            $request[1] = '';
        
        switch($request[1]) {
            
            // FIXME: remove
            case 'test':
                $this->_model->test();
                PPHP::PExit();
                break;
                
            // register form
            default:
            case 'register':
                if (!PModules::moduleLoaded('mail')) {
                    throw new PException('Module "mail" not found!');
                }
                // start output buffering to save all to content
                ob_start();
                $this->_view->registerForm();
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
			/*
                case 'confirm':
                ob_start();
                $username = "";
                $email = "";
                $this->_view->confirmation($username, $email);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
			*/

            case 'termsandconditions':
                $this->_view->showTermsAndConditions();
                PPHP::PExit();    // all layout done in template
        }
    }
}
?>
