<div id="teaser"  class="clearfix" >
  <div id="teaser_l" >
  
    <div id="pic_main" >
      <div id="img1" >
        <?php $layoutbits = new MOD_layoutbits;
        echo $layoutbits->PIC_100_100 ($member->Username,'',$style=""); ?>
      </div>
    </div>
  </div>
 
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
        <img src="images/icons/<?=($member->Accomodation) ? $member->Accomodation : 'neverask'?>.gif"  class="float_left"  title="No, sorry"  width="30"  height="30"  alt="<?=$member->Accomodation?>" >
        <?php
        // specific icon according to membes.TypicOffer
        if (strstr($member->TypicOffer, "guidedtour"))
        {
            if (stripos($words->get("TypicOffer_guidedtour"),"<") !== false)
            {
                $translation_link = $words->get("TypicOffer_guidedtour");
                $title = "";
            }
            else
            {
                $translation_link = "";
                $title = $words->get("TypicOffer_guidedtour");
            }
            echo "              <img src='images/icons/icon_castle.gif' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />{$translation_link}\n";
        }
        if (strstr($member->TypicOffer, "dinner"))
        {
            if (stripos($words->get("TypicOffer_dinner"),"<") !== false)
            {
                $translation_link = $words->get("TypicOffer_dinner");
                $title = "";
            }
            else
            {
                $translation_link = "";
                $title = $words->get("TypicOffer_dinner");
            }
            echo "              <img src='images/icons/icon_food.gif' class='float_left' title='{$title}' width='30' height='30' alt='icon_food' />{$translation_link}\n";
        }
        if (strstr($member->TypicOffer, "CanHostWeelChair"))
        {
            if (stripos($words->get("TypicOffer_CanHostWeelChair"),"<") !== false)
            {
                $translation_link = $words->get("TypicOffer_CanHostWeelChair");
                $title = "";
            }
            else
            {
                $translation_link = "";
                $title = $words->get("TypicOffer_CanHostWeelChair");
            }
            echo "              <img src='images/icons/wheelchair.gif' class='float_left' title='{$title}' width='30' height='30' alt='wheelchair' />{$translation_link}\n";
        }
        ?>
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
    <? 
        // display linkpath, only if not the members own profile
        if (isset($_SESSION["IdMember"]) and strcmp($member->id,$_SESSION["IdMember"]) != 0) {
            $linkwidget = new LinkSinglePictureLinkpathWidget();
            $linkwidget->render($_SESSION["IdMember"],$member->id,'profile-picture-linkpath');
        }
     ?>
  </div>
</div>

