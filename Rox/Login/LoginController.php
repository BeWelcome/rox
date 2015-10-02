<?php

namespace Rox\Login;

use Symfony\Component\HttpFoundation\Request;

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class LoginController
{
    /**
     * Shows the public start page
     *
     * @return StartPage
     */
    public function checkCredentials(Request $request) {
        $loginController = new \LoginController();
        $args = new \stdClass();
        $args->post = array(
            'u' => $request->request->get('u'),
            'p' => $request->request->get('p')
        );
        $args->request = '';
        $result = $loginController->loginCallback($args, null, null);
        if ($result) {
            // redirect to /home
            header('Location: /home');
        } else {
            header('Location: /');
        }
    }
}