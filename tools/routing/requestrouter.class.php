<?php


class RequestRouter
{
    /**
     * where the routes are stored
     *
     * @var array
     */
    private static $_routes = array();

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (empty($this->_routes))
        {
            // general routes
            $this->addRoute('main_page','', 'RoxController', 'index');

            // group routes
            $this->addRoute('groups_overview','groups', 'GroupsController', 'index');
            $this->addRoute('show_group','groups/:group_id:', 'GroupsController', 'showGroup');
        }
    }

    /**
     * adds a temporary route to the routes
     *
     * @param string $name - name of the route
     * @param string $url - the route itself
     * @param string $controller - name of the controller
     * @param string $method - name of the method to call
     * @access public
     * @return bool
     */
    public function addRoute($name, $url, $controller, $method = 'index')
    {
        if (!is_string($name) || !empty($this->_routes[$name]) || !is_string($url) || !is_string($controller))
        {
            return false;
        }
        $this->_routes[$name] = array('url' => $url, 'controller' => $controller, 'method' => $method);
        return true;
    }


    /**
     * returns a given route if it is set
     *
     * @param string $name - name of the route to return
     * @access public
     * @return array
     */
    public function getRoute($name)
    {
        if (!is_string($name) || empty($this->_routes[$name]))
        {
            return array();
        }
        return $this->_routes[$name];
    }

    /**
     * looks for a matching route in the set routes
     *
     * @param string $uri - uri to check
     * @access public
     * @return array - empty if nothing found, otherwise full of good stuff
     */
    public function matchRoute($uri)
    {
        $match = array();
        $matchvars = array();
        foreach ($this->_routes as $route)
        {
            $url = preg_replace(array('/\//','/\*/','/:[^:]+:/','/\\?\/$/'), array('\/','.*','([^\/]+)',''),   $route['url']);
            $url = "/^{$url}\\/?$/i";
            if (!preg_match($url, $uri, $matches))
            {
                continue;
            }
            if (empty($match) || count(explode('/', $route['url'])) >= count(explode('/', $match['url'])))
            {
                $match = $route;
                $matchvars = $matches;
            }
        }

        // check if any vars were sent through the request
        if (!empty($match) && preg_match_all('/:([^:]+?):/', $match['url'], $vars, PREG_SET_ORDER))
        {
            if (count($vars) == (count($matchvars) - 1))
            {
                $i = 1;
                $match['vars'] = array();
                foreach ($vars as $varname)
                {
                    $match['vars'][$varname[1]] = $matchvars[$i];
                    $i++;
                }
            }
        }
        if (!empty($match) && empty($match['vars']))
        {
            $match['vars'] = array();
        }
        return $match;
    }

    /**
     * returns a url string, based on the routes url but with with placeholders replaced
     *
     * @param string $route - name of route to get url for
     * @param array $vars - vars to use instead of placeholders
     * @access public
     * @return string
     */
    public function url($route, $vars = array())
    {
        $route = $this->getRoute($route);
        if (empty($route))
        {
            return '';
        }
        $url = $route['url'];
        $placeholders = preg_match_all('/:([^:]+?):/', $url, $matches, PREG_SET_ORDER);
        if ($placeholders)
        {
            $keys = array_keys($vars);
            foreach ($matches as $match)
            {
                if (in_array($match[1], $keys))
                {
                    $url = str_replace($match[0], $vars[$match[1]], $url);
                }
                $placeholders--;
            }
            if ($placeholders != 0)
            {
                return '';
            }
        }
        return PVars::getObj('env')->baseuri . $url;
    }



    
    /**
     * checks to see if a route matches the incoming request
     * checks through the _routes array, to see if something matches
     *
     * @access public
     * @param array $request
     * @return array
     */
    public function findRoute($request)
    {
        $route = $this->matchRoute(implode('/',$request));
        if (!empty($route))
        {
            return array($route['controller'], $route['method'], $route['vars']);
        }
        else
        {
            return $this->controllerClassnameForString(isset($request[0]) ? $request[0] : '');
        }
    }

    /**
     * find the name of the controller to be called,
     * given the first part of the request string
     * 
     * @param string $name first part of request
     * @return string controller classname
     */
    public function controllerClassnameForString($name)
    {
        $controller = $this->translate($name);
        if ($controller) {
            $classname = ucfirst($controller).'Controller';
            if (class_exists($classname) && (
                is_subclass_of($classname, 'PAppController') ||
                is_subclass_of($classname, 'RoxControllerBase')
            )) {
                return array($classname, 'index', null);
            }
        }
        return array($this->defaultControllerClassname(), 'index', null);
    }
    
    
    /**
     * replace the first part of the request by something else.
     *
     * @param unknown_type $name
     * @return unknown
     */
    protected function translate($name)
    {
        if (!$name) return false;
        
        $key = strtolower($name);
        
        $ini_alias_table = $this->loadRoutingAliasTable();
        
        if (array_key_exists($key, $ini_alias_table)) {
            return $ini_alias_table[$key];
        } else {
            return $key;
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
    
    
    protected function loadRoutingAliasTable()
    {
        $alias_table = array();
		// Added check for PVars::getObj('syshcvol')->IniCache to be able to switch on/off caching for the alias.ini files
        $force_refresh = ('localhost' == $_SERVER['SERVER_NAME'] || PVars::getObj('syshcvol')->IniCache == 0);
        if (is_file($cachefile = SCRIPT_BASE.'build/alias.cache.ini') && !$force_refresh) {
            $this->iniParse($cachefile, $alias_table);
        } else {
            foreach (scandir(SCRIPT_BASE.'build') as $subdir) {
                $dir = SCRIPT_BASE.'build/'.$subdir;
                if (!is_dir($dir)) {
                    // echo ' - not a dir';
                } else if (!is_file($filename = $dir.'/alias.ini')) {
                    // echo ' - no alias.ini in '.$dir;
                } else {
                    $this->iniParse($filename, $alias_table);
                }
            }
            $this->iniWrite($cachefile, $alias_table);
        }
        return $alias_table;
    }
    
    protected function iniParse($file, &$alias_table)
    {
        if (!is_array($ini_settings = parse_ini_file($file))) {
            return false;
        } else foreach ($ini_settings as $key => $value) {
            $aliases = split("[,\n\r\t ]+", $value);
            foreach ($aliases as $alias) {
                $alias_table[$alias] = $key;
            }
        }
        return true;
    }
    
    protected function iniWrite($file, $alias_table)
    { 
        $rwd_table = array();
        foreach ($alias_table as $k => $v) {
            $rwd_table[$v][] = $k;
        }
        $str = '';
        foreach ($rwd_table as $x => $y) {
            $str .= "$x = ".implode(' ', $y)."\n";
        }
        file_put_contents($file, $str);
    }

    /**
     * get the controller classname
     *
     * @return classname of the controller that should be run
     */
/* seemingly not used anymore - cut off, pending deletion

    public function chooseControllerClassnameAndMethodname($request)
    {
        if (!isset($request[0])) {
            $classname = $this->controllerClassnameForString(false);
            $methodname = 'index';
        } else switch($request[0]) {
            case 'ajax':
            case 'json':
            case 'xml':
                $classname = $this->controllerClassnameForString(isset($request[1]) ? $request[1] : false);
                $methodname = $request[0];
                break;
            default:
                $classname = $this->controllerClassnameForString($request[0]);
                $methodname = 'index';
        }
        
        return array($classname, $methodname);
    }
*/    

}


?>
