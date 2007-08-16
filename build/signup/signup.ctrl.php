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
    private $_model;
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
                
        // index is called when http request = ./user
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch($request[1]) {
                                
            // confirms a registration
            case 'confirm':
                if (
                    !isset($request[2]) 
                    || !isset($request[3]) 
                    || !preg_match(User::HANDLE_PREGEXP, $request[2])
                    || !$this->_model->handleInUse($request[2])
                    || !preg_match('/^[a-f0-9]{16}$/', $request[3])
                ) {
                    $error = true;
                } else {
                    if ($this->_model->confirmRegister($request[2], $request[3])) {
                        $error = false;
                    } else {
                        $error = true;
                    }
                }
                ob_start();
                $this->_view->registerConfirm($error);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
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
            default:
                if (preg_match(User::HANDLE_PREGEXP, $request[1])) {
                    ob_start();
                    $this->_view->userPage($request[1]);
                    $str = ob_get_contents();
                    ob_end_clean();
                    $P = PVars::getObj('page');
                    $P->content .= $str;
                }
                break;
*/
        }
    }
    
}
?>
