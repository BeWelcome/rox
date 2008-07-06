<?php


class ProfilePage extends MemberPage
{
    protected function getSubmenuActiveItem()
    {
        return 'profile';
    }
    
    protected function leftSidebar()
    {
    	$member = $this->member;
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
    	
        $words = $this->getWords();
        include "x/../../templates/profile_sidebar.php";
    }
    
    
    protected function column_col33()
    {
        $this->main();
        // include "x/../../templates/profile_main.php";
        
        if (false) {
            $member = $this->member;
            echo '<pre><h1>this->member</h1><br />';
            print_r($member);
            echo '<hr><h1>this->member->trads</h1><br />';
            print_r($member->trads);
            //echo '<hr><h1>this->member->address</h1><br />';
            //print_r($member->address);
            echo '</pre>';
        }
                
    }
}


?>
