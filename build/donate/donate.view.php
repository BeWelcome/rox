<?php

class DonateView extends PAppView
{
    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    public function ShowSimpleTeaser($title)
    {
        require TEMPLATE_DIR.'apps/rox/teaser_simple.php';
    }

    public function donate($sub = false,$TDonationArray = false, $error = false)
    {
        if ($sub == 'list' && $TDonationArray) {
            require TEMPLATE_DIR.'apps/rox/donate_list.php';
        } else require TEMPLATE_DIR.'apps/rox/donate.php';
    }
    
    public function donateBar($TDonationArray = false)
    {
        require TEMPLATE_DIR.'apps/rox/userbar_donate.php';
    }

	public function submenu($sub) {
        require TEMPLATE_DIR.'apps/rox/submenu_donate.php';
	}    
    
}








?>