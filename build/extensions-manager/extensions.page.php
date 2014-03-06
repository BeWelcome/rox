<?php


class ExtensionsPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        return 'Extensions Manager';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Extensions Manager';
    }
    
    protected function leftSidebar()
    {
        // can be empty
    }
    
    protected function column_col3()
    {
        $maindir = SCRIPT_BASE.'extensions';
        
        $available_ext_folders = array();
        foreach (scandir($maindir) as $subdir) {
            if (!is_dir($dir = $maindir.'/'.$subdir)) {
                // nothing
            } else if ('..' == $subdir || '.' == $subdir || '.svn' == $subdir) {
                // nothing
            } else {
                $available_ext_folders[$subdir] = false;
            }
        }
        
        if (!isset($_SESSION['extension_folders'])) {
            $_SESSION['extension_folders'] = '';
        }
        $active_ext_folders = preg_split("/[,\n\r\t ]+/", $_SESSION['extension_folders']);
        foreach ($active_ext_folders as $key) {
            if (isset($available_ext_folders[$key])) {
                $available_ext_folders[$key] = true;
            }
        }
        
        echo '
        <h3>Choose your extensions</h3>
        The active extensions are stored in $_SESSION["extension_folders"].<br>
        Currently the value is: "'.$_SESSION['extension_folders'].'"<br>
        <br>
        You can create extensions in the /extensions/ folder...<br>
        They work by replacing classes in the __autoload.<br>
        <br>
        <br>
        <form action="extensions" method="post">
        <p>';
        $formkit = $this->layoutkit->formkit;
        echo $formkit->setPostCallback('ExtensionsController', 'extensionsManagerCallback');
        
        foreach ($available_ext_folders as $key => $value) {
            echo '
            <input type="checkbox" name="extensions[]" value="'.$key.'"'.($value ? ' checked' : '').'/> '.$maindir.'/<strong>'.$key.'</strong><br>';
        }
        
        echo '
        </p>
        <br>    
        <input type="submit" class="button" value="Ok"/><br>
        </form>';
    }
}


?>
