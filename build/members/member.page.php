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
        $items = array();
        if (APP_User::isBWLoggedIn()) {
            $username = $_SESSION['Username'];
            return array(
                array('profile', "members/$username", 'Profile'),
                array('visitors', "myvisitors", 'My visitors'),
                array('mypreferences', 'mypreferences', 'My Preferences'),
                array('editmyprofile', 'editmyprofile', 'Edit My Profile'),
                array('comments', "members/$username/comments", 'View Comments(n)'),
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
        return $items;
    }
    
    
    protected function teaserContent()
    {
		$member = $this->member;
		//TODO: language selection from profile selection, not session language
		$profile_language = $_SESSION['IdLanguage'];
        ?>
        <DIV id="teaser"  class="clearfix" >
           
          <DIV id="teaser_l" >
             
            <DIV id="pic_main" >
               
              <DIV id="img1" >
                <A href="myphotos.php?action=viewphoto&IdPhoto=7"  title="No picture for admin (He is ugly) but the update picture comment works !" >
                  <IMG src="http://localhost/bw-trunk-new/htdocs/bw//memberphotos"  alt="ProfilePicture" >
                </A>
              </DIV>
              <DIV id="pic_sm1" >
                <A href="member.php?action=previouspicture&photorank=0&cid=1" >
                  <IMG name="pic_sm1"  src="http://localhost/bw-trunk-new/htdocs/bw//memberphotos"  width="30"  height="30"  border="0" >
                </A>
                 
              </DIV>
              <DIV id="pic_sm2" >
                 
                <IMG name="pic_sm2"  src="http://localhost/bw-trunk-new/htdocs/bw//memberphotos"  width="30"  height="30"  border="0" >
              </DIV>
              <DIV id="pic_sm3" >
                 
                <A href="member.php?action=nextpicture&photorank=0&cid=1" >
                  <IMG name="pic_sm3"  src="http://localhost/bw-trunk-new/htdocs/bw//memberphotos"  width="30"  height="30"  border="0" >
                </A>
              </DIV>
            </DIV>
          </DIV>
          <DIV id="teaser_gmap" >
            <IMG src="http://maps.google.com/staticmap?zoom=4&maptype=mobile&size=350x120&center=48.1333333,-1.2&markers=48.1333333,-1.2,blue&key=" >
          </DIV>
          <DIV id="teaser_r" >
             
            <DIV id="profile-info" >
              <DIV id="username" >
                <STRONG><?php echo $member->Username ?></STRONG>
                <?php echo $member->name() ?>  
                <BR>
              </DIV>
            </DIV>
             
            <DIV id="navigation-path" >
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
            </DIV>
            <DIV id="profile-info" >
              <!--<IMG src="images/neverask.gif"  class="float_left"  title="No, sorry"  width="30"  height="30"  alt="neverask" >-->
              <TABLE>
                <TBODY>
                  <TR>
                    <TD>
                       <?php $comments_count = $member->count_comments(); 
                       echo $comments_count['all']." comments (".$comments_count['positive']." positive)"?>
                      <BR>
                      Age: <?php echo $member->age()?> 
                      <?php $occupation = $member->get_trad("Occupation", $profile_language);
                      		if($occupation != null) echo ", ".$occupation; ?>
                    </TD>
                    <TD>
                       Available translations:
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=1&lang=en" >
                        <IMG height="11px"  width="16px"  src="images/flags/en.png"  alt="en.png" >
                      </A>
                      <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=1&lang=fr" >
                        <IMG height="11px"  width="16px"  src="images/flags/fr.png"  alt="fr.png" >
                      </A>
                    </TD>
                  </TR>
                </TBODY>
              </TABLE>
            </DIV>
          </DIV>
        </DIV>
        <?php
    }
}


?>