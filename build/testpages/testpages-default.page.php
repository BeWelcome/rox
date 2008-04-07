<?php


class TestpagesDefaultPage extends PageWithRoxLayout
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