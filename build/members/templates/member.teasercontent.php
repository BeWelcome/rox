<div id="profile-info">
  <div class="subcolumns">
    <h1 id="username">
      <strong><?=$member->Username ?></strong>
      <?$name = $member->name(); ?><?=($name == '') ? $member->Occupation : $name;?>
      <?=($verification_status) ? '
        <a href="verifymembers/verifiersof/'.$member->Username.'">
          <img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'" /></a>': ''?>
      <?=($member->Accomodation == 'anytime') ? '
        <img src="images/icons/door_open.png" alt="'.$member->Accomodation.'" title="' . $words->getSilent('CanOfferAccomodation') . '" />': ''?>
      <?=$words->flushBuffer()?>
    </h1> <!-- username -->
    <div class="c50l">
      <div class="subcl">
        
        <?php if (($logged_member = $this->model->getLoggedInMember()) && $logged_member->hasOldRight(array('Admin' => '', 'SafetyTeam' => '', 'Accepter' => ''))) : ?>
        <div id='member-status'>
          <?php  if ($member->Status!="Active") {
                    echo $words->get('MemberStatus') . ': ' . $member->Status; 
                }; ?>
        </div>
        <?php endif ;?>
        <div id="navigation-path" >
          <h2>
            <?php
              // The "Hong Kong solution": Only display and link country.
              if ($member->region() == '' && $member->city() == $member->country()):
            ?>
              <strong><a class="" href="places/<?php echo $member->countryCode(); ?>"><?php echo $member->country(); ?></a></strong>
            <?php
              // In case of missing parent in Geonames DB: Only display city and country. Don't link city.
              elseif ($member->region() == ''):
            ?>
              <strong><?php echo $member->city(); ?></strong>, <a class="" href="places/<?php echo $member->countryCode(); ?>"><?php echo $member->country(); ?></a>
            <?php
              // For every other city display normal path. Don't show region if it has the same name as city.
              else:
            ?>
              <strong><a class="" href="places/<?php echo $member->countryCode() . "/" . $member->region() . "/" . $member->city(); ?>"><?php echo $member->city(); ?></a></strong><?php if ($member->region() != $member->city()): ?>, <a class="" href="places/<?php echo $member->countryCode() . "/" . $member->region(); ?>"><?php echo $member->region(); ?></a><?php endif; ?>, <a class="" href="places/<?php echo $member->countryCode(); ?>"><?php echo $member->country(); ?></a>
            <?php
              endif;
            ?>
          </h2>
          <p class="grey">
            <?=$agestr ?><?php if($occupation != null) echo ", ".$occupation; ?><br />
             <?php
                $strGender = MOD_layoutbits::getGenderTranslated($member->Gender, $member->HideGender, true);
                if (!empty($strGender)) {
                    echo $strGender . "<br />";
                }
             ?>
            <?php if (!empty($logged_member)) : ?>
                <?php echo $words->get("MemberSince");?>: <?php echo $layoutbits->ago(strtotime($member->created));?> <br />
                <?php
                    if (strtotime($member->LastLogin) > strtotime('-1 week'))
                    {
                        echo $words->get("LastLogin")?>: <?php echo $words->get("LastLoginPrivacy");
                    }
                    else
                    {
                        echo $words->get("LastLogin")?>: <?php echo $layoutbits->ago(strtotime($member->LastLogin));
                    }
                    ?>
            <?php endif; ?>
          </p>
        </div> <!-- navigation-path -->
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

