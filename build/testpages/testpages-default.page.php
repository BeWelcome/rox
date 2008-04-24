<?php


class TestpagesDefaultPage extends PageWithActiveSkin
{
    protected function teaserHeadline()
    {
        if ($pagename = $this->pagename) {
            echo $pagename;
        } else {
            // some testing stuff...
            echo '<pre>SERVER<br><br>';
            print_r($_SERVER);
            echo '<br>REQUEST';
            print_r($_REQUEST);
            echo '<br>ENV';
            print_r($_ENV);
            echo '</pre>';
        }
    }
}


?>