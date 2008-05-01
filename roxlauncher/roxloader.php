<?php


class RoxLoader
{
    private $_ini_settings = 0;
    private $_rox_local_ini_found = false;
    
    public function get($section = 0, $key = 0)
    {
        if (!self::$_ini_settings) self::_load();
        if (!$section) {
            // copy everything, to be sure.
            $res = array();
            foreach (self::$_ini_settings as $sec => $contents) {
                $res[$sec] = array();
                foreach ($contents as $k => $value) {
                    $res[$sec][$k] = $value;
                }
            }
            return $res;
        }
        if (!isset (self::$_ini_settings[$section])) return 0;
        if (!$key) {
            // copy the section, to be sure.
            $res = array();
            foreach(self::$_ini_settings[$section] as $k => $value) {
                $res[$k] = $value;
            }
            return $res;
        }
        if (!isset (self::$_ini_settings[$section][$key])) return 0;
        return self::$_ini_settings[$section][$key];
    }
    

    
    public function load($files)
    {
        $ini_settings = array();
        if (!is_array($files)) {
            // do nothing, leave $ini_settings empty
        } else foreach ($files as $filename) {
            if (is_file($filename)) {
                $sections = parse_ini_file($filename, true);
                foreach ($sections as $section => $contents) {
                    if(!isset($ini_settings[$section])) {
                        $ini_settings[$section] = array();
                    }
                    foreach ($contents as $key => $value) {
                        $ini_settings[$section][$key] = $value;
                    }
                }
            }
        }
        
        return $ini_settings;
    }
    
    private static function _load_ini($filename)
    {
        if (!is_file($filename)) {
            return false;
        } else {
            $sections = parse_ini_file($filename, true);
            foreach ($sections as $section => $contents) {
                if(!isset(self::$_ini_settings[$section])) {
                    self::$_ini_settings[$section] = array();
                }
                foreach ($contents as $key => $value) {
                    self::$_ini_settings[$section][$key] = $value;
                }
            }
            return true;
        }
    }
    
    public static function create_ini_file()
    {
        // settings from ini file
        self::_load();
        
        if(self::$_rox_local_ini_found) {
            // rox_local.ini exists, don't need to create.
        } else {
            // the rox_local.ini is missing, and has to be created.
            
            // where is PVars different from the ini settings?
            $differences = array();
            foreach (array(
                'db' => 'config_rdbms',
                'smtp' => 'config_smtp',
                'mailAddresses' => 'config_mailAddresses',
                'request' => 'config_request',
                'google' => 'config_google',
                'chat' => 'config_chat',
                'env' => 'env'
            ) as $section => $objectname) {
                if(
                    ($object = PVars::getObj($objectname)) &&
                    isset(self::$_ini_settings[$section])
                ) {
                    $settings_pvars[$section] = array();
                    foreach (self::$_ini_settings[$section] as $key => $value) {
                        $settings_pvars[$section][$key] = $object->__get($key);
                        if (self::$_ini_settings[$section][$key] != $object->__get($key)) {
                            if (!isset($differences[$section])) {
                                $differences[$section] = array(); 
                            }
                            $differences[$section][$key] = $object->__get($key);
                        }
                        echo '.';
                    }
                    echo ':';
                }
                echo ';';
            }
            
            // some things are forced to be explicit.
            if (!isset($differences['db'])) $differences['db'] = array();
            if (!isset($differences['env'])) $differences['env'] = array();
            $differences['db']['dsn'] = PVars::getObj('config_rdbms')->dsn;
            $differences['db']['user'] = PVars::getObj('config_rdbms')->user;
            $differences['db']['password'] = PVars::getObj('config_rdbms')->password;
            $differences['env']['baseuri'] = PVars::getObj('env')->baseuri;
            
            // create the string to write into the file
            $res = "";
            foreach ($differences as $category => $contents) {
                $res .= "\n[$category]\n";
                foreach ($contents as $key => $value) {
                    $res .= "$key = \"$value\"\n";
                }
            }
            $filename = SCRIPT_BASE.'rox_local.ini';
            if (!$file_handle = fopen($filename, 'w')) {
                // didn't work..
                echo '<br>Rox_ini::create_ini_file() tried to create a new file "'.$filename.'", but failed to open the file handle.<br>';
                return;
            } else if (!fwrite($file_handle, $res)) {
                // didn't work..
                echo '<br>Rox_ini::create_ini_file() tried to create a new file "'.$filename.'", but failed to write to the file handle.<br>';
                return;
            } else {
                // it worked!
                echo '<br>Rox_ini::create_ini_file() has successfully created a new file "'.$filename.'", that will from now on replace your "config.inc.php".<br>';
            }
            if (!fclose($file_handle)) {
                echo 'And now failed to close the file again? Ouch!<br>';
                return;
            }
        }
    }
}


?>