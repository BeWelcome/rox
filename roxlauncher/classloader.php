<?php


/**
 * This thing can be plugged into the AutoloadPlug, by
 * AutoloadPlug::setCallback(array(new ClassLoader, 'autoload'));
 */
class ClassLoader
{
    private $_files_by_classname = array();
    
    function addClassesForAutoloadInisInFolder($path, $subdir_level = 1)
    {
        if (!is_dir($path)) return;
        
        $this->addClassesFromIniFile($path.'/autoload.ini', $path);
        
        if ($subdir_level > 0) {
            foreach (scandir($path) as $subdir) {
                $dir = $path.'/'.$subdir;
                if (!is_dir($dir)) {
                    // echo ' - not a dir';
                } else if ('.' == $dir || '..' == $dir) {
                    // do nothing
                } else {
                    $this->addClassesForAutoloadInisInFolder($dir, $subdir_level-1);
                }
            }
        }
    }
    
    function addClassesFromIniFile($filename, $path_prefix = false)
    {
        if (is_file($filename)) {
            $this->addClassesFromIniSettings(
                parse_ini_file($filename, true),
                $path_prefix ? $path_prefix : dirname($filename)
            );
        }
    }
    
    function addClassesFromIniSettings($ini_settings, $path_prefix)
    {
        foreach ($ini_settings as $k => $v) {
            if (!is_array($v)) {
                $this->addClassesFromIniRow( "$path_prefix/$k" , $v);
            } else foreach ($v as $kk => $vv) {
                $this->addClassesFromIniRow( "$path_prefix/$k/$kk" , $vv);
            }
        }
    }
    
    protected function addClassesFromIniRow($file, $string_with_classnames)
    {
        foreach (split("[,\n\r\t ]+", $string_with_classnames) as $classname) {
            $this->addClass($classname, $file);
        }
    }
    
    function addClass($classname, $file)
    {
        if (!array_key_exists($classname, $this->_files_by_classname)) {
            $this->_files_by_classname[$classname] = $file;
        }
    }
    
    
    
    function autoload($classname)
    {
        if (isset($this->_files_by_classname[$classname])) {
            if ($this->requireFileAbsolute($this->_files_by_classname[$classname], $classname)) {
                return true;
            }
        }
        // still not found. try other tricks..
        $camel_explode = $this->camelCaseExplode($classname);
        if (count($camel_explode) < 2) {
            // boring, can't do anything.
            return false;
        }
        $begin = $camel_explode[0];
        if (!is_dir(SCRIPT_BASE.'build/'.$begin)) {
            return false;
        }
        switch($end = end($camel_explode)) {
            case 'controller':
                $suffix = 'ctrl.php';
                $try_subdirs = array();
                array_pop($camel_explode);
                break;
            case 'page':
            case 'widget':
            case 'model':
                $suffix = $end.'.php';
                $try_subdirs = array($end.'s', $end);
                array_pop($camel_explode);
                break;
            default:
                $suffix = 'entity.php';
                $try_subdirs = array('model', 'models', 'entities');
        }
        $try_subdirs[] = '';
        foreach ($try_subdirs as $subdir) {
            if (!empty($subdir)) $subdir .= '/';
            if (count($camel_explode) > 1) {
                if ($this->requireFile('build/'.$begin.'/'.$subdir.implode('', array_slice($camel_explode, 1)).'.'.$suffix, $classname)) {
                    return true;
                }
            } else if ($this->requireFile('build/'.$begin.'/'.$subdir.$suffix, $classname)) {
                return true;
            }
            if ($this->requireFile('build/'.$begin.'/'.$subdir.implode('', $camel_explode).'.'.$suffix, $classname)) {
                return true;
            }
        }
        return false;
    }
    
    
    protected function requireFile($rel_path, $classname = false)
    {
        return $this->requireFileAbsolute(SCRIPT_BASE.$rel_path, $classname);
    }
    
    protected function requireFileAbsolute($abs_path, $classname = false)
    {
        if (is_file($abs_path)) {
            require_once $abs_path;
            return is_string($classname) ? class_exists($classname) : true;
        }
        return false;
    }
    
    
    function showClasses()
    {
        echo '<pre>kirschkernspucken<br>'; print_r($this->_files_by_classname); echo '</pre>';
    }
    
    
    /**
     * thanks a lot Charl van Niekerk, http://blog.charlvn.za.net/2007/11/php-camelcase-explode-20.html
     *
     * @param string $name
     * @param boolean $lowercase
     * @return array of string segments
     */
    function camelCaseExplode($string, $lowercase = true, $example_string = 'AA Bc', $glue = false)
    {
        static $regexp_available = array(
            '/([A-Z][^A-Z]*)/',
            '/([A-Z][^A-Z]+)/',
            '/([A-Z]+[^A-Z]*)/',
        );
        static $regexp_by_example = array();
        if (!isset($regexp_by_example[$example_string])) {
            $example_array = explode(' ', $example_string);
            foreach ($regexp_available as $regexp) {
                if (implode(' ', preg_split(
                    $regexp,
                    str_replace(' ', '', $example_string),
                    -1,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                )) == $example_string) {
                    break;
                }
            }
            $regexp_by_example[$example_string] = $regexp;
        }
        $array = preg_split(
            $regexp_by_example[$example_string],
            $string,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        if ($lowercase) $array = array_map('strtolower', $array);
        return is_string($glue) ? implode($glue, $array) : $array;
    }
}


?>