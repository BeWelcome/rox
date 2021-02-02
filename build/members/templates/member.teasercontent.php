<div class="row">
  <div class="col-12 col-md-7">
      <?php

      // TODO: move HTML to a template
      // $member = $this->member;
      // $words = $this->getWords();
      $picture_url = 'members/avatar/'.$member->Username;
      ?>
      <div class="p-2 d-md-none"><img src="<?= $picture_url . '/100'?>" alt="profile picture of <?= $member->Username ?>" class="float-left d-md-none"></div>

    <h2 class="m-0 d-inline"><strong>
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
        <span class="h4"><?php $name = $member->name(); ?><?=($name == '') ? (($occupation) ? $occupation : "") : $name;?></span>
    </h2> <!-- username -->

      <h5>
          <?php
          // The "Hong Kong solution": Only display and link country.
          if ($member->region() == '' && $member->city() == $member->country()):
              ?>
              <strong><a href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countrycode()); ?>"><?php echo $member->country(); ?></a></strong>
              <?php
          // In case of missing parent in Geonames DB: Only display city and country. Don't link city.
          elseif ($member->region() == ''):
              ?>
              <strong><?php echo urlencode($member->city()); ?></strong>, <a href="places/<?php
                echo urlencode($member->country()) . "/" . urlencode($member->countryCode()); ?>"><?php echo $member->country(); ?></a>
              <?php
          // For every other city display normal path. Don't show region if it has the same name as city.
          else:
              ?>
              <strong><a href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countrycode())
                  . "/" . urlencode($member->region()) . "/" . urlencode($member->regioncode()) . "/"
                  . urlencode($member->city) . "/" . $member->IdCity; ?>"><?php echo $member->city(); ?></a></strong><?php if ($member->region() != $member->city()): ?>,
              <a href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countryCode()) . "/"
                  . urlencode($member->region()) . "/" . urlencode($member->regioncode()); ?>"><?php echo $member->region(); ?></a><?php endif; ?>,
              <a href="places/<?php echo urlencode($member->country()) . "/" . urlencode($member->countryCode()); ?>"><?php echo $member->country(); ?></a>
              <?php
          endif;
          ?>
      </h5><!-- location -->

      <div class="o-form-group form-inline small"><?php echo $this->statusForm($member); ?></div>

  </div>
      <div class="col-12 col-md-5" style="border-left: 1px solid #ccc;">

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
