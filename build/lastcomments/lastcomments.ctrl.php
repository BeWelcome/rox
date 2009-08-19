<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/

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

