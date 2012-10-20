<?php

/**
 * This class can import local settings from the old "inc/config.inc.php",
 * and create a fresh new "rox_local.ini".
 * 
 * extra long class name to avoid name clashes with other classes that import something
 */
class RoxLocalSettingsImporter
{
    public function importConfigPHP($default_settings)
    {
        if (is_file(SCRIPT_BASE.'rox_local.ini')) {
            
            // rox_local.ini exists, don't need to create.
            // normally this should not happen when this function is called.
            echo '
<pre>
'.__METHOD__.'() was called,
but "'.SCRIPT_BASE.'rox_local.ini" already exists!
</pre>
'
            ;
            PPHP::PExit();
            
        } else if (!is_file(SCRIPT_BASE.'inc/config.inc.php')) {
            
            // rox_local.ini exists, don't need to create.
            // normally this should not happen when this function is called.
            echo '
<pre>
'.__METHOD__.'() was called,
but "'.SCRIPT_BASE.'inc/config.inc.php" is missing
</pre>
'
            ;
            PPHP::PExit();
            
        } else {
            
            // the rox_local.ini is missing, and has to be created.
            
            // load settings from config.inc.php
            require_once SCRIPT_BASE.'inc/config.inc.php';
            
            // where is PVars different from the ini settings?
            $differences = array();
            foreach (array(
                'db' => 'config_rdbms',
                'db' => 'db',
                'smtp' => 'config_smtp',
                'mailAddresses' => 'config_mailAddresses',
                'request' => 'config_request',
                'google' => 'config_google',
                'env' => 'env'
            ) as $sectionname => $objectname) {
                $differences[$sectionname] = array();
                if (!$object = PVars::getObj($objectname)) {
                    // ehm.. no idea
                } else if (!isset($default_settings[$sectionname])) {
                    // ehm.. no idea.
                } else foreach ($default_settings[$sectionname] as $key => $value) {
                    if (!$object->$key) {
                        // do nothing
                    } else if ($default_settings[$sectionname][$key] != $object->$key) {
                        $differences[$sectionname][$key] = $object->$key;
                    }
                    echo '.';
                }
                echo ';';
            }
            
            // where is $_SYSHCVOL different from $default_settings['syshcvol'] ?
            if(!isset($default_settings['syshcvol'])) {
                if (!empty($_SYSHCVOL)) {
                    echo '<br>creating a full syshcvolbullshit<br>';
                    $differences['syshcvol'] = $_SYSHCVOL;
                } else {
                    echo '<br>syshcvol empity<br>';
                }
            } else {
                echo '<br>syshcvol differences extraction<br>';
                $default_settings_syshcvol = $default_settings['syshcvol'];
                $differences['syshcvol'] = array();
                foreach ($_SYSHCVOL as $key => $value) {
                    if (!isset($default_settings_syshcvol[$key]) || $default_settings_syshcvol[$key] != $_SYSHCVOL[$key]) {
                        // setting needs to go into $differences
                        $differences['syshcvol'][$key] = $value;
                    }
                    echo '+';
                }
            }
            
            // some things are forced to be implicit (not imported)
            unset($differences['syshcvol']['MYSQLUsername']);
            unset($differences['syshcvol']['MYSQLPassword']);
            unset($differences['syshcvol']['MYSQLDB']);
            unset($differences['syshcvol']['SiteName']);
            unset($differences['syshcvol']['MainDir']);
            unset($differences['syshcvol']['WWWIMAGEDIR']);
            
            // some things are forced to be explicit (imported even if the same as default).
            if (!isset($differences['db'])) $differences['db'] = array();
            if (!isset($differences['env'])) $differences['env'] = array();
            $differences['db']['dsn'] = PVars::getObj('config_rdbms')->dsn;
            $differences['db']['user'] = PVars::getObj('config_rdbms')->user;
            $differences['db']['password'] = PVars::getObj('config_rdbms')->password;
            $differences['env']['baseuri'] = PVars::getObj('env')->baseuri;
            
            // create the string to write into the ini file
            $res = "";
            foreach ($differences as $sectionname => $sectioncontents) {
                if (empty($sectioncontents)) {
                    // nothing to do.
                } else {
                    $res .= "\n[$sectionname]\n";
                    if (isset($default_settings[$sectionname])) {
                        $default_settings_section = $default_settings[$sectionname]; 
                    } else {
                        $default_settings_section = array();
                    }
                    foreach ($sectioncontents as $key => $value) {
                        if (!is_array($value)) {
                            $res .= "$key = \"$value\"\n";
                        } else {
                            // ini notation does only work with numeric keys, unfortunately..
                            $res .= "\n";
                            for ($i=0; $i<count($value); ++$i) {
                                if (isset($value[$i])) {
                                    $res .= $key."[] = \"".$value[$i]."\"\n";
                                } else {
                                    $res .= $key."[] = \"\"\n";
                                }
                            }
                            $res .= "\n"; 
                        }
                    }
                }
            }
            $filename = SCRIPT_BASE.'rox_local.ini';
            $this->writeSettingsToFile($filename, $res);
        }
    }
    
    
    
    protected function writeSettingsToFile($filename, $res)
    {
        if (!$file_handle = fopen($filename, 'w')) {
            // didn't work..
            echo '
<br>'.__METHOD__.'() tried to create a new file "'.$filename.'",
but failed to open the file handle.<br>
'
            ;
            return;
        } else if (!fwrite($file_handle, $res)) {
            // didn't work..
            echo '
<br>'.__METHOD__.' tried to create a new file "'.$filename.'",
but failed to write to the file handle.<br>
'
            ;
            return;
        } else {
            // it worked!
            echo '
<p>
'.__METHOD__.'()<br>
has successfully created a new file "'.$filename.'",<br>
that will from now on replace your "config.inc.php".
</p>
<p>
<strong>Reload the page</strong> to get rid of this message!
</p>
<br>
<p>
If the message pops up again and again (for instance, on test.bewelcome.org),<br>
it means that something has wiped the newly created file.<br>
(usually this happens when new php code is uploaded by a developer)
</p>
<p>
The sysadmins should create a script to restore the file.<br>
(they did the same for the "config.inc.php")
</p>
<p>
See <a href="http://www.bevolunteer.org/trac/ticket/481">Trac Ticket #481: server script for ini files</a>
</p>
'
            ;
        }
        if (!fclose($file_handle)) {
            echo '
<br>And now failed to close the file again? Ouch!<br>
'
            ;
        }
    }
}

