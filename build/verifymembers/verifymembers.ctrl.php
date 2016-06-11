<?php

/**
 * verifymembers controller
 *
 * @package verifymembers
 * @author JeanYves, Micha
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class VerifymembersController extends RoxControllerBase
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new VerifyMembersModel;
    }

    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index($args=false)
    {
        $request = $args->request;
        $mem_redirect = $this->mem_redirect;
        $model = $this->model;

        if (!$this->model->getLoggedInMember())
        {
            $page = new VerifyMustLoginPage();
            $page->setRedirectURL(implode('/',$request));
            return $page;
        }

        // look at the request.
        switch (isset($request[1]) ? $request[1] : false) {
            case 'verifiersof':
                $VerifierList=$model->LoadVerifiers($request[2]) ;
                $page = new VerifiedMembersViewPage($request[2],"",$VerifierList);
                break ;
            case 'approvedverifiers':
                $ApprovedVerifiers=$model->LoadApprovedVerifiers() ;
                $page = new VerifiedApprovedVerifiers($ApprovedVerifiers);
                break ;
            case 'verifiersby':
                $VerifierList=$model->LoadVerified($request[2]) ;
                $page = new VerifiedMembersViewPage("",$request[2],$VerifierList);
                break ;
            case 'doverifymember':
                if ($model->AddNewVerified($args->post)) {
                    $VerifierList=$model->LoadVerifiers($args->post["IdMemberToVerify"]) ;
                    $page = new VerifiedMembersViewPage($model->CheckAndGetUsername($args->post["IdMemberToVerify"]),"",$VerifierList);
                }
                else {
                    $page = new VerifyMembersPage("Something weird happen bug ?");
                }
                break ;
            default :
                $member_self = $this->getMember($this->_session->has( 'IdMember' ) ? $this->_session->get('IdMember') : false);
                if (!isset($member_self)) {
                    // no member specified
                    $page = new VerifyNoMemberSpecifiedPage();
                } else if (!isset($request[1])) {
                    // no member specified
                    $page = new VerifyNoMemberSpecifiedPage();
                } else if (!$member_other = $this->getMember($request[1])) {
                    // did not find such a member
                    $page = new MembersMembernotfoundPage;
                } else {
                    // found a member with given id or username
                    if ($member_other->id == $member_self->id) {
                        // user is watching her own profile
                        return new VerifyMyselfPage();
                    } elseif ($member_self->Status != 'Active' || $member_other->Status != 'Active') {
                        return new VerifyMembersNotActivePage();
                    }
                    if (isset($request[2]) && $request[2] == 'proceed') {
                        if ($mem_redirect && ($mem_redirect->member_data || $mem_redirect->post)) {
                            $page = new VerifyMembersProceedPage($request[1]);
                        } else {
                            $request[2] = '';
                            $this->redirect(implode('/',$request));
                        }
                    } elseif (isset($request[2]) && $request[2] == 'finish') {
                        $page = new VerifyMembersFinishPage($request[1]);
                    } else {
                        $page = new VerifyMembersPage();
                    }
                    $page->member1 = $member_self;
                    $page->member2 = $member_other;
                }
        }
        // return the $page object,
        // so the framework can call the "$page->render()" function.
        return $page;
    }

    protected function getMember($cid)
    {
        $model = new MembersModel;
        if (is_numeric($cid)) {
            return $model->getMemberWithId($cid);
        } else if (!empty($cid)) {
            return $model->getMemberWithUsername($cid);
        } else {
            return false;
        }
    }

    /**
     * handles edit profile form post - profile updating
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function checkPasswordCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $this->cleanVars($args->post);
            $request = $args->request;
            $errors = $this->model->checkPasswordsOfMembers($args->post);
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->problems = $errors;
                $mem_redirect->post = $vars;
                return false;
            }

            $member_data = array();
            $member_data[1] = $this->model->LoadPrivateData($vars['cid1'], $vars['password1']);
            $member_data[2] = $this->model->LoadPrivateData($vars['cid2'], $vars['password2']);
            if (!$member_data[1] || !$member_data[2]) {
                // show form again
                $vars['errors'] = array('no_member_data');
                $mem_redirect->problems = $vars['errors'];
                $mem_redirect->post = $vars;
                return false;
            }
            $mem_redirect->member_data = $member_data;

            $str = $request[0].'/'.$request[1];
            if (in_array('proceed',$request)) return implode($request,'/');
            return $str.'/proceed';
        }
    }

    /**
     * handles verification form post
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function verifyCallback($args, $action, $mem_redirect, $mem_resend)
    {
        if (isset($args->post)) {
            $vars = $this->cleanVars($args->post);
            $vars_old = $mem_redirect->post;

            $request = $args->request;
            $errors = $this->model->checkVerificationForm($args->post);
            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $mem_redirect->problems = $errors;
                $mem_redirect->post = $vars;
                return false;
            }

            $success = $this->model->AddNewVerified($vars);
            if (!$success) $mem_redirect->problems = array('Could not update profile');

            // Redirect to a nice location like editmyprofile/finish
            $str = implode('/',$request);
            return $request[0].'/'.$request[1].'/finish';
        }
    }
}

