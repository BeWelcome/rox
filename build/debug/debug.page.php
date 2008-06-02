<?php


class DebugPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return 'Debug Page - BeWelcome';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Debug Page';
    }
    
    protected function column_col3()
    {
        echo '
        <h3> $_SYSHCVOL control </h3>
        Showing some of the non-sensitive $_SYSHCVOL settings
        <ul>';
        global $_SYSHCVOL;
        foreach (array(
            'SiteName',
            'MainDir',
        ) as $key) {
            if (isset($_SYSHCVOL[$key])) {
                echo '
                <li>$_SYSHCVOL["'.$key.'"] = "'.$_SYSHCVOL[$key].'"</li>';
            } else {
                echo '
                <li>$_SYSHCVOL["'.$key.'"] not set.</li>';
            }
        }
        echo '
        </ul>';
    }
}


?>