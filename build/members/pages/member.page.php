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
        $member = $this->member;
        
        $words = $this->getWords();
        $ww = $this->ww;
        $wwsilent = $this->wwsilent;
        $comments_count = $member->count_comments(); 
        if ($this->myself) {
            return array(
                array('profile', "members/$username", $ww->MemberPage),
                array('visitors', "myvisitors", $ww->MyVisitors),
                array('mypreferences', 'mypreferences', $ww->MyPreferences),
                array('editmyprofile', 'editmyprofile', $ww->EditMyProfile),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery)
            );
        } else {
            return array(
                array('profile', "members/$username", 'Profile'),
                array('comments', "members/$username/comments", 'View Comments('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", 'Photo Gallery')
            );
        }
    }


    protected function leftsidebar()
    {
	    ?>
	    <div id="personalmenu" class="sm">
	    <? parent::submenu() ?>
	    </div>
		<?php
    }    

    protected function submenu() {

    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/profile.css';
       return $stylesheets;
    }
    
    
    protected function teaserContent()
    {
        $this->__call('teaserContent', array());
    }
}


?>
