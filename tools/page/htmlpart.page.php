<?php


class PageWithHTMLpart extends PageWithRoxLayout
{
    private $_widgets = array();  // will be asked for stylesheet and scriptfile information

    public function render() {
        $this->init();
        $this->printHTML();
    }
    
    protected function init() {
        // by default, nothing happens.
        // the idea of this function is to set some values,
        // such as page title, meta keyword, meta description
    }

    protected function getPageTitle() {
        return 'BeWelcome';
    }
    
    
    /**
     * Widgets added this way will be asked
     * for stylesheet and scriptfile information
     * TODO: evtl not a good idea to do it this way.
     *
     * @param RoxWidget $widget
     */
    public function addWidget(RoxWidget $widget)
    {
        $this->_widgets[] = $widget;
    }
    
    
    protected function printHTML()
    {
        echo (is_object($this->layoutkit) && (is_object($this->layoutkit->mem_from_redirect))) ? $this->layoutkit->mem_from_redirect->buffered_text : '';
        
        echo $this->get('content');
        
    }

    protected function getPagePermalink() {
        return 'index';
    }
	
    protected function getPage_meta_robots()
    {
        if (empty($this->meta_robots)) {
            $this->meta_robots = 'All' ;
        }
        return($this->meta_robots) ;
    }
    
    public function SetMetaRobots($ss) 
    {
            $this->meta_robots = $ss ;
    }
	
	
}
