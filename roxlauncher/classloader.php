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
        if (!is_dir($path)) return false;
        
        $settings = array();
        // $force_refresh = ('localhost' == $_SERVER['SERVER_NAME']);
        // sorry, the caching doesn't work that well. so we disable it for now.
        $force_refresh = true;
        if (!is_file($cachefile = $path.'/autoload.cache.ini') || $force_refresh) {
            $this->recursiveIniParsing($settings, $path, '', $subdir_level);
            $this->createIniFile($cachefile, $settings);
        } else {
            $this->iniParsing($settings, $cachefile, '');
        }
        
        // add classes found in $settings
        foreach ($settings as $k => $v) {
            foreach ($v as $kk => $vv) {
                $file = $path.(empty($k) ? '/' : "/$k/").$kk;
                foreach ($vv as $classname) {
                    $this->addClass($classname, $file);
                }
            }
        }
        
        return true;
    }
    
    protected function recursiveIniParsing(&$settings, $start_path, $rel_path, $subdir_level)
    {
        $path = empty($rel_path) ? $start_path : $start_path.'/'.$rel_path;
        $filename = $path.'/autoload.ini';
        
        if (is_file($filename)) {
            $scan_subdirectories = $this->iniParsing($settings, $filename, $rel_path);
            if (is_array($scan_subdirectories)) {
                foreach ($scan_subdirectories as $rel_scan_path => $depth) {
                    $this->recursiveIniParsing($settings, $start_path, $rel_scan_path, $depth);
                }
            }
        }
        
        if ($subdir_level > 0) {
            foreach (scandir($path) as $subdir) {
                if (is_dir($subdir_path = $path.'/'.$subdir) && '.' != $subdir && '..' != $subdir) {
                    $this->recursiveIniParsing(
                        $settings,
                        $start_path,
                        empty($rel_path) ? $subdir : ($rel_path.'/'.$subdir),
                        $subdir_level-1
                    );
                }
            }
        }
    }
    
    protected function iniParsing(&$settings, $filename, $rel_path) {
        if (!empty($rel_path)) $rel_path .= '/';
        if (is_file($filename)) {
            $scan_subdirectories = array();
            foreach (parse_ini_file($filename, true) as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $kk => $vv) {
                        if (is_numeric($vv)) {
                            // this is a command to scan subdirectories for more ini files,
                            // $vv denotes the search depth
                            $scan_subdirectories[$rel_path.$k.'/'.$kk] = $vv;
                        } else foreach (preg_split("/[,\n\r\t ]+/", $vv) as $classname) {
                            @$settings[$rel_path.$k][$kk][] = $classname;
                        }
                    }
                } else {
                    if (is_numeric($v)) {
                        // this is a command to scan subdirectories for more ini files,
                        // $vv denotes the search depth
                        $scan_subdirectories[$rel_path.$k] = $v;
                    } else foreach (preg_split("/[,\n\r\t ]+/", $v) as $classname) {
                        @$settings[$rel_path][$k][] = $classname;
                    }
                }
            }
            return $scan_subdirectories;
        } else {
            return false;
        }
    }
    
    protected function createIniFile($filename, $settings) {
        $str = '';
        if (isset($settings[''])) {
            foreach ($settings[''] as $kk => $vv) {
                $str .= "$kk = ".implode(' ', $vv)."\n";
            }
        }
        foreach ($settings as $k => $v) {
            if ('' == $k) continue;
            $str.= "\n[$k]\n";
            foreach ($v as $kk => $vv) {
                $str .= "$kk = ".implode(' ', $vv)."\n";
            }
        }
        file_put_contents($filename, $str);
    }
    
    function addClassesFromIniSettings($ini_settings, $path_prefix = false)
    {
        if (!empty($path_prefix)) {
            $path_prefix.='/';
        }
        foreach ($ini_settings as $k => $v) {
            if (!is_array($v)) {
                $this->addClassesFromIniRow( $path_prefix.$k , $v);
            } else foreach ($v as $kk => $vv) {
                $this->addClassesFromIniRow( $path_prefix.$k.'/'.$kk , $vv);
            }
        }
    }
    
    protected function addClassesFromIniRow($file, $string_with_classnames)
    {
        foreach (preg_split("/[,\n\r\t ]+/", $string_with_classnames) as $classname) {
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
            $file = $this->_files_by_classname[$classname];
            if ($this->requireFileAbsolute($file, $classname)) {
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
                if ($this->requireFile('build/'. $camel_explode[0] . '/' . $camel_explode[1] .'/'. $subdir .
                    implode('', $camel_explode) . '.' . $suffix, $classname)) {
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
            if (!is_string($classname)) {
                return true;
            } else if (class_exists($classname) || interface_exists($classname)) {
                self::$_where_is_class[$classname] = $abs_path;
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    
    
    function showClasses()
    {
        echo '<pre>kirschkernspucken<br>'; print_r($this->_files_by_classname); echo '</pre>';
    }
    
    private static $_where_is_class = array();
    function whereIsClass($classname) {
        if (!class_exists($classname)) {
            return false;
        } else if (!isset(self::$_where_is_class[$classname])) {
            return false;
        } else {
            return self::$_where_is_class[$classname];
        }
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
