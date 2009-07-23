<?php

/**
 * This controller is called when the request is 'groups/...'
 */
class NotifyController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new NotifyModel();
    }
    
    public function __destruct()
    {
        unset($this->_model);
    }
	
    public function index()
    {
        $request = PRequest::get()->request;        
        if (!isset($request[1]))
        {
            $this->_redirect('');
        }
        
        switch ($request[1])
        {
            case 'admin':
                $BW_Rights = new MOD_right();
                if (!$BW_Rights->hasRight('Admin') || !$BW_Rights->hasRight('Notify')) 
                    $this->_redirect(''); 
                $page = new NotifyAdminPage();
                $page->setModel($this->_model);
                if (isset($_GET['sort'])) $page->sortorder = $_GET['sort'];
                $page->active_page = $this->getPageNumber($request, 2);
                return $page;
                break;
            case 'search':
                $terms = ((isset($_GET['GroupsSearchInput'])) ? $_GET['GroupsSearchInput'] : '');
                $resultpage = ((isset($_GET['Page'])) ? $_GET['Page'] : 0);
                $order = ((isset($_GET['Order'])) ? $_GET['Order'] : 'nameasc');
                $page = new GroupsSearchPage();
                $page->search_result = $this->_model->findGroups($terms, $resultpage, $order);
                $page->result_page = $resultpage;
                $page->result_order = $order;
                $page->search_terms = $terms;
                break;
            default:
                // check wether there's a note with id = $request[1]
                if ($note = $this->_model->findNote($request[1]))
                {
                    switch ($request[2])
                    {
                        case 'check':
                            // Ajax function
                            $this->checkNote($note);
                            PPHP::PExit();                        
                        default:
                            break;
                    }
                }
        }
        $page->member = $this->_model->getLoggedInMember();
        $page->model = $this->_model;
        return $page;
    }


    /**
     * Mark a note as "checked"
     *
     */
    private function checkNote($note)
    {
        $note->updateNote(true);
        return false;
    }


    /**
     * handles showing the group admin page with various options on it
     *
     * @param object $group - group entity
     * @param string $request - action to carry out
     * @access private
     * @return object $page
     */
    private function admin($group, $request)
    {
        if (!$this->_model->getLoggedInMember() || !$this->_model->canAccessGroupAdmin($group))
        {
            $this->_redirect('groups/');
        }

        return new GroupAdminPage();
    }

    private function _redirect($rel_url)
    {
        header('Location: '.PVars::getObj('env')->baseuri.$rel_url);
        PPHP::PExit();
    }

    /**
     * callback for deleting/updating notes
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return string
     */
    public function NotifyAdminCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $BW_Rights = new MOD_right();
        $layoutkit = 
        $count = $action->count;
        $return = $args->req;

        if (!APP_User::loggedIn() /*|| !$BW_Rights->hasRight('Admin') */)
        {
            return $return;
        }

        if ($count < 0)
        {
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        $post = $args->post;
        if (isset($post['action']) && $post['action'] == 'create' && !empty($post['IdMember']) || !empty($post['IdRelMember']) || !empty($post['Type']) || !empty($post['WordCode']))
        {
            $result = $this->_model->createNote($post);
        } elseif (isset($post['action']) && $post['action'] == 'delete' && count($post['note-mark']) > 0) {
            $result = $this->_model->deleteNotes($post['note-mark']);
        } else {
            $mem_redirect->problems = true;
            return $return;
        }        

        $mem_redirect->result = $result;
        return $return;
    }
    
    
    protected function getPageNumber($request, $req_index)
    { 
        if (!isset($request[$req_index])) {
            return 1;
        } else {
            $s = $request[$req_index];
            if (is_numeric($s)) {
                return $s;
            } else if (eregi('page([0-9]+)', $r, $regs)) {
                return $regs[1];
            } else if (eregi('p([0-9]+)', $r, $regs)) {
                return $regs[1];
            } else {
                // not the right format for page
                return 1;
            }
        }
    }

}

