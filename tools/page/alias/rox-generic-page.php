<?php


/**
 * this class allows to have a local page object,
 * and replaces the global PVars::getObj('page').
 * Unlike the PVars::getObj('page'), this one can render itself!
 * 
 * alias of PageWithParameterizedRoxLayout
 * 
 * inject the parameters using functionality defined in ObjectWithInjection
 */
class RoxGenericPage extends PageWithParameterizedRoxLayout
{
    protected function getColumnNames() {
        return array('col1', 'col3');
    }

    protected function submenu()
    {
        echo <<<SUBMENU
<div class="col-md-3 offcanvas-collapse">
    <div class="w-100 p-1 text-right d-md-none">
        <button type="button" class="btn btn-sm" aria-label="Close" data-toggle="offcanvas">
            <i class="fa fa-lg fa-times" aria-hidden="true"></i>
        </button>
    </div>
    {$this->get('newBar')}
</div>
SUBMENU;
    }
}

class RoxGenericPage_OLD
{
    private $_attributes = array();
    
    /**
     * set the attributes to be used in page.php template
     * This 'magic method' is called when you call
     * $page = new RoxGenericPage();
     * $page->keyname = $value;
     *
     * @param string $key name of the attribute
     * @param string $value buffered html source
     */
    public function __set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }
    
    
    /**
     * magic method called in page.php, typically by
     * <?=$Page->keyname ?>
     *
     * @param unknown_type $key
     * @return unknown
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_attributes)) return false;
        else return $this->_attributes[$key];
    }


    protected function getColumnNames() {
        return array('col1', 'col3');
    }

    /**
     * show the page.php template, using the attributes in $_attributes
     */
    public function render()
    {
        // we need this for the page.php template
        $Page = $this;
        
        // TODO: this loop can be removed when page.php has been updated in all branches.
        foreach($this->_attributes as $key => $value) {
            PVars::getObj('page')->$key = $value;
        }
        
        header('Content-type: text/html;charset="utf-8"');
        require_once TEMPLATE_DIR.'page.php';
        
        // this line takes care that the page output does not happen twice.
        PVars::getObj('page')->output_done = true;
    }
}

?>