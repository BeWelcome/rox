<?php


class ExtensionsController extends RoxControllerBase
{
    public function index($args = false)
    {
        $page = new ExtensionsPage();
        $request = $args->request;
        if (!isset($request[1])) {
            // nothing happens
        } else if (empty($request[1])) {
            // nothing happens
        } else if(!is_dir(SCRIPT_BASE.'extensions/'.$request[1])) {
            echo $request[1];
            // nothing happens
        } else {
            if (!isset($_SESSION['extension_folders'])) {
                $_SESSION['extension_folders'] = '';
            }
            $active_ext_folders = split("[,\n\r\t ]+", $_SESSION['extension_folders']);
            $extfolder = $request[1];
            switch (isset($request[2]) ? $request[2] : '') {
                case 'off':
                case 'disable':
                    if ($key = array_search($extfolder, $active_ext_folders)) {
                        unset($active_ext_folders[$key]);
                    }
                    break;
                case 'on':
                default:
                    if (!in_array($extfolder, $active_ext_folders)) {
                        $active_ext_folders[] = $extfolder;
                    }
            }
            $_SESSION['extension_folders'] = implode(' ', $active_ext_folders);
            if (isset($request[3])) {
                $this->redirect(implode('/', array_slice($request, 3)));
                PPHP::PExit();
            } else {
                $this->redirect('extensions');
                PPHP::PExit();
            }
        }
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