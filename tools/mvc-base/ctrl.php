<?php


abstract class RoxControllerBase extends RoxComponentBase
{
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
}


?>