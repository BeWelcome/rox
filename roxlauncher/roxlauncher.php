<?php

require_once SCRIPT_BASE.'roxlauncher/roxloader.php';
require_once 'environmentexplorer.php';

/**
 * methods in here get called by other methods in PTLauncher.
 *
 */
class RoxLauncher
{
    /**
     * central starting point.
     * to be called in htdocs/index.php
     */
    function launch()
    {
        $env_explore = $this->initializeGlobalState();
        
        try {
            // find an app and run it.
            $this->chooseAndRunApplication($env_explore);
        } catch (Exception $e) {
            $debug = true;
            if (class_exists('PVars') && !($debug = PVars::get()->debug))
            {
                $debug = false;
            }
            if (class_exists('ExceptionPage') && $debug)
            {
                $page = new ExceptionPage;
                $page->exception = $e;
                $page->render();
            }
            elseif ($debug)
            {
                echo '
                <h2>A terrible '.get_class($e).' was thrown</h2>
                <p>RoxLauncher is feeling sorry.</p>
                <pre>';
                print_r($e);
                echo '
                </pre>';
            }
            else
            {
                echo <<<HTML
<h1>Sorry :(</h1>
<p>BeWelcome has just suffered an error of some magnitude (i.e. we cannot show you the page you were looking for and something went wrong as we looked for it), which is why you are looking at this error message. We apologise for the inconvenience many times, and humbly request that you send the address of this page to us through the feedback (and hopefully that is not the page that brings up this error ...).</p>
HTML;
            }
        }
    }
    
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    protected function chooseAndRunApplication($env_explore)
    {
        $router = new RoxFrontRouter();
        $router->classes = $env_explore->classes;
        $router->env = $env_explore;
        $router->session_memory = new SessionMemory('SessionMemory');
        
        $router->route();
    }
    
    
    /**
     * This is called from
     * htdocs/bw/lib/tbinit.php
     */
    function initBW()
    {
        $this->initializeGlobalState();
    }
    
    
    protected function initializeGlobalState()
    {
        $env_explore = new EnvironmentExplorer;
        $env_explore->initializeGlobalState();
        return $env_explore;
    }
}




?>
