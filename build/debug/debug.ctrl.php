<?php


class DebugController extends RoxControllerBase
{
    function index($args = false)
    {
        $request = $args->request;
        if (!MOD_right::get()->hasRight('Debug')) {
            return new PublicStartpage();
        } else switch (isset($request[0]) ? $request[0] : false) {
            case 'sqltest':
                $page = new SqltestPage;
                $page->model = new SqltestModel();
                return $page;
            case 'debug':
            default:
                switch (isset($request[1]) ? $request[1] : false) {
                    case 'sqltest':
                        $page = new SqltestPage;
                        $page->model = new SqltestModel();
                        return $page;
                    case 'dbsummary':
                        $page = new DatabaseSummaryPage;
                        $page->model = new DatabaseSummaryModel();
                        return $page;
                    default:
                        return new DebugPage;
                }
        }
    }
}



?>