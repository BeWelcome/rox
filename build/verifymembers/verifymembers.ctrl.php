<?php

/**
 * Hello verifymembers controller
 *
 * @package verifymembers
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

 require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right 
 require_once("../htdocs/bw/lib/FunctionsCrypt.php") ; // Requiring BW right 


class VerifymembersController extends RoxControllerBase
{

    private $_model;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new VerifyMembersModel();
    }
    
    public function __destruct() {
        unset($this->VerifyMembersModel);
    }
    

    /**
     * decide which page to show.
     * This method is called automatically
     */
//    public function index($args = false)
    public function index($args=false)
    {
        $request = PRequest::get()->request;
        
        // look at the request.
        switch ($request[0]) {
            case 'calculator':
                $page = new HellouniverseCalculatorPage();
                break;
            default:
                if (isset($request[1])) { // if there is an additional parameter
				 	 switch ($request[1]) {
            		 		case 'prepareverifymember':
									 $post_args = $args->post;
//echo "print_r(\$post_args)=",print_r($post_args) ;
//									die(" \$post_args[username_to_verify]=".$post_args['username_to_verify']) ;
//									$m=$this->_model->LoadPrivateData($post_args['username_to_verify'],$post_args['member_to_check_pw']) ;
									$m=$this->_model->LoadPrivateData("jeanyves","password") ;
									if (!isset($m)) {													 
            	 	  				   $page = new VerifyMembersPage("no such member"); // With error
									}
									else {
									    $page = new VerifyMembersProceedPage($m);
									}
               			 	break;
            		 		case 'doverifymember':
									
								default :
                    				die("\$request[1]=".$request[1]) ;
					 } // end of switch ($request[1])
                }
				 else {
            	 	  $page = new VerifyMembersPage(""); // Without error
				 }
				 break ;            
        }
        // return the $page object, so the "$page->render()" function can be called somewhere else.
        return $page;
    }
    
    
    public function calculatorCallback($args, $action, $mem_redirect, $mem_resend)     {
        $post_args = $args->post;
        
        // give some information to the page that will show up after the redirect
        $mem_redirect->x = $x = $post_args['x'];
        $mem_redirect->y = $y = $post_args['y'];
        $mem_redirect->z = $x + $y;
    }
}


?>