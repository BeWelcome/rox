<?php


use App\Utilities\SessionTrait;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

class RoxComponentBase
{
    use SessionTrait;

    protected $package;

    public function __construct() {
        $this->setSession();
        $this->package = new Package(new JsonManifestVersionStrategy('build/manifest.json'));
    }

    // TODO: The __get / __set mechanic is quite cool, but
    // there is no good way to make some things protected and others public!
    // Ideally, things injected from outside (with __set) would be protected,
    // while things retrieved with 'get_'.$key should be public.
    // one possibility to solve that could be to have
    // __call being public, while __get would be protected.
    // Anyway, PHP doc says that __get and __set are always public.
    private $_parameters = array();  // injected parameters
    private $_cache = array();  // cache for __get methods
    
    function __call($key, $args)
    {
        if (empty($args)) {
            if (isset($this->_cache[$key])) {
                return $this->_cache[$key];
            } else if (method_exists($this, $methodname = 'get_'.$key)) {
                return $this->_cache[$key] = $this->$methodname();
            }
        }
        return false;
    }
    
    function __get($key)
    {
        if (isset($this->_parameters[$key])) {
            return $this->_parameters[$key];
        } else if (isset($this->_cache[$key])) {
            return $this->_cache[$key];
        } else if (method_exists($this, $methodname = 'get_'.$key)) {
            return $this->_cache[$key] = $this->$methodname();
        } else {
            return false;
        }
    }
    
    

    function __set($key, $value)
    {
        if (isset($this->$key)) {
            // must be a protected attribute,
            // otherwise it would not have triggered __set
            $this->$key = $value;
        } else if (method_exists($this, $methodname = 'set_'.$key)) {
            // method is supposed to be by-reference,
            // so it can change the $value to be stored..
            if ($this->$methodname($value)) {
                // it's ok to set
                // $value can be modified by $this->$methodname(&$value)
                $this->_parameters[$key] = $value;
            }
        } else {
            $this->_parameters[$key] = $value;
        }
    }
    
    protected function get($key) {
        return isset($this->_parameters[$key]) ? $this->_parameters[$key] : false;
    }
    
    protected function getValues()
    {
        return $this->_parameters;
    }
    
    function refresh_get($key)
    {
        $methodname = 'get_'.$key;
        return $this->_cache[$key] = $this->$methodname();
    }
    
    function __toString() {
        return print_r($this, true);
    }


    /**
     * returns an instance of the MOD_words class
     *
     * @access protected
     * @return object
     */    
    protected function getWords()
    {
        if (!$this->MOD_words)
        {
            $this->MOD_words = new MOD_words;
        }
        return $this->MOD_words;
    }

    /**
     * wrapper for calls to MOD_log::get->write()
     *
     * @param string $string
     * @param string $type
     * @access protected
     */
    protected function logWrite($string, $type = 'Log')
    {
        $this->getLog()->write($string, $type);
    }

    /**
     * wrapper function for MOD_log::get()
     *
     * @access protected
     * @return object
     */
    protected function getLog()
    {
        return MOD_log::get();
    }

    /**
     * returns the revision number saved in revision.txt
     *
     * @access protected
     * @return string
     */
    protected function getVersionInfo()
    {
        $revisionFile = "../revision.txt";
        if (file_exists($revisionFile)) {
            $version = substr(file_get_contents($revisionFile), 0, 7);
        } else {
            $version = "0000000";
        }
        return $version;
    }

    protected function getUrl($path)
    {
        return $this->package->getUrl($path);
    }
}

