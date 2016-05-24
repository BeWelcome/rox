<?php

namespace Rox\Login;

use Rox\Framework\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
* Login controller
*
* @package Framework
* @author shevek
*/
class LoginController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function checkCredentials(Request $request) {
        // Use the old login controller to do the checks for now
        $loginController = new \LoginController();
        $args = new \stdClass();
        $args->post = array(
            'u' => $request->request->get('u'),
            'p' => $request->request->get('p')
        );
        $args->request = $request->getPathInfo();
        $action = new \stdClass;
        $action->count = 0;
        $action->redirect_req = '';
        $memredirect = new \stdClass;

        $result = $loginController->loginCallback($args, $action, $memredirect);
        if ($result) {
            return $this->redirect('/home');
        } else {
            return $this->redirect('/');
        }
    }
}