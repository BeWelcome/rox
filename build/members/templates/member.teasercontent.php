<div id="teaser"  class="clearfix" >
    <div id="teaser_l" >

        <div id="pic_main" >
            <div id="img1" >
                <a href="gallery/show/user/<?=$member->Username?>"><img src="members/avatar/<?=$member->Username?>" alt="Picture of <?$member->Username?>" /></a>
            </div> <!-- img1 -->
        </div> <!-- pic_main -->
    </div> <!-- teaser_l -->

    <div id="teaser_r" >
        <div id="profile-info" >
            <div id="username" >
                <strong><?=$member->Username ?></strong>
                <?=$member->name() ?><br />
            </div> <!-- username -->
            <p>
            <?php
              if (($right->hasRight("Accepter"))or($right->hasRight("SafetyTeam"))) { // for people with right display real status of the member
                if ($member->Status!="Active") {
                    echo "<span class=\"memberstatus\"> ",$member->Status," </span>\n";
                }
              } // end of for people with right dsiplay real status of the member
              if ($member->Status=="ChoiceInactive") {
                    echo "<span class=\"memberinactive\"> ",$ww->WarningTemporayInactive," </span>\n";
              }
            ?>
            </p>
        

        <div id="navigation-path" >
            <!--<a href="country/" >Country</a>-->
            <h2><strong><a href="country/<?=$member->countryCode()."/".$member->region()."/".$member->city() ?>" ><?=$member->city() ?></a></strong>
                    (<a href="country/<?=$member->countryCode()."/".$member->region() ?>" ><?=$member->region() ?></a>)
            <strong><a href="country/<?=$member->countryCode() ?>" ><?=$member->country() ?></a></strong></h2>
        </div> <!-- navigation-path -->
        

        <img src="images/icons/<?=($member->Accomodation) ? $member->Accomodation : 'neverask'?>.gif"  class="float_left"  title="<?=$member->Accomodation?>"  width="30"  height="30"  alt="<?=$member->Accomodation?>" />
        <?php
        // specific icon according to members.TypicOffer
        if (strstr($member->TypicOffer, "guidedtour"))
        {
            $title = $words->getSilent("TypicOffer_guidedtour");
			$image_name = 'icon_castle.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
        if (strstr($member->TypicOffer, "dinner"))
        {
            $title = $words->getSilent("TypicOffer_dinner");
			$image_name = 'icon_food.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
        if (strstr($member->TypicOffer, "CanHostWeelChair"))
        {
            $title = $words->getSilent("TypicOffer_CanHostWeelChair");
			$image_name = 'wheelchair.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
		echo $words->flushBuffer();
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
        </div> <!-- profile-info -->

        <div id="linkpath">
            <?
                // display linkpath, only if not the members own profile
                if (isset($_SESSION["IdMember"]) and strcmp($member->id,$_SESSION["IdMember"]) != 0) {
                    $linkwidget = new LinkSinglePictureLinkpathWidget();
                    $linkwidget->render($_SESSION["IdMember"],$member->id,'profile-picture-linkpath');
                }
             ?>
        </div> <!-- linkpath -->
    </div> <!-- teaser_r -->
</div> <!-- teaser -->

