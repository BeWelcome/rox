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
    
    
    /*
    protected function column_col3()
    {
        // some testing stuff...
        
        echo '
<pre>

<strong>SERVER</strong>
';
        print_r($_SERVER);
        echo '


<strong>REQUEST</strong>
';
        print_r($_REQUEST);
        echo '


<strong>ENV</strong>
';
        print_r($_ENV);
        echo '

</pre>
';
    }
    */
}


?>