<?php

class DonateView extends PAppView
{
    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    private $_model;
    
    public function __construct(DonateModel &$model) {
        $this->_model =& $model;
    }

    public function ShowSimpleTeaser($title)
    {
        $words = new MOD_words();
        echo '<div id="teaser" class="page-teaser clearfix">';
        echo '<h1>'.$words->getFormatted($title).'</h1>';
        echo '</div>';
    }

    public function donate($sub = false,$TDonationArray = false, $error = false)
    {
        if ($sub == 'list' && $TDonationArray) {
            require 'templates/donate_list.php';
        } else require 'templates/donate.php';
    }
    
    public function donateBar($TDonationArray = false)
    {
         $Stat=$this->_model->getStatForDonations() ;
        require 'templates/userbar_donate.php';
    }

    public function submenu($sub) {
        require 'templates/submenu_donate.php';
    }
}

?>
