<?php

/**
 * This class can import local settings from the old "inc/config.inc.php",
 * and create a fresh new "rox_local.ini".
 * 
 * extra long class name to avoid name clashes with other classes that import something
 */
class RoxLocalSettingsImporter
{
    public function importConfigPHP($settings)
    {
        if (is_file(SCRIPT_BASE.'rox_local.ini')) {
            // rox_local.ini exists, don't need to create.
            // normally this should not happen when this function is called.
            echo '<pre>
    '.__CLASS__.'::'.__METHOD__.'() was called,
    but "'.SCRIPT_BASE.'rox_local.ini" already exists!
            </pre>';
                        PPHP::PExit();
        } else if (!is_file(SCRIPT_BASE.'inc/config.inc.php')) {
            // rox_local.ini exists, don't need to create.
            // normally this should not happen when this function is called.
            echo '<pre>
    '.__CLASS__.'::'.__METHOD__.'() was called,
    but "'.SCRIPT_BASE.'inc/config.inc.php" is missing
            </pre>';
            PPHP::PExit();
        } else {
            // the rox_local.ini is missing, and has to be created.
            
            // load settings from 
            require_once SCRIPT_BASE.'inc/config.inc.php';
            
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
                    isset($settings[$section])
                ) {
                    $settings_pvars[$section] = array();
                    foreach ($settings[$section] as $key => $value) {
                        $settings_pvars[$section][$key] = $object->__get($key);
                        if ($settings[$section][$key] != $object->__get($key)) {
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
                echo '<br>'.__CLASS__.'::'.__METHOD__.'() tried to create a new file "'.$filename.'", but failed to open the file handle.<br>';
                return;
            } else if (!fwrite($file_handle, $res)) {
                // didn't work..
                echo '<br>'.__CLASS__.'::'.__METHOD__.' tried to create a new file "'.$filename.'", but failed to write to the file handle.<br>';
                return;
            } else {
                // it worked!
                echo '<br>'.__CLASS__.'::'.__METHOD__.'() has successfully created a new file "'.$filename.'", that will from now on replace your "config.inc.php".<br>';
            }
            if (!fclose($file_handle)) {
                echo '<br>And now failed to close the file again? Ouch!<br>';
            }
        }
    }
}


?>