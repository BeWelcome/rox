<?php


class QuicksearchController extends PAppController
{
    public function __construct()
    {
        parent::__construct();
        $this->_model = new Searchmembers();
        $this->_view  = new QuicksearchView($this->_model);
    }
    
    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    
    
    
    
    public function index()
    {
        $vw = new ViewWrap($this->_view);
        $P = PVars::getObj('page');
        
        // First check if the feature is closed
        if ($_SESSION["Param"]->FeatureQuickSearchIsClosed!='No') {
            $this->_view->showFeatureIsClosed();
            PPHP::PExit();
             break ;
        } // end of test "if feature is closed" 


        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        $error = false;
        $sub = '';
        $sub = $request[1];
        // static pages
        switch($request[1]) {
            case 'done':
                break;
            case 'cancel':
                break;
            default:
                break;
        }
        
        $P->content .= $vw->quicksearch_results(null) ;
        
        // teaser content
//        $P->teaserBar .= $vw->ShowSimpleTeaser('Donate',$TDonationArray);

        // submenu
        $P->subMenu .= $vw->submenu($sub);
        
        // User bar on the left
//        $P->newBar .= $vw->donateBar($TDonationArray);
    }
}


?>