<?php

/**
 * verifymembers controller
 *
 * @package verifymembers
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
echo ' bananenbrot ';
// require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right 
// require_once("../htdocs/bw/lib/FunctionsCrypt.php") ; // Requiring BW right
// TODO: use the MyTB right.. (MOD_right)
echo ' rabenbrot ';
class VerifymembersController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args=false)
    {
        $request = $args->request;
        $model = new VerifyMembersModel;
        
        print_r($args->post);
        
        // look at the request.
        switch (isset($request[1]) ? $request[1] : false) {
            case false:
            case '':
                // no request[1] was specified
                $page = new VerifyMembersPage(""); // Without error
                break;
            case 'prepareverifymember':
                // a nice trick to get all the post args as local variables...
                // they will all be prefixed by 'post_'
                extract($args->post, EXTR_PREFIX_ALL, 'post');
                if (!isset($post_username_to_verify) || !isset($post_member_to_check_pw)) {
                    // the post args you need are not set. what happened?
                    // show a page with error
                    //
                    //     note by Andreas:
                    //     the problem is when the PPostHandler from PT framework makes a redirect.
                    //     I really don't know why it does. I am trying to find out.
                    //     Obviously, all the POST values are lost after a redirect.
                    //
                    $page = new VerifyMembersPage("insufficient POST arguments.");
                } else if (!$m = $model->LoadPrivateData(
                    $post_username_to_verify,
                    $post_member_to_check_pw
                )) {
                    // $m not found... 
                    // show a page with error
                    $page = new VerifyMembersPage("no member with username '$post_username_to_verify' found.");
                } else {
                    $page = new VerifyMembersProceedPage($m);
                }
                break;
            case 'doverifymember':
            default :
                die("\$request[1]=".$request[1]) ;
                // TODO: please, no dying... show a default instead!
        }
        // return the $page object,
        // so the framework can call the "$page->render()" function.
        return $page;
    }
}


?>