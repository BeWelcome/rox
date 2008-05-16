<?php


class LoginController
{
    public function loginCallback($args, $action, $mem_for_redirect)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;
        $model = new LoginModel();
        
        
        if (!$user = $model->login($args->post['u'], $args->post['p'])) {
            // uuh
            $memory->login_errors = 'login_failed';
        } else if (!$model->loggedIn()) {
            $memory->login_errors = 'login_failed';
        } else {
            $memory->login_errors = false;
        }
        
        /*
        if (isset($args->post['memory'])) {
            $str = $args->post['memory'];
            $memory->prev = $str;
        } else {
            $memory->prev = false;
        }
        */
        
        /*
        if (isset($args->post['redirect_req'])) {
            return $args->post['redirect_req'];
        }
        */
    }
}


?>