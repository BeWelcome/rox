<?php

class QuicksearchView extends PAppView
{
    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    private $_model;
    
    public function __construct(Searchmembers $model) {
        $this->_model = $model;
    }

    public function ShowSimpleTeaser($title)
    {
        require TEMPLATE_DIR.'apps/rox/teaser_simple.php';
    }

    public function quicksearch_results($TReturn)
    {

		$this->page->title='Search Results - Bewelcome' ;
        require 'templates/quicksearch.php';
    }

    public function showFeatureIsClosed()		{
//        PVars::getObj('page')->title = 'Feature Closed - Bewelcome';
		$this->page->title='Feature Closed - Bewelcome' ;
        require 'templates/featureclosed.php';
	} // end of showFeatureIsClosed()


	public function submenu($sub) {
//	  	 $Stat=$this->_model->getStatForDonations() ;
//        require TEMPLATE_DIR.'apps/rox/submenu_quicksearch.php';
	}    
    
}








?>