<?php

/**
 * This class is a decomposition of the old htdocs/index.php.
 * It tries to do exactly what PT used to do in index.php
 * and in the files inside the /inc/ directory.
 * 
 * Starting point is "launch()"
 * 
 * The class can be extended for customization purposes.
 *
 */
abstract class PTLauncher
{
    private $_base_xml = 0;
    
    /**
     * central starting point.
     * to be called in htdocs/index.php
     */
    public function launch()
    {
        // load data in base.xml
        $this->loadBaseXML();
        
        // load essential framework libraries
        $this->loadFramework();
        
        $this->simulateMissingFunctions();
        
        $S = PSurveillance::get();
        
        $this->checkEnvironment();
        $this->loadConfiguration();
        $this->loadDefaults();
        
        PSurveillance::setPoint('base_loaded');
        
        $this->initSession();
        $this->initAutoload();
        
        PSurveillance::setPoint('loading_apps');
        
        $this->doPostHandling();
        
        // find an app and run it.
        $this->chooseAndRunApplication();
    }
    
    /**
     * some native PHP functions could be missing, if they require a newer PHP version.
     *
     */
    protected function simulateMissingFunctions()
    {
        foreach (scandir($maindir) as $file) {
            if (is_file($file)) {
                $functionname = basename($file, '.php');
                if (!function_exists($functionname)) {
                    require_once $file;
                }
            }
        }
    }
    
    
    /**
     * not sure what exactly the base.xml does
     * but PT needs it.
     *
     */
    protected function loadBaseXML()
    {
        require_once SCRIPT_BASE.'inc/base.inc.php';
        $this->_base_xml = $B;
    }
    
    /**
     * this will indirectly load all necessary framework files.
     *
     */
    protected function loadFramework()
    {
        require_once SCRIPT_BASE.'lib/libs.php';
    }
    
    /**
     * check something.
     *
     */
    protected function checkEnvironment()
    {
        // example call of requiring extension "xsl"
        //if (!PPHP::assertExtension('xsl')) 
        //    die('XSL required!');
    }
    
    
    /**
     * load configuration from inc/config.inc.php,
     * which also sets a lot of global symbols.
     *
     */
    protected function loadConfiguration()
    {
        require_once SCRIPT_BASE.'inc/config.inc.php';
    }
    
    /**
     * again, PT needs it.
     * 
     */
    protected function loadDefaults()
    {
        $B = $this->_base_xml;
        
        // copied from defaults.inc.php
        
        // we don't need PPckup() and translate($request) anymore,
        // we have chooseControllerClassname() instead.
        
        // suspended
        $susp = $B->x->query('/basedata/suspended');
        if ($susp->length > 0) {
            $env = PVars::getObj('env');
            if ($env->suspend_url) {
                header('Location: '.$env->suspend_url);
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
            PPHP::PExit();
        }
        
        // debug?
        $debug = $B->x->query('/basedata/debug');
        if ($debug->length > 0) {
            PVars::register('debug', true);
            $build = str_replace(SCRIPT_BASE, '', BUILD_DIR);
            PVars::register('build', substr($build, 0, strlen($build) - 1));
        }
    }
    
    /**
     * do some things which are necessary to start
     * using the $_SESSION
     *
     */
    protected function initSession()
    {
        if (defined ('SESSION_NAME')) {
            ini_set ('session.name', SESSION_NAME);
        }
        ini_set ('session.use_trans_sid', 0);
        ini_set ('url_rewrite.tags', '');
        ini_set ('session.hash_bits_per_character', 6);
        ini_set ('session.hash_function', 1);
        session_start();
        if (empty ($_COOKIE[session_name ()]) ) {
            PVars::register('cookiesAccepted', false);
        } else {
            PVars::register('cookiesAccepted', true);
        }
        PVars::register('queries', 0);
        
        $this->fillSessionWithValues();
    }
    
    /**
     * some fields in the $_SESSION need to be filled with default values, if they are empty.
     *
     */
    protected function fillSessionWithValues()
    {
        // by default, do nothing.
        // RoxLauncher will override this method.
    }
    
    /**
     * register classnames for autoload.
     * The associated filenames are stored in the build.xml and module.xml files.
     *
     */
    protected function initAutoload()
    {
        PSurveillance::setPoint('loading_modules');
        // load modules
        $Mod = PModules::get();
        $Mod->setModuleDir(SCRIPT_BASE.'modules');
        $Mod->loadModules();
        PSurveillance::setPoint('modules_loaded');
        
        $Apps = PApps::get();
        $Apps->build();
        // process includes
        $includes = $Apps->getIncludes();
        if ($includes) {
            foreach ($includes as $inc) {
                require_once $inc;
            }
        }
        PSurveillance::setPoint('apps_loaded');
    }
    
    /**
     * call the PT posthandler, which does something with $_POST requests.
     *
     */
    protected function doPostHandling()
    {
        // this line is enough to start the posthandler action.
        PPostHandler::get();
    }
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    protected function chooseAndRunApplication()
    {
        require_once SCRIPT_BASE . 'roxlauncher/ptfrontrouter.php';
        $router = new PTFrontRouter();
        $router->inject('request', PRequest::get()->request);
        $router->inject('post_args', $_POST);
        $router->inject('get_args', $_GET);
        $router->route(); 
    }
    
    
    
        
    
    /**
     * die if something in the env is not ok
     */
    protected function checkEnv()
    {
        // by default, check nothing
        
        /*
         * example implementation could be
        
        //BW Rox needs the GD plugin
        if (!PPHP::assertExtension('gd')) {
            die('GD lib required!');
        }
        
         */
    }
}

?>