<?php


class MemberPage extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        $member = $this->member;
        return "".$member->Username." - Profile";
    }
    
    
    protected function getTopmenuActiveItem()
    {
        return 'profile';
    }
    
    
    protected function getSubmenuItems()
    {
          if (APP_User::isBWLoggedIn()) {
            $username = $_SESSION['Username'];
            return array(
                array('profile', "members/$username", 'Profile'),
                array('visitors', "myvisitors", 'My visitors'),
                array('mypreferences', 'mypreferences', 'My Preferences'),
                array('editmyprofile', 'editmyprofile', 'Edit My Profile'),
                array('comments', "members/$username/comments", 'View Comments(n)'),
                array('blogs', "blog/$username", 'Blog'),
                array('gallery', "gallery/show/user/$username", 'Photo Gallery')
            );
        } else {
            $username = 'Boris';
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
        $comments_count = $member->count_comments(); 

        $agestr = "";
        if ($member->age == "hidden") {
            $agestr .= $words->get("AgeHidden");
        } else {
            $agestr= $words->get('AgeEqualX', "hidden" );
        }
        $languages = $member->get_profile_languages(); 
        $occupation = $member->get_trad("Occupation", $profile_language);        

        //$profile_language = $_SESSION['IdLanguage'];
        ?>
        <div id="teaser"  class="clearfix" >
        
          <div id="teaser_l" >
          
            <div id="pic_main" >
              <div id="img1" >
                <a href="myphotos.php?action=viewphoto&IdPhoto=<?=$member->getProfilePictureID()?>"  title="No picture for admin (He is ugly) but the update picture comment works !" >
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
                <strong><?php echo $member->Username ?></strong>
                <?php echo $member->name() ?>  
                <br />
              </div>
            </div>
             
            <div id="navigation-path" >
               <!--<A href="country/" >Country</A>-->
               	<h2><strong><a href="country/<?php echo  $member->countryCode()."/".$member->region()."/".$member->city() ?>" ><?php echo  $member->city() ?></a></strong>
				(<A href="country/<?php echo  $member->countryCode()."/".$member->region() ?>" ><?php echo  $member->region() ?></A>)
               	<strong><A href="country/<?php echo $member->countryCode() ?>" ><?php echo  $member->country() ?></A></strong></h2>
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
                       <?php 
                            echo $words->get('NbComments',  $comments_count['all']);
                            echo " (".$words->get('NbTrusts',  $comments_count['positive']).")";
	                    ?>
                      <br />
                      <?php
                            echo $agestr;
                      ?> 
                      <?php 
                            if($occupation != null) echo ", ".$occupation; ?>
                    </td>
                    <td>
                       <?=$words->get('ProfileVersionIn');?>:
                       <?php 
                       foreach($languages as $language) { 
                  	?>
                                <a href="<?=PVars::getObj('env')->baseuri."members/".$member->Username."/".$language?>" >
                                 <img height="11px"  width="16px"  src="<?=PVars::getObj('env')->baseuri?>bw/images/flags/<?=$language?>.png"  alt="<?=$language?>.png" >
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
