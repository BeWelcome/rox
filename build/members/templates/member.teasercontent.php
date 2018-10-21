<div class="row">
  <div class="col-12 col-md-8">
    <h1 class="h2 m-0"><strong>
      <?php if ($this->passedAway == 'PassedAway') {
           echo $words->get('ProfileInMemoriam', $member->Username);
      } else {
            echo $member->Username;
      } ?>
      </strong>

      <?php if (!$this->passedAway) : ?>

          <?php
          $icons = array();
          if (strstr($member->TypicOffer, "CanHostWeelChair"))
          {
              $icons[] = '<img src="images/icons/wheelchairblue.png" ' .
                  'class="mb-2" ' .
                  'alt="' . $words->getSilent('wheelchair') . '" ' .
                  'title="' . $words->getSilent('CanHostWheelChairYes') . '" />';
          }

          $icons[] = '<img src="images/icons/' . $member->Accomodation . '.png"' .
              ' class="mb-2"' .
              ' alt="' . $words->getSilent($member->Accomodation) .'"' .
              ' title="' . $words->getSilent('CanOffer' . $member->Accomodation) . '" />';

          for($ii=0; $ii < count($icons); $ii++)
          {
              echo $icons[$ii];
          }
          ?>
            <?=$words->flushBuffer()?>
      <?php endif; ?>
        <br>
        <?$name = $member->name(); ?><?=($name == '') ? (($member->Occupation) ? $member->Occupation : "") : $name;?>
    </h1> <!-- username -->

      <h4>
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
      </h4><!-- location -->
  </div>
      <div class="col-12 col-md-4" style="border-left: 1px solid #ccc;">
          <div class="form-group form-inline small"><?php echo $this->statusForm($member); ?></div>

              <?php if($occupation != null){
                  echo '<p class="h5">' . $occupation . '</p>';
              } ?><!-- occupation -->

              <p class="m-0">
            <?php
                echo $agestr;
                $strGender = MOD_layoutbits::getGenderTranslated($member->Gender, $member->HideGender, true);
                if (!empty($strGender)) {
                    echo ', ' . $strGender;
                }
                echo '<br>';
             ?>
            <?php if ($this->loggedInMember) : ?>
                <?php echo '<span class="font-weight-bold">' . $words->get("MemberSince") . ': </span>';
                    if (strtotime($member->created) > strtotime('-1 week')){
                        echo $words->get("LastLoginPrivacy");
                    } else {
                        echo $layoutbits->ago(strtotime($member->created));
                    }
                    echo  $this->memberSinceDate($member);
                    echo '<br><span class="font-weight-bold">' . $words->get("LastLogin") . ': </span>';
                    if (strtotime($member->LastLogin) > strtotime('-1 week')){
                        echo $words->get("LastLoginPrivacy");
                    } else {
                        echo $layoutbits->ago(strtotime($member->LastLogin));
                    }
                    ?>
            <?php endif; ?>
          </p>
      </div>
    </div> <!-- profile header -->

        <?php
            if (get_class($this) == 'EditMyProfilePage' || get_class($this) == 'EditProfilePage') $urlstring = 'editmyprofile';
            if (get_class($this) == 'ProfilePage' || get_class($this) == 'MyProfilePage') $urlstring = 'members/'.$member->Username;
            if (isset($urlstring)) { ?>
            <div class="row">
                <div class="col-12" >
                    <?php require 'profileversion.php'; ?>
                </div>
            </div>
            <?php } ?>
