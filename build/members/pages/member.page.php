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
                array('editmyprofile', 'editmyprofile', $ww->EditMyProfile),
                array('mypreferences', 'mypreferences', $ww->MyPreferences),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery)
            );
        } else {
            return array(
                array('profile', "members/$username", $ww->MemberPage),
                array('comments', "members/$username/comments", $ww->ViewComments.'('.$comments_count['all'].')'),
                array('trips', "trip/show/$username", $ww->Trips),
                array('blogs', "blog/$username", $ww->Blog),
                array('gallery', "gallery/show/user/$username", $ww->Gallery)
            );
        }
    }  
    
    protected function columnsArea()
    {
        $side_column_names = parent::getColumnNames();
        $mid_column_name = array_pop($side_column_names);
        ?>
        <?php foreach ($side_column_names as $column_name) { ?>

          <div id="<?=$column_name ?>">
            <div id="<?=$column_name ?>_content" class="clearfix">
              <? $name = 'column_'.$column_name ?>
              <?php $this->$name() ?>
            </div> <!-- <?=$column_name ?>_content -->
          </div> <!-- <?=$column_name ?> -->

        <?php } ?>

          <div id="<?=$mid_column_name ?>">
            <div id="<?=$mid_column_name ?>_content" class="clearfix">
              <?php //parent::submenu(); ?>
              <? $name = 'column_'.$mid_column_name; ?>
                <?php $this->$name() ?>
              <?php $this->$name ?>
            </div> <!-- <?=$mid_column_name ?>_content -->
            <!-- IE Column Clearing -->
            <div id="ie_clearing">&nbsp;</div>
            <!-- Ende: IE Column Clearing -->
          </div> <!-- <?=$mid_column_name ?> -->
        <?php
    }

    protected function submenu() {
        parent::submenu();
    }

    protected function getStylesheets() {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/profile.css';
       return $stylesheets;
    }
    
    /*
     * The idea was that stylesheetpatches was for MSIE
     */
    protected function getStylesheetPatches()
    {
        //$stylesheet_patches = parent::getStylesheetPatches();
        $stylesheet_patches[] = 'styles/css/minimal/patches/patch_2col_left.css';
        return $stylesheet_patches;
    }

    
    
    protected function teaserContent()
    {
        $this->__call('teaserContent', array());
    }
}


?>
