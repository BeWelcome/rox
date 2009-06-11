<div id="profile-info" >

<div class="subcolumns">

  <div class="c50l" >
    <div class="subcl" >
                    <div id="username" >
                        <strong><?=$member->Username ?></strong>
                        <?=($verification_status) ? '<img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'">': ''?>
                        <?=($member->Accomodation == 'anytime') ? '<img src="images/icons/door_open.png" alt="'.$member->Accomodation.'" title="'.$member->Accomodation.'">': ''?>
                        <br />
                        <?$name = $member->name(); ?><?=($name == '') ? $member->Occupation : $name;?>

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
                    
    </div> <!-- subcl -->
  </div> <!-- c50l -->
  <div class="c50r" >
    <div class="subcr" >
        <? // Profile translations ?>
            <?php
            if (get_class($this) == 'EditMyProfilePage' || get_class($this) == 'EditProfilePage') $urlstring = 'editmyprofile';
            if (get_class($this) == 'ProfilePage' || get_class($this) == 'MyProfilePage') $urlstring = 'members/'.$member->Username;
            if (isset($urlstring)) {
                require 'profileversion.php'; 
            }
            ?>
    </div> <!-- subcr -->
  </div> <!-- c50r -->

</div> <!-- subcolumns -->

            </div> <!-- profile-info -->

