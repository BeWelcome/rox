<?php


class TestpagesController extends RoxControllerBase
{
    public function index($args = false)
    {
        $request = $args->request;
        if (!isset($request[1])) {
            $page = new TestpagesDefaultPage();
            $page->pagename = '$request[1] not defined.';
        } else if (!class_exists($classname = $request[1])) {
            $page = new TestpagesDefaultPage();
            $page->pagename = $request[1] . ' - not a class.';
        } else if (!method_exists($classname, 'render')) {
            $page = new TestpagesDefaultPage();
            $page->pagename = $classname . '::render() not defined.';
        } else {
            $page = new $classname();
        }
        return $page;
    }
}


?>