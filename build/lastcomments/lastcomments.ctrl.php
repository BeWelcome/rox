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
				$page = new LastcommentsPage($this->_model->GetLastComments($_SESSION["Param"]->NbCommentsInLastComments));
			}
			else {
				$page = new MembersMustloginPage;
			}
//            $page->member = $this->_model->getLoggedInMember();
            return $page;
        }
		else if ($request[1]=="vote") {
			$IdComment=0 ;
			if (isset($request[2])) {
				$IdComment=$request[2] ;
			}
			$this->_model->AddVote($IdComment) ;
			$page = new LastcommentsPage($this->_model->GetLastComments($_SESSION["Param"]->NbCommentsInLastComments));
		}
		else if ($request[1]=="commentofthemoment") {
			$page = new LastcommentsPage($this->_model->GetCommentOfTheMoment(),$request[1]);
		}
		else if ($request[1]=="voteremove") {
			$IdComment=0 ;
			if (isset($request[2])) {
				$IdComment=$request[2] ;
			}
			$this->_model->RemoveVote($IdComment) ;
			$page = new LastcommentsPage($this->_model->GetLastComments($_SESSION["Param"]->NbCommentsInLastComments));
		}
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

