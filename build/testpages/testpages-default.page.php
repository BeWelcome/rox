<?php


class TestpagesDefaultPage extends PageWithActiveSkin
{
    protected function teaserHeadline()
    {
        if ($this->headline) {
            echo $this->headline;
        } else {
            echo 'no pagename found';
        }
    }
    
    
    
    protected function column_col3()
    {
        // some testing stuff...
        if (is_file(SCRIPT_BASE.'rox_local.ini')) {
            echo '
rox_local.ini exists.
'
            ;
        }
        if (is_file(SCRIPT_BASE.'inc/config.inc.php')) {
            echo '
inc/config.inc.php exists.
'
            ;
        }
    }
    
}


?>