<div id="teaser"  class="clearfix" >
    <div class="subcolumns">

      <div class="c15l" >
        <div class="subcl" >
            <div id="pic_main" >
                <div id="img1">
                    <a href="gallery/show/user/<?=$member->Username?>"><img src="members/avatar/<?=$member->Username?>" alt="Picture of <?$member->Username?>" class="framed" /></a>
                </div> <!-- img1 -->
            </div> <!-- pic_main -->
        </div> <!-- subcl -->
      </div> <!-- c50l -->
      <div class="c85r" >
        <div class="subcr" >
            <div id="profile-info" >
                <div id="username" >
                    <strong><?=$member->Username ?></strong>
                    <?=$member->name() ?> <?=($verification_status) ? '<img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'">': ''?>
    <?=$words->flushBuffer()?>
                </div> <!-- username -->
                <?php
                  if (($right->hasRight("Accepter"))or($right->hasRight("SafetyTeam"))) { // for people with right display real status of the member
                    if ($member->Status!="Active") {
                        echo "<br /><span class=\"memberstatus\"> ",$member->Status," </span>\n";
                    }
                  } // end of for people with right dsiplay real status of the member
                  if ($member->Status=="ChoiceInactive") {
                        echo "<br /><span class=\"memberinactive\"> ",$ww->WarningTemporayInactive," </span>\n";
                  }
                ?>
                </p>


            <div id="navigation-path" >
                <!--<a href="country/" >Country</a>-->
                <h3><strong><a href="country/<?=$member->countryCode()."/".$member->region()."/".$member->city() ?>" ><?=$member->city() ?></a></strong>
                        (<a href="country/<?=$member->countryCode()."/".$member->region() ?>" ><?=$member->region() ?></a>)
                <strong><a href="country/<?=$member->countryCode() ?>" ><?=$member->country() ?></a></strong></h3>
            </div> <!-- navigation-path -->

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
            </div> <!-- subcr -->
      </div> <!-- c50r -->

    </div> <!-- subcolumns -->
    
</div> <!-- teaser -->

