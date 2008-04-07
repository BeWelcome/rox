<?php


class TestpagesDefaultPage extends PageWithActiveSkin
{
    protected function teaserHeadline()
    {
        if ($pagename = $this->pagename) {
            echo $pagename;
        } else {
            echo 'no pagename found';
        }
    }
}


?>