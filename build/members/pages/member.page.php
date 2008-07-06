<?php


class MemberPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        $member = $this->member;
        return $this->wwsilent->ProfilePageFor($member->Username)." - BeWelcome";
    }
    
    
    protected function getTopmenuActiveItem()
    {
        return 'profile';
    }
    
    
    protected function getSubmenuItems()
    {
        $username = $this->member->Username;
        $ww = $this->wwsilent;
        if ($this->myself) {
            return array(
                array('profile', "members/$username", $ww->MemberPage),
                array('visitors', "myvisitors", $ww->MyVisitors),
                array('mypreferences', 'mypreferences', $ww->MyPreferences),
                array('editmyprofile', 'editmyprofile', $ww->EditMyProfile),
                array('comments', "members/$username/comments", $ww->ViewComments.'(n)'),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery)
            );
        } else {
            return array(
                array('profile', "members/$username", 'Profile'),
                array('comments', "members/$username/comments", 'View Comments(n)'),
                array('gallery', "gallery/show/user/$username", 'Photo Gallery')
            );
        }
    }
    
    
    protected function teaserContent()
    {
        $member = $this->member;
	
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;

        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $comments_count = $member->count_comments(); 

        $agestr = "";
        if ($member->age == "hidden") {
            $agestr .= $ww->AgeHidden;
        } else {
            $agestr= $ww->AgeEqualX("hidden");
        }
        $languages = $member->get_profile_languages(); 
        $occupation = $member->get_trad("Occupation", $profile_language);        

        //$profile_language = $_SESSION['IdLanguage'];
            
        require "templates/profile_teaser.php";
    }
}


?>
