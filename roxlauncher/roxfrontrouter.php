<?php

require_once SCRIPT_BASE . 'roxlauncher/ptfrontrouter.php';
class RoxFrontRouter extends PTFrontRouter
{
    private $_roxposthandler;
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    public function route()
    {
        // set the default page title
        // this should happen before the applications can overwrite it.
        // TODO: maybe there's a better place for this.
        PVars::getObj('page')->title='BeWelcome';
        
        //--------------------------
        
        // in this place we originally had a "new RoxController()".
        // The only effect was calling the internal "RoxController::_loadDefaults()"
        // so we try to replicate this here.
        $this->load_whatever_defaults_that_were_originally_loaded_with_RoxController();
        
        //--------------------------
        
        // alternative post handling !!
        
        require_once SCRIPT_BASE.'roxlauncher/roxposthandler.php';
        $roxposthandler = new RoxPostHandler();
        $roxposthandler->load();
        $this->_roxposthandler = $roxposthandler;
        
        if (!is_array($_POST) || count($_POST)<=0 || !isset($_POST['rox_callback_id'])) {
            // no post arguments
            if (
                isset($_SESSION['PostHandler']) &&
                'PPostHandler' != get_class(unserialize($_SESSION['PostHandler']))
            ) {
                // the $_SESSION['PostHandler'] got damaged.
                // a reset can repair it.
                unset($_SESSION['PostHandler']);
            }
            // traditional posthandler
            PPostHandler::get();
            
            $classname = $this->chooseControllerClassname();
            $object = $this->createController($classname);
            $object->index();
        } else {
            if (!$callback_method = $roxposthandler->getCallbackMethod($_POST['rox_callback_id'])) {
                // form has expired
                // show a safety anchor
                $classname = $this->chooseControllerClassname();
                $methodname = 'postExpired';
            } else {
                $classname = $callback_method[0];
                $methodname = $callback_method[1];
            }
            // get rid of the global $_POST array. local is enough.
            $post_args = $_POST;
            foreach ($_POST as $key => $value) {
                unset($_POST[$key]);
            }
            $object = $this->createController($classname);
            // PPHP::PExit();
            if (method_exists($object, $methodname)) {
                $object->$methodname($post_args);
            } else {
                $object->index();
            }
            
        }
        
        $roxposthandler->save();
        
        //---------------------------
        
        // some pages need an additional output step.
        
        if (PVars::getObj('page')->output_done) {
            // output already happened, or not planned
        } else {
            // assemble the strings buffered in PVars::getObj('page')
            $aftermathController = new PDefaultController;
            $aftermathController->output();
        }
    }
    
    /**
     * create a controller and inject some data
     *
     * @param unknown_type $classname
     */
    protected function createController($classname)
    {
        $controller = new $classname();
        if (method_exists($controller, 'inject')) {
            $controller->inject('RoxPostHandler', $this->_roxposthandler);
            $controller->inject('request', $this->get('request'));
            $controller->inject('post_args', $this->get('post_args'));
            $controller->inject('get_args', $this->get('get_args'));
        }
        return $controller;
    }
    
    /**
     * replace the first part of the request by something else.
     * TODO: alias handling could be done in another way
     *
     * @param unknown_type $name
     * @return unknown
     */
    protected function translate($name)
    {
        $o = array(
            // the following requests can all be handled by the 'about' application!
            // other strings can be added!
            'theidea' => 'about',
            'thepeople' => 'about',
            'getactive' => 'about',
            'terms' => 'about',
            'bod' => 'about',
            'help' => 'about',
            'terms' => 'about',
            'impressum' => 'about',
            'affiliations' => 'about',
            'privacy' => 'about',
            'stats' => 'about'
        );
        if (array_key_exists(strtolower($name), $o)) {
            return $o[strtolower($name)];
        }
        return $name;
    }
    
    /**
     * find the name of the controller to be called,
     * given the first part of the request string
     * 
     * @param string $name first part of request
     * @return string controller classname
     */
    protected function controllerClassnameForString($name)
    {
        if (!$name) {
            return 0;
        } else if (!class_exists(
            $classname = ucfirst($name).'Controller'
        )) {
            return 0;
        } else if (!is_subclass_of($classname, 'PAppController')) {
            return 0;
        } else {
            return $classname;
        }
    }
    
    /**
     * if no controller fits the request, use a RoxController
     *
     * @return string classname of the default controller
     */
    protected function defaultControllerClassname()
    {
        return 'RoxController';
    }
    
    
}


?>