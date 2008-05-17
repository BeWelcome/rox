<?php


class SqltestController extends RoxControllerBase
{
    public function index($args = false)
    {
        $request = $args->request;
        $page = new SqltestPage();
        $page->model = new SqltestModel();
        return $page;
    }
}



?>