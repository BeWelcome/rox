<?php

/**
 * This controller is called when the request is 'comments/...'
 */
class LastCommentsController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new LastcommentsModel();
    }
    
    public function __destruct()
    {
        unset($this->_model);
    }

    public function index()
    {
        $request = PRequest::get()->request;
        if (!isset($request[1]))         {
			if( ($User = APP_User::login())) {
				$page = new LastcommentsPage($this->_model->GetLastComments());
				$page->model = $this->_model;
			}
			else {
                $page = new MembersMustloginPage;
			}
//            $page->member = $this->_model->getLoggedInMember();
            return $page;
        }
        $page->member = $this->_model->getLoggedInMember();
        $page->model = $this->_model;
        return $page;
    }


    private function _redirect($rel_url)
    {
        /*
        echo PVars::getObj('env')->baseuri.'<br>';
        echo PVars::getObj('env')->baseuri.implode('/', PRequest::get()->request).'<br>';
        echo PVars::getObj('env')->baseuri.$rel_url;
        */
        header('Location: '.PVars::getObj('env')->baseuri.$rel_url);
        PPHP::PExit();
    }

}

