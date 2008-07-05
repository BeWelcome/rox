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
        ?>
        <div id="teaser"  class="clearfix" >
        
          <div id="teaser_l" >
          
            <div id="pic_main" >
              <div id="img1" >
                <a href="myphotos.php?action=viewphoto&amp;IdPhoto=<?=$member->getProfilePictureID()?>"  title="No picture for admin (He is ugly) but the update picture comment works !" >
                  <img src="memberphotos"  alt="ProfilePicture" >
                </a>
                
              </div>
            </div>
          </div>
          <div id="teaser_gmap" >
            <img src="http://maps.google.com/staticmap?zoom=4&maptype=mobile&size=350x120&center=48.1333333,-1.2&markers=48.1333333,-1.2,blue&key=" >
          </div>
          <div id="teaser_r" >
            <div id="profile-info" >
              <div id="username" >
                <strong><?=$member->Username ?></strong>
                <?=$member->name() ?>  
                <br />
              </div>
            </div>
             
            <div id="navigation-path" >
               <!--<A href="country/" >Country</A>-->
               	<h2><strong><a href="country/<?=$member->countryCode()."/".$member->region()."/".$member->city() ?>" ><?=$member->city() ?></a></strong>
				(<A href="country/<?=$member->countryCode()."/".$member->region() ?>" ><?=$member->region() ?></A>)
               	<strong><A href="country/<?=$member->countryCode() ?>" ><?=$member->country() ?></A></strong></h2>
               <?php
               /*
              <A href="../country/<?php echo $member->countrycode()?>" ><?php $member->country()?></A>
               > 
              <A href="../country/<?php echo $member->countrycode()?>/<?php echo $member->region()?>" ><?php echo $member->region()?></A>
               > 
              <A href="../country/<?php echo $member->countrycode()?>/<?php echo $member->region()?>/<?php echo $member->city()?>" ><?php echo $member->city()?>�</A>
               */
               ?>
            </div>
            <div id="profile-info">
              <!--<IMG src="images/neverask.gif"  class="float_left"  title="No, sorry"  width="30"  height="30"  alt="neverask" >-->
              <table>
                <tbody>
                  <tr>
                    <td>
                       <?=$ww->NbComments($comments_count['all'])." (".$ww->NbTrusts($comments_count['positive']).")" ?>
                      <br />
                      <?=$agestr ?> 
                      <?php 
                            if($occupation != null) echo ", ".$occupation; ?>
                    </td>
                    <td>
                       <?=$ww->ProfileVersionIn ?>:
                       <?php 
                       foreach($languages as $language) { 
                  	?>
                                <a href="members/<?=$member->Username ?>/<?=$language ?>">
                                 <img height="11px"  width="16px"  src="bw/images/flags/<?=$language ?>.png"  alt="<?=$language ?>.png">
                      	</a>                       	
                       <?php } ?>
                       
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php
    }
}


?>
