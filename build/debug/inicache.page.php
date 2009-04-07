<?php


class DebugInicachePage extends DebugPage
{
    function column_col3()
    {
        foreach (array(
            'build/autoload.cache.ini',
            'build/alias.cache.ini',
        ) as $filename) {
            echo '<h3>'.$filename.'</h3>';
            $filename = SCRIPT_BASE.$filename;
            if (is_file($filename)) {
                echo '<pre>';
                echo file_get_contents($filename);
                echo '</pre>';
            } else {
                echo 'does not exist';
            }
        }
    }
}


?>