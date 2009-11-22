<div id="profile-info" >

<div class="subcolumns">

  <div class="c50l" >
    <div class="subcl" >
                    <div id="username" >
                        <strong><?=$member->Username ?></strong>
                        <?=($verification_status) ? '<a href="verifymembers/verifiersof/'.$member->Username.'"><img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'"></a>': ''?>
                        <?=($member->Accomodation == 'anytime') ? '<img src="images/icons/door_open.png" alt="'.$member->Accomodation.'" title="'.$member->Accomodation.'">': ''?>
                        <br />
                        <?$name = $member->name(); ?><?=($name == '') ? $member->Occupation : $name;?>

        <?=$words->flushBuffer()?>
                    </div> <!-- username -->
                    <?php if (($logged_member = $this->model->getLoggedInMember()) && $logged_member->hasOldRight(array('Admin' => '', 'SafetyTeam' => '', 'Accepter' => ''))) : ?>
                    <div id='member-status'><?= $words->get('MemberStatus') . ': ' . $member->Status; ?>
                    </div>
                    <?php endif ;?>
                    
                    
    </div> <!-- subcl -->
  </div> <!-- c50l -->
  <div class="c50r" >
    <div class="subcr" >
        
        <?php
          if ($member->Status=="ChoiceInactive") {
                echo "<div class=\"note big\">",$ww->WarningTemporayInactive," </div>\n";
          }
        
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

