<?php


class ExtensionsController extends RoxControllerBase
{
    public function index($args = false)
    {
        $page = new ExtensionsPage();
        return $page;
    }
    
    
    
    public function extensionsManagerCallback($args, $action, $mem_for_redirect)
    {
        if (!isset($args->post['extensions'])) {
            $_SESSION['extension_folders'] = '';
        } else if (!is_array($args->post['extensions'])) {
            // do nothing
        } else {
            $_SESSION['extension_folders'] = implode(' ', $args->post['extensions']);
        }
    }
}



?>