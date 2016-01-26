<div id="profile-info">
  <div class="subcolumns">
    <h1 id="username"><strong>
      <?php if ($this->passedAway == 'PassedAway') {
           echo $words->get('ProfileInMemoriam', $member->Username);
      } else {
            echo $member->Username;
      } ?>
      </strong>

      <?$name = $member->name(); ?><?=($name == '') ? $member->Occupation : $name;?>
      <?php if (!$this->passedAway) : ?>
      <!-- Hidden in accordance with trac ticket 1992 until bugs which limit the validity of verification system are resolved.
      <?=($verification_status) ? '
        <a href="verifymembers/verifiersof/'.$member->Username.'">
        <img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'" /></a>': ''?>  -->
      <?=($member->Accomodation == 'anytime') ? '
        <img src="images/icons/door_open.png" alt="'.$member->Accomodation.'" title="' . $words->getSilent('CanOfferAccomodation') . '" />': ''?>
      <?=$words->flushBuffer()?>
      <?php endif; ?>
    </h1> <!-- username -->
    <div class="c50l">
      <div class="subcl">

        <?php echo $this->statusForm($member); ?>
        <div id="navigation-path" >
          <h2>
            <?php
              // The "Hong Kong solution": Only display and link country.
              if ($member->region() == '' && $member->city() == $member->country()):
            ?>
              <strong><a class="" href="places/<?php echo $member->country() . "/" . $member->countrycode(); ?>"><?php echo $member->country(); ?></a></strong>
            <?php
              // In case of missing parent in Geonames DB: Only display city and country. Don't link city.
              elseif ($member->region() == ''):
            ?>
              <strong><?php echo $member->city(); ?></strong>, <a class="" href="places/<?php echo $member->country() . "/" . $member->countryCode(); ?>"><?php echo $member->country(); ?></a>
            <?php
              // For every other city display normal path. Don't show region if it has the same name as city.
              else:
            ?>
              <strong><a class="" href="places/<?php echo $member->country() . "/" . $member->countrycode() . "/" . $member->region() . "/" . $member->regioncode() . "/" . $member->city . "/" . $member->IdCity; ?>">              <?php echo $member->city(); ?></a></strong><?php if ($member->region() != $member->city()): ?>,
              <a class="" href="places/<?php echo $member->country() . "/" . $member->countryCode() . "/" . $member->region() . "/" . $member->regioncode(); ?>"><?php echo $member->region(); ?></a><?php endif; ?>,
              <a class="" href="places/<?php echo $member->country() . "/" . $member->countryCode(); ?>"><?php echo $member->country(); ?></a>
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
            <?php if ($this->loggedInMember) : ?>
                <?php echo $words->get("MemberSince"). ': ';
                    if (strtotime($member->created) > strtotime('-1 week')){
                        echo $words->get("LastLoginPrivacy");
                    } else {
                        echo $layoutbits->ago(strtotime($member->created));
                    }
                    echo  $this->memberSinceDate($member);
                    echo '<br>'.$words->get("LastLogin").': ';
                    if (strtotime($member->LastLogin) > strtotime('-1 week')){
                        echo $words->get("LastLoginPrivacy");
                    } else {
                        echo $layoutbits->ago(strtotime($member->LastLogin));
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

