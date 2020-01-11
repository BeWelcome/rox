<?php

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Router;

/**
 * Class RoxControllerBase
 */
abstract class RoxControllerBase extends RoxComponentBase
{
    /** @var  FormFactoryInterface */
    protected $formFactory;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     *
     * @return RoxControllerBase
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return Router
     */
    protected function getRouter() {
        return $this->router;
    }

    /**
     * @param FormFactoryInterface $formFactory
     * @return RoxControllerBase
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    protected function redirect($request, $get_args = '') {
        $relative_url = is_array($request) ? implode('/', $request) : $request;
        $this->redirectAbsolute(
            PVars::getObj('env')->baseuri . $relative_url,
            $get_args
        );
    }

    protected function redirectHome($get_args = '') {
        $this->redirect('index', $get_args);
    }

    protected function redirectRefresh() {
        $this->redirect(implode('/',$this->get('request')));
    }

    protected function redirectAbsolute($url, $get_args = '') {
        if (!empty($get_args)) {
            if (!is_array($get_args)) {
                $url .= '?'.$get_args;
            } else {
                $url .= '?'.http_build_query($get_args);
            }
        }
        header('Location: ' . $url);
        PVars::getObj('page')->output_done = true;
    }

    protected function setTitle($title) {
        PVars::getObj('page')->title = $title . " - BeWelcome.org";
    }

    protected function setTitleTranslate($title) {
        $words = new MOD_words;
        $this->setTitle($words->getBuffered($title));
    }

    /**
     * trims all values posted back to controller
     *
     * @param array $post_vars
     * @access private
     * @return array
     */
    protected function cleanVars($post_vars)
    {
        $vars = array();
        foreach ($post_vars as $key => $var)
        {
            if (is_string($var))
            {
                $var = trim($var);
            }
            $vars[$key] = $var;
        }
        return $vars;
    }

    /**
     * redirects to a login screen, returning the user to where it was afterwards
     *
     * @param string $url_part - relative url
     * @access protected
     */
    protected function redirectToLogin($url_part)
    {
        $this->redirectAbsolute($this->router->url('login_helper', array('url' => $url_part)));
        PPHP::PExit();
    }

    /**
     * Set flash message
     *
     * @param string $message Message text for flash
     * @param string $type Type of flash, i.e. "error" or "notice"
     */
    private function setFlash($message, $type) {
        $flashName = 'flash_' . $type;
        $this->session->set( $flashName, $message );
    }

    /**
     * Set flash notice message
     * @see PageWithRoxLayout::getFlashNotice() for counterpart
     *
     * @param string $message Message text for flash
     */
    public function setFlashNotice($message) {
        $this->setFlash($message, 'notice');
    }

    /**
     * Set flash error message
     * @see PageWithRoxLayout::getFlashError() for counterpart
     *
     * @param string $message Message text for flash
     */
    public function setFlashError($message) {
        $this->setFlash($message, 'error');
    }
}
