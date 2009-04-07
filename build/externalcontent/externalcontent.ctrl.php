<?php


class ExternalcontentController extends RoxControllerBase
{
    function index($args = false)
    {
        $page = new ExternalContentPage();
        $page->setExternalURL('http://blogs.bevolunteer.org/', $args->get);
        $page->get = $args->get;
        return $page;
    }
}


?>