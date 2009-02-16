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
 
<? 
  	// display linkpath, only if not the members one profile
// var_dump($_SESSION["IdMember"]);	
// var_dump($m->id);
// var_dump(strcmp($m->id,$SESSION["IdMember"]));

	if (isset($_SESSION["IdMember"]) and strcmp($member->id,$_SESSION["IdMember"]) != 0) {
        $linkwidget = new LinkSinglePictureLinkpathWidget();
        $linkwidget->render($_SESSION["IdMember"],$member->id,'profile-picture-linkpath');
    }
  
 ?>
 
  <div id="teaser_r" >
    <div id="profile-info" >
      <div id="username" >
<strong><?=$member->Username ?></strong>
<?=$member->name() ?>  
<br />
      </div>
      <p>
<?php
  if (($right->hasRight("Accepter"))or($right->hasRight("SafetyTeam"))) { // for people with right display real status of the member
    if ($member->Status!="Active") {
        echo "<table><tr><td bgcolor=yellow><font color=blue><b> ",$member->Status," </b></font></td></table>\n";
    }
  } // end of for people with right dsiplay real status of the member
  if ($member->Status=="ChoiceInactive") {
        echo "<table><tr><td bgcolor=yellow align=center>&nbsp;<br><font color=blue><b> ",$ww->WarningTemporayInactive," </b></font><br>&nbsp;</td></tr></table>\n";
  }
?>
    </p>
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
      <img src="images/icons/<?=$member->Accomodation?>.gif"  class="float_left"  title="No, sorry"  width="30"  height="30"  alt="<?=$member->Accomodation?>" >
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
  </tr>
</tbody>
      </table>
    </div>
  </div>
</div>

