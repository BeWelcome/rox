<?php


class DonateController extends PAppController
{
    public function __construct()
    {
        parent::__construct();
        $this->_model = new DonateModel();
        $this->_view  = new DonateView($this->_model);
    }
    
    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    
    
    
    
    public function index()
    {
        if (!isset($request[1])) {
            $request[1] = '';
        }
        $TDonationArray = false;
        $error = false;
        
        // static pages
        switch($request[1]) {
            case 'done':
                $error = $this->_model->returnFromPayPal();
                $sub = $request[1];
            case 'cancel':
                if (isset($_SESSION["PaypalBW_key"])) {
                    $sub = $request[1];
                    // Log to track wrong donation
                    MOD_log::get()->write("Donation cancelled  [\$_SESSION[\"PaypalBW_key\"]=".$_SESSION["PaypalBW_key"]."]","Donation");
                break;
                }
            default:
                $TDonationArray = $this->_model->getDonations();
                $sub = '';
                break;
        }
        
        ob_start();
        $this->_view->donate($sub,$TDonationArray,$error);
        $str = ob_get_contents();
        ob_end_clean();
        $P = PVars::getObj('page');
        $P->content .= $str;
        
        // teaser content
        ob_start();
        $this->_view->ShowSimpleTeaser('Donate');
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();
        
        // User bar on the left
        ob_start();
        $this->_view->donateBar();
        $str = ob_get_contents();
        ob_end_clean();
        $Page = PVars::getObj('page');
        $Page->newBar .= $str;
    }
}


?>