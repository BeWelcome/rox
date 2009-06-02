<?php


abstract class RoxWidget extends VisualComponent
{

    /**
     * Holds an object of the entity factory, used for instantiating entities (obviously)
     * Loaded by __construct() - so all descendants of RoxModelBase has access to it
     *
     * @var object RoxEntityFactory
     * @access protected
     */
    protected $_entity_factory;

    public function __construct()
    {
        $this->_entity_factory = new RoxEntityFactory;
        parent::__construct();
    }


    /**
     * calls the entity factory to create an entity, passes along any arguments
     *
     * @param string - first parameter must be the name of the entity to create
     * @return object
     * @access protected
     */
    protected function createEntity(/* args */)
    {
        $args = func_get_args();
        return call_user_func_array(array($this->_entity_factory, 'create'), $args);
    }

    /**
     * please implement!
     * render() method does the output.
     */
    abstract public function render();
    
    public function getStylesheets() {
        return array();
    }
    
    public function getScriptfiles() {
        return array();
    }
    
}

