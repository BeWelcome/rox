<?php

/**
 * This controller is called when the request is 'rights/...'
 */
class RightsController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new RightsModel();
    }
    
    public function __destruct()
    {
        unset($this->_model);
    }
	
    public function index()
    {
        if (!$this->_model->hasRightsAccess())
        {
            die('You cannot access this app');
        }

        $roles = $this->_model->getAllRoles();
        $request = PRequest::get()->request;

        $page->member = $this->_model->getLoggedInMember();
        $page->model = $this->_model;
        return $page;
    }

}

