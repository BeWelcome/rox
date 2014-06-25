<div class="subcolumns">
    <div class="c66l">
        <div class="subcolumns">
            <div class="c25l">
            <a href="<?php echo $picture_url ?>" id="profile_image"><img src="<?php echo $thumbnail_url ?>" alt="Picture of <?php echo $member->Username ?>" class="framed" height="150" width="150"/></a>
            <div id="profile_image_zoom_content" class="hidden">
                <img src="<?php echo $picture_url?>" alt="Picture of <?php echo $member->Username ?>" />
            </div>
            <script type="text/javascript">
                // Activate FancyZoom for profile picture
                // (not for IE, which don't like FancyZoom)
                if ( typeof FancyZoom == "function" && is_ie === false) {
                    new FancyZoom('profile_image');
                }
            </script>
            </div><!-- profile_pic -->
                                <h1 id="username"><strong><?php echo $teaserusername ?></strong>
                      <?$name = $member->name(); ?><?=($name == '') ? $member->Occupation : $name;?>
                      <?php if (!$this->passedAway) : ?>
                      <?=($member->Accomodation == 'anytime') ? '
                        <img src="images/icons/door_open.png" alt="'.$member->Accomodation.'" title="' . $words->getSilent('CanOfferAccomodation') . '" />': ''?>
                      <?=$words->flushBuffer()?>
                      <?php endif; ?>
                    </h1> <!-- username -->

            <div class="c75l" id="profile-info">
                <div class="subcolumns">
                    <div class="c50l">
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
                <?php if (!empty($logged_member)) : ?>
                    <?php echo $words->get("MemberSince").': ';
                        if (strtotime($member->created) > strtotime('-1 week')){
                            echo $words->get("LastLoginPrivacy");
                        } else {
                            echo $layoutbits->ago(strtotime($member->created));
                        }
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
        </div> <!-- c50l -->
    <div class="c50r" >
        <?php
            if (get_class($this) == 'EditMyProfilePage' || get_class($this) == 'EditProfilePage') $urlstring = 'editmyprofile';
            if (get_class($this) == 'ProfilePage' || get_class($this) == 'MyProfilePage') $urlstring = 'members/'.$member->Username;
            if (isset($urlstring)) {
                require 'profileversion.php';
            }
        ?>
    </div> <!-- c50r -->
  </div> <!-- subcolumns -->
</div> <!-- profile-info -->
</div> <!-- subcolumns -->
</div>
<div class="c33r"> 
    <div class="pull-left">
    <div class="btn-group-vertical profile-actions">
    <a href="<?php echo $messagelinkname?>" role="button" class="btn btn-primary"><?php echo $messagewordsname?></a>
    <a href="<?php echo $commentslinkname?>" role="button" class="btn btn-primary"><?php echo $commentswordsname?></a>
    <a href="<?php echo $relationslinkname?>" role="button" class="btn btn-primary"><?php echo $relationswordsname?></a>
    <a href="<?php echo $mynotelinkname?>" role="button" class="btn btn-primary"><?php echo $mynotewordsname?></a>
    </div>
    </div>
</div>
</div>
</div>
            <ul class="nav nav-tabs" id="profile_linklist">
              <?php

        $active_menu_item = $this->getSubmenuActiveItem();
        foreach ($this->getSubmenuItems() as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            $class = isset($item[3]) ? $item[3] : 'leftpadding';
            if ($name === $active_menu_item) {
                $attributes = ' class="active '.$class.'"';
                $around = '';
            } else {
                $attributes = ' class="'.$class.'"';
                $around = '';
            }

            ?><li id="sub<?=$index ?>" <?=$attributes ?> data-toggle="tab">
              <?=$around?><a style="cursor:pointer;" href="<?=$url ?>"><span><?=$label ?></span></a><?=$around?>
              <?=$words->flushBuffer(); ?>
            </li>
            <?php

        }

            ?></ul>
