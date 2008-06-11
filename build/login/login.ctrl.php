<?php


class LoginController
{
    public function loginCallback($args, $action, $mem_for_redirect)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;
        $model = new LoginModel();
        
        $post = $args->post;

        
        // note:
        // all the echos are buffered by the framework,
        // and sent out after the redirect.
        
        
        if (empty($post['u'])) {
            echo 'no username given.';
            
        } else if (!$member = $model->getBWMemberByUsername($username = trim($post['u']))) {
            echo 'member "'.$username.'" does not exist';
            
        } else if (!is_string($post['p'])) {
            echo 'no password given';
            
        } else if (!$model->checkBWPassword($member, $password = trim($post['p']))) {
            echo 'wrong password given for username '.$member->Username;
            
        } else {
            // bw member exists, and pw matches.
            
            // what about the tb user?
            if (!$tb_user = $model->getTBUserForBWMember($member)) {
                // no, he's not in TB. Buuuh.
                // Create new?
                echo "no tb user found with handle = '$member->Username'";
            } else if (!$model->checkTBPassword($tb_user, $password)) {
                // tb password didn't match.
                echo "given password does not match for tb user '$member->Username'"; 
            } else {
                // tb pw does match! yeah.
                // can now change the tables and session so that user is logged in.
                if (!$model->setBWMemberAsLoggedIn($member)) {
                    // something in the status was not ok.
                    echo "status was not ok..";
                } else {
                    echo "login successful";
                    $model->setupBWSession($member);
                    $model->setTBUserAsLoggedIn($tb_user);
                }
            }
        }
    }
}


?>