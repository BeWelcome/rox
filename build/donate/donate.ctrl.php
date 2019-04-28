<?php


class DonateController extends PAppController
{
    public function __construct()
    {
        parent::__construct();
        $this->_model = new DonateModel();
        $this->_view = new DonateView($this->_model);
    }
    
    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    
    
    
    
    public function index()
    {
        $this->_view->_session = $this->getSession();
        $vw = new ViewWrap($this->_view);
        $P = PVars::getObj('page');
        
        $request = PRequest::get()->request;
        if (!isset($request[1])) {
            $request[1] = '';
        }
        $TDonationArray = false;
        $error = false;
        $sub = '';
        $sub = $request[1];
        // static pages
        switch($request[1]) {
            case 'done':
                $error = $this->_model->returnFromPayPal();
                $TDonationArray = $this->_model->getDonations();
                break;
            case 'cancel':
                if ($this->_session->has( "PaypalBW_key" )) {
                    // Log to track wrong donation
                    MOD_log::get()->write("Donation cancelled  [\$this->_session->get(\"PaypalBW_key\"]=".$_SESSION["PaypalBW_key"].")","Donation");
                break;
                }
            default:

                $TDonationArray = $this->_model->getDonations();
                break;
        }
        
        $P->content .= $vw->donate($sub,$TDonationArray,$error);
        
        // teaser content
        $P->teaserBar .= $vw->ShowSimpleTeaser('Donate',$TDonationArray)
            . $vw->donateBar($TDonationArray);

        // submenu
        $P->subMenu .= $vw->submenu($sub);
        
        // User bar on the left

    }
}


?>