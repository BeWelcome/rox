<?php

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
    function launch(EnvironmentExplorer $env_explore)
    {
        try {
            // find an app and run it.
            $this->chooseAndRunApplication($env_explore);
        } catch (Exception $e) {
            ExceptionLogger::logException($e);
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
        $router = new \RoxFrontRouter();
        // $router->classes = $env_explore->classes;
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

class ExceptionLogger
{
    public static function logException($e)
    {
        if ($handle = fopen('exception.log', 'at'))
        {
            $string = "Exception occurred at " . date('Y-m-d H:i:s') . ". Here are the details:". PHP_EOL;

			if (isset($_SESSION["IdMember"])) {
				$string .= "Session data: IdMember=" . $_SESSION["IdMember"] ;
				if (isset($_SESSION["Username"])) {
					$string .= " Username=" . $_SESSION["Username"] ;
				}
				if (isset($_SESSION["MemberStatus"])) {
					$string .= " MemberStatus=" . $_SESSION["MemberStatus"] ;
				}
				$string .=	PHP_EOL;
			}
			else {
				$string .=	"No session data for log members".PHP_EOL;
			}
            $string .= "* Request url: {$_SERVER['REQUEST_URI']}" . PHP_EOL;
            $string .= "* Exception message: {$e->getMessage()}" . PHP_EOL;
            $string .= "* Exception class: " . get_class($e) . PHP_EOL;
            $string .= "* Exception code: {$e->getCode()}" . PHP_EOL;
            $string .= "* Exception line: {$e->getLine()}" . PHP_EOL;
            $string .= "* Exception file: {$e->getFile()}" . PHP_EOL;

            if (method_exists($e, 'getInfo'))
            {
                $string .= "* Exception info:" . PHP_EOL;
                foreach ($e->getInfo() as $i => $inf)
                {
                    $string .= "  * info[{$i}]: {$inf}" . PHP_EOL;
                }
            }
            if (method_exists($e, 'getTrace'))
            {
                $string .= "* Exception trace:" . PHP_EOL;
                foreach ($e->getTrace() as $i => $step)
                {
                    $string .= "  * Step {$i}:" . PHP_EOL;
                    foreach ($step as $key => $value)
                    {
                        if (is_array($value))
                        {
                            if (is_object($key))
                            {
                                $key = get_class($key);
                            }
                            $string .= "    * {$key} = " . self::getArgsVal($value) . PHP_EOL;
                        }
                        else
                        {
                            $string .= "    * {$key} = {$value}" . PHP_EOL;
                        }
                    }
                }
            }
            $string .= PHP_EOL;
            fwrite($handle, $string);
            fclose($handle);
        }
    }

    private static function getArgsVal($args)
    {
        $store = array();
        foreach ($args as $item)
        {
            if (is_scalar($item))
            {
                $store[] = $item;
            }
            elseif (is_object($item))
            {
                $store[] = get_class($item);
            }
            elseif (is_array($item))
            {
                $store[] = "array" . self::getArgsVal($item);
            }
            else
            {
                $store[] = gettype($item);
            }
        }
        return "(" . implode(' - ', $store) . ")";
    }
}
