<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.


*/



/**
 * InviteAFriend controller
 *
 * @package InviteAFriend
 * @author Micha <lupochen>
 */
class InviteController extends RoxControllerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new InviteModel();
    }

    public function index($args = false)
    {
        $request = $args->request;
        $a = new App_user();
        $logged = $a->isBWLoggedIn('NeedMore,Pending');
        
        switch (isset($request[1])) {
            case 'sent':
                $page = new InviteSentPage();
                break;
            case '':
            default:
                if ($logged) {
                    $page = new InvitePage();
                } else {
                    $this->redirectHome();
                }
        }
        
        $page->setModel($this->model);
    
        return $page;
    }
    
    
    /**
     * Callback function for InviteAFriend page
     *
     * @param Object $args
     * @param Object $action 
     * @param Object $mem_redirect memory for the page after redirect
     * @param Object $mem_resend memory for resending the form
     * @return string relative request for redirect
     */
    public function InviteCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;
        
        $mem_redirect->post = $args->post;
        
        if (!APP_User::loggedIn()) {
            // not logged in.
            // the login form will be shown after the automatic redirect
            // after successful login, the message is recovered.
        } else if ($count < 0) {
            // session has expired while user was typing.
            $mem_redirect->expired = true;
        } else if ($mem_resend->already_sent_as) {
            // form has already been processed, with the message sent!
            // for a new message, the user needs a new form.
            // tell the redirected page which message has been already sent!
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
        } else {
            if ($count > 0) {
                // form has been already processed $count times,
                // but the last time it was not successful.
                // so, we can send again
                // but tell the page how many times it had failed before
                $mem_redirect->fail_count = $count;
            } else {
                // first time to try sending the form
            }
            
            // now finally try to send it.
            $result = new ReadOnlyObject($this->model->sendOrComplain($args->post));
            
            if (count($result->problems) > 0) {
                $mem_redirect->problems = $result->problems;
            } elseif (!$result->status){
                $mem_redirect->problems = array('email','Unknown error - Invitation not sent.');
            } else {
                // sending message was successful
                $mem_resend->already_sent_as = $result->message_id;
                return "invite/sent";
            }
        }

        return implode('/', $args->request);
    }
    
}


?>