<?php

namespace Rox\Login;

use Rox\Framework\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* Login controller
*
* @package Framework
* @author shevek
*/
class LoginController extends Controller
{

    public function loginAction(Request $request) {
        $page = new LoginPage($this->getRouting());

        // Setup the form used inside the template
        // \todo move to a separate form class
        $form = $this->getFormFactory()->createBuilder()
            ->add(
                'username',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => 'Username'
                    ],
                    'label' => false
                ]
            )
            ->add('password',
                PasswordType::class,
                [
                    'attr' => [
                        'placeholder' => 'Password'
                    ],
                    'label' => false
                ]
            )
            ->add(
                'login',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'btn btn-primary'
                    ]
                ]
            )
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // form was submitted and all inputs are valid
            // Do the login.
            $data = $form->getData();
            $args = new \stdClass();
            $args->post = array(
                'u' => $data['username'],
                'p' => $data['password'],
            );
            $args->request = $request->getPathInfo();
            $action = new \stdClass;
            $action->count = 0;
            $action->redirect_req = '';
            $memredirect = new \stdClass;

            $loginController = new \LoginController();
            $result = $loginController->loginCallback($args, $action, $memredirect);
            if ($result) {
                new RedirectResponse('/home');
            }
        }
        $page->initializeFormComponent(false);
        $page->addForm($form);
        return new Response($page->render());
    }

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
        return $result;

        if ($result) {
            return $this->redirect('/home');
        } else {
            return $this->redirect('/');
        }
    }
}