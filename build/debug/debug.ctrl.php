<?php


class DebugController extends RoxControllerBase
{
    public function index($args = false)
    {
        $request = $args->request;
        if (!MOD_right::get()->hasRight('Debug')) {
            $page = new PublicStartpage();
        } else switch (isset($request[0]) ? $request[0] : false) {
            case 'sqltest':
                $page = new SqltestPage;
                $page->model = new SqltestModel();
                return $page;
            case 'debug':
            default:
                $page = new DebugPage;
        }
        return $page;
    }
}



?>