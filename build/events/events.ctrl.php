<?php
/**
 * Events controller class.
 *
 * @author shevek
 */
class EventsController extends RoxControllerBase
{
    /**
     * Declaring private variables.
     */
    private $_model;
    private $_view;
    protected $event;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new EventsModel();
        $this->_view  = new EventsView($this->_model);
    }
    
    public function list_all() {
        return new EventsListPage();
    }
    
    public function find() {
        return new EventsFindPage();
    }
    
    public function show() {
        return new EventsShowPage(new Event());
    }
    
    public function create() {
        return new EventsCreatePage();
    }
    
}
