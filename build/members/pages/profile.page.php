<?php


class ProfilePage extends MemberPage
{
	
    protected function teaserHeadline()
    {
        echo 'Profile of someone';
    }
    
    
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
        include "../templates/profile_sidebar.php";
    }
    
    
    protected function column_col3()
    {
        $member = $this->member;
        //print_r($this->model->get_profile_language());
        //just to showcase the language selection method below while the
        //profile language switch isn't ready for action 
        //not sure if non-english profile should be shown as default in production
        //$profile_language = $_SESSION['IdLanguage'];
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
	
        $words = $this->getWords();		
        //$words->setLanguage('fr');
        
        $messengers = $member->messengers();
        $website = $member->WebSite;
        		
        $groups = $member->get_group_memberships();
        include "../templates/profile_main.php";
        
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
