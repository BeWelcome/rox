<?php
// Error shortcuts
if (in_array('SignupErrorInvalidBirthDate', $vars['errors'])) {
  $errorBirthDate = true;
}
if (in_array('SignupErrorInvalidFirstName', $vars['errors'])) {
  $errorFirstName = true;
}
if (in_array('SignupErrorInvalidLastName', $vars['errors'])) {
  $errorLastName = true;
}
if (in_array('SignupErrorInvalidStreet', $vars['errors'])) {
  $errorStreet = true;
}
if (in_array('SignupErrorInvalidHouseNumber', $vars['errors'])) {
  $errorHouseNumber = true;
}
if (in_array('SignupErrorInvalidZip', $vars['errors'])) {
  $errorZip = true;
}
if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
  $errorEmail = true;
}


?>
          <fieldset id="profilesummary">
            <legend><?=$words->get('ProfileSummary')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="25%" ></col>
                <col width="15%" ></col>
                <col width="35%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePicture')?>:<br/><img src="members/avatar/<?=$member->Username?>?xs" title="Current picture" alt="Current picture" style="padding: 1em"/></td>
                  <td colspan="3" >
                    <label for="profile_picture"><?= $words->get('uploadselectpicture'); ?>
                    <div class="small"><?= $words->get('Profile_UploadWarning', sprintf("%.1f MB", PFunctions::returnBytes(ini_get('upload_max_filesize')) / 1048576)); ?></div></label><br /><input id="profile_picture" name="profile_picture" type="file" />
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileSummary')?>:</td>
                  <td colspan="3" >
                    <textarea name="ProfileSummary" id="ProfileSummary" class="long" cols="50"  rows="6" ><?php echo htmlentities($vars['ProfileSummary'], ENT_COMPAT, 'UTF-8'); ?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><strong><?=$words->get('SignupBirthDate')?></strong>: *</td>
                  <td colspan="2" >
                    <input<?php if (isset($errorBirthDate)) { ?> class="error-input-text"<?php } ?> type='text' value="<?=$vars['BirthDate']?>" name="BirthDate"/>
                    <?php if (isset($errorBirthDate)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidBirthDate')?></div>
                    <?php } ?>
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>
                  </tr>
                  <tr align="left" >
                  <td class="label"><?= $words->get('Age')?>:</td>
                  <td colspan="3">
                        <input name="HideBirthDate" value="Yes" type="checkbox" <?= ($vars['HideBirthDate'] == 'Yes') ? 'checked="checked"' : '' ?> /> <?= $words->get('Hidden');?>
                        <div class="small"><?= $words->get('HiddenAgeInfo'); ?></div>
                  </td>
                </tr>

                <tr align='left'>
                    <td class='label'><?= $words->get('Gender'); ?>:</td>
                    <td colspan='2' >
                        <input class="radio" type="radio" id="genderF" name="gender" value="female" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><label for='genderF'> <?= $words->get('female'); ?></label>&nbsp;&nbsp;
                        <input class="radio" type="radio" id='genderM' name="gender" value="male" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : '');?>/><label for='genderM'> <?= $words->get('male'); ?></label>&nbsp;&nbsp;
                        <input class="radio" type="radio" id='genderX' name="gender" value="other" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'other') ? ' checked="checked"' : '');?>/><label for='genderX'> <?= $words->get('GenderOther'); ?></label></td>
                        
                     <td><input name="HideGender" value="Yes" type="checkbox" id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : '');?>/><label for='HideGender'> <?= $words->get("Hidden");?></label></td>
                    <?php
                        if (in_array('SignupErrorInvalidGender', $vars['errors']))
                        {
                            echo '<div class="error">'.$words->get('SignupErrorInvalidGender').'</div>';
                        }
                    ?>
                </tr>

                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOccupation')?>:</td>
                  <td colspan="2" >
                    <input type="text"  name="Occupation" value="<?php echo htmlentities($vars['Occupation'], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileLanguagesSpoken')?>:</td>
                  <td colspan="3" >
                    <table>
                      <tbody>
                      <?php
                        $lang_ids = array();
                        for ($ii = 0; $ii < count($vars['languages_selected']); $ii++)
                        {
                            $lang_ids[] = $vars['languages_selected'][$ii]->IdLanguage;
                            echo <<<HTML
                        <tr>
                        <td>

                            <input type='hidden' name='memberslanguages[]' value='{$vars['languages_selected'][$ii]->IdLanguage}'/>
                            <input type='text' disabled='disabled' value='{$vars['languages_selected'][$ii]->Name}'/>
                          </td>
                          <td>
                              <select name="memberslanguageslevel[]" >
HTML;
                                for ($jj = 0; $jj < count($vars['language_levels']); $jj++)
                                {
                                    $selected = $vars['language_levels'][$jj] == $vars['languages_selected'][$ii]->Level? ' selected="selected"': '';
                                    echo <<<HTML
                                    <option value='{$vars['language_levels'][$jj]}'{$selected}>{$words->get("LanguageLevel_" . $vars['language_levels'][$jj])}</option>
HTML;
                                }
                            echo <<<HTML
                            </select>
                          </td>
                          <td><a href='#' class='remove_lang'>{$words->get('RemoveLanguage')}</a>
                          </td>
                        </tr>
HTML;
                        }
                      echo <<<HTML
                        <tr id="lang1">
                          <td><select class='lang_selector' name="memberslanguages[]" >
                          <option selected="selected">-{$words->get("ChooseNewLanguage")}-</option>
                          <optgroup label="{$words->getSilent('SpokenLanguages')}">
HTML;
                                for ($jj = 0; $jj < count($vars['languages_all_spoken']); $jj++)
                                {
                                    if (in_array($vars['languages_all_spoken'][$jj]->id, $lang_ids))
                                    {
                                        continue;
                                    }
                                    echo <<<HTML
                                    <option value="{$vars['languages_all_spoken'][$jj]->id}">{$vars['languages_all_spoken'][$jj]->TranslatedName} ({$vars['languages_all_spoken'][$jj]->Name})</option>
HTML;
                                }
                            echo <<<HTML
                            </optgroup>
                          <optgroup label="{$words->getSilent('SignedLanguages')}">
HTML;
                                for ($jj = 0; $jj < count($vars['languages_all_signed']); $jj++)
                                {
                                    if (in_array($vars['languages_all_signed'][$jj]->id, $lang_ids))
                                    {
                                        continue;
                                    }
                                    echo <<<HTML
                                    <option value="{$vars['languages_all_signed'][$jj]->id}">{$vars['languages_all_signed'][$jj]->TranslatedName}</option>
HTML;
                                }
                            echo <<<HTML
                            </optgroup>
                          </select>
                          </td>
                          <td>
                            <select name="memberslanguageslevel[]" >
HTML;
                                for ($jj = 0; $jj < count($vars['language_levels']); $jj++)
                                {
                                    echo <<<HTML
                                    <option value="{$vars['language_levels'][$jj]}">{$words->get("LanguageLevel_" . $vars['language_levels'][$jj])}</option>
HTML;
                                }
                            echo <<<HTML
                            </select>
                          </td>
                          <td>&nbsp;</td>
                        </tr>
                      </tbody>
                      </table>
                    <input type="button" id="langbutton" class="button" name="addlang" value="{$words->get('AddLanguage')}" />
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="contactinfo">
            <legend>{$words->get('ContactInfo')}</legend>
            <table border="0" class="full" >
              <colgroup>
                <col width="25%" ></col>
                <col width="25%" ></col>
                <col width="25%" ></col>
                <col width="25%" ></col>
              </colgroup>
              <tbody>
HTML;
    if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data
?>
                <tr align="left" >
                  <td class="label" ><strong><?=$words->get('FirstName')?></strong>: *</td>
                  <td>
                    <input class="<?php if (isset($errorFirstName)) { ?>error-input-text<?php } ?>" type="text" name="FirstName" value="<?php echo htmlentities($vars['FirstName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <?php if (isset($errorFirstName)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidFirstName')?></div>
                    <?php } ?>
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_FirstName"
                    <?php if ($vars['IsHidden_FirstName'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SecondName')?>:</td>
                  <td><input type="text" name="SecondName" value="<?php echo htmlentities($vars['SecondName'], ENT_COMPAT, 'UTF-8'); ?>"/></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_SecondName"
                    <?php if ($vars['IsHidden_SecondName'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><strong><?=$words->get('LastName')?></strong>: *</td>
                  <td>
                    <input class="<?php if (isset($errorLastName)) { ?>error-input-text<?php } ?>" type="text" name="LastName" value="<?php echo htmlentities($vars['LastName'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <?php if (isset($errorLastName)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidLastName')?></div>
                    <?php } ?>
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_LastName"
                    <?php if ($vars['IsHidden_LastName'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Street')?>:</td>
                  <td>
                    <input class="<?php if (isset($errorStreet)) { ?>error-input-text<?php } ?>" type="text" name="Street" id="Street" value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <?php if (isset($errorStreet)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidStreet')?></div>
                    <?php } ?>
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Address"
                    <?php if ($vars['IsHidden_Address'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('HouseNumber')?>:</td>
                  <td>
                    <input class="short<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>" type="text" name="HouseNumber" id="HouseNumber" value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                    <?php if (isset($errorHouseNumber)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidHouseNumber')?></div>
                    <?php } ?>
                  </td>
                  <td>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Post code')?>:</td>
                  <td>
                    <input class="short <?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text" name="Zip" value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                    <?php if (isset($errorZip)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidZip')?></div>
                    <?php } ?>
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Zip"
                    <?php if ($vars['IsHidden_Zip'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                  <td>
                    &nbsp;
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Location')?>:</td>
                  <td >
                    <?=$member->city?>
                    <br />
                    <?=$member->region?>
                    <br />
                    <?=$member->country?>
                    <br />
                  </td>
                  <td>
                    <a href="setlocation" ><?=$words->get('UpdateMyLocation')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHomePhoneNumber')?>:</td>
                  <td>
                    <input type="text" size="25" name="HomePhoneNumber"  value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_HomePhoneNumber"
                    <?php if ($vars['IsHidden_HomePhoneNumber'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileCellPhoneNumber')?>:</td>
                  <td>
                    <input type="text" size="25" name="CellPhoneNumber" value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_CellPhoneNumber"
                    <?php if ($vars['IsHidden_CellPhoneNumber'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileWorkPhoneNumber')?>:</td>
                  <td>
                    <input type="text" size="25"  name="WorkPhoneNumber" value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_WorkPhoneNumber"
                    <?php if ($vars['IsHidden_WorkPhoneNumber'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><strong><?=$words->get('SignupEmail')?></strong>: *</td>
                  <td>
                    <input class="<?php if (isset($errorEmail)) { ?>error-input-text<?php } ?>" type="text" size="25" name="Email" value="<?=str_replace('%40', '@', $vars['Email'])?>" />
                    <?php if (isset($errorEmail)) { ?>
                      <div class="error-caption"><?=$words->get('SignupErrorInvalidEmail')?></div>
                    <?php } ?>
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>

                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Website')?>:</td>
                  <td>
                    <input type="text" size="25"  name="WebSite"  value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                </tr>

                <?php
                if(isset($vars['messengers'])) {
                    foreach($vars['messengers'] as $me) {
                    $val = 'chat_' . $me['network_raw'];
                ?>
                <tr align="left" >
                  <td class="label" ><?=$me["network"]?>
                  <?="<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$me["image"]."' width='16' height='16' title='".$me["network"]."' alt='".$me["network"]."' />"?>
                  </td>
                  <td>
                    <input type="text" size="25" name="<?=$val?>" value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>" />
                  </td>
                  <td>
                  </td>
                </tr>
                <?php
                    }
                }

    }
?>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="profileaccommodation">
            <legend><?=$words->get('ProfileAccommodation')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAccommodation')?></td>
                  <td>
                    <select name="Accomodation" >
                    <?php
                    $syshcvol = PVars::getObj('syshcvol');
                    $tt = $syshcvol->Accomodation;
                    $max = count($tt);
                    for ($ii = 0; $ii < $max; $ii++) {
                        echo "<option value=\"" . $tt[$ii] . "\"";
                        if ($tt[$ii] == $vars['Accomodation'])
                            echo " selected=\"selected\"";
                        echo ">", $words->get("Accomodation_" . $tt[$ii]), "</option>\n";
                    }
                    ?>
                    </select>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileNumberOfGuests')?>:</td>
                  <td>
                  <select name="MaxGuest" >
                  <?php
                  for ($ii = 1; $ii < 30; $ii++) {
                  ?>
                    <option value="<?=$ii?>" <?=($vars['MaxGuest'] == $ii) ? 'selected="selected"':''?>><?=$ii?></option>
                  <?php
                  }
                  ?>
                  </select>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMaxLenghtOfStay')?>:</td>
                  <td colspan="2" >
                    <textarea name="MaxLenghtOfStay" class="long"  cols="50" rows="4" ><?=$vars['MaxLenghtOfStay']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileILiveWith')?>:</td>
                  <td colspan="2" >
                    <textarea name="ILiveWith" class="long"  cols="50" rows="4" ><?=$vars['ILiveWith']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePleaseBring')?>:</td>
                  <td colspan="2" >
                    <textarea name="PleaseBring" class="long"  cols="50" rows="4" ><?=$vars['PleaseBring']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferGuests')?>:</td>
                  <td colspan="2" >
                    <textarea name="OfferGuests" class="long"  cols="50" rows="4" ><?=$vars['OfferGuests']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferHosts')?>:</td>
                  <td colspan="2" >
                    <textarea name="OfferHosts" class="long"  cols="50" rows="4" ><?=$vars['OfferHosts']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ICanAlsoOffer')?>:</td>
                  <td colspan="2" >
                    <ul>
                    <?php
                        $max = count($vars['TabTypicOffer']);
                        for ($ii = 0; $ii < $max; $ii++) {
                            echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabTypicOffer[$ii] . "\" ";
                            if (strpos($member->TypicOffer, $member->TabTypicOffer[$ii]) !== false)
                                echo "checked=\"checked\"";
                            echo " />";
                            echo "&nbsp;&nbsp;", $words->get("TypicOffer_" . $member->TabTypicOffer[$ii]), "</li>\n";
                        }
                    ?>
                    </ul>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePublicTransport')?>:</td>
                  <td colspan="2" >
                    <textarea name="PublicTransport" class="long"  cols="50" rows="4" ><?=$vars['PublicTransport']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileRestrictionForGuest')?>:</td>
                  <td colspan="2" >
                    <ul>
                    <?php
                        $max = count($member->TabRestrictions);
                        for ($ii = 0; $ii < $max; $ii++) {
                            echo "<li><input type=\"checkbox\" name=\"check_" . $member->TabRestrictions[$ii] . "\" ";
                            if (strpos($member->Restrictions, $member->TabRestrictions[$ii]) !== false)
                                echo "checked=\"checked\"";
                            echo " />";
                            echo "&nbsp;&nbsp;", $words->get("Restriction_" . $member->TabRestrictions[$ii]), "</li>\n";
                        }
                    ?>
                    </ul>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOtherRestrictions')?>:</td>
                  <td colspan="2" >
                    <textarea name="OtherRestrictions" class="long" cols="50" rows="4" ><?=$vars['OtherRestrictions']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAdditionalAccomodationInfo')?>:</td>
                  <td colspan="2" >
                    <textarea name="AdditionalAccomodationInfo" class="long" cols="50"  rows="4" ><?=$vars['AdditionalAccomodationInfo']?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="profileinterests">
            <legend><?=$words->get('ProfileInterests')?></legend>
            <table border="0" class="full" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHobbies')?>:</td>
                  <td>
                    <textarea name="Hobbies" class="long" cols="50"  rows="4" ><?=$vars['Hobbies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileBooks')?>:</td>
                  <td>
                    <textarea name="Books" class="long" cols="50"  rows="4" ><?=$vars['Books']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMusic')?>:</td>
                  <td>
                    <textarea name="Music" class="long" cols="50"  rows="4" ><?=$vars['Music']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMovies')?>:</td>
                  <td>
                    <textarea name="Movies" class="long" cols="50"  rows="4" ><?=$vars['Movies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOrganizations')?>:</td>
                  <td>
                    <textarea name="Organizations" class="long" cols="50"  rows="4" ><?=$vars['Organizations']?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
            <h3><?=$words->get('ProfileTravelExperience')?></h3>
            <table border="0" class="full" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePastTrips')?>:</td>
                  <td>
                    <textarea name="PastTrips" class="long" cols="50"  rows="4" ><?=$vars['PastTrips']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePlannedTrips')?>:</td>
                  <td>
                    <textarea name="PlannedTrips" class="long" cols="50"  rows="4" ><?=$vars['PlannedTrips']?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>

          <?php if (!empty($vars['Relations']) && 1 == 1) : // Disabled ?>
          <fieldset id="specialrelations">
            <legend><?=$words->get('MyRelations')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="60%" ></col>
                <col width="15%" ></col>
              </colgroup>
              <tbody>
                <?php
                $Relations=$vars['Relations'];
                foreach($Relations as $Relation) {
                    $comment = $words->mInTrad($Relation->Comment, $profile_language);

                    // Hack to filter out accidental '0' or '123456' comments that were saved
                    // by users while relation comment update form was buggy (see #1580)
                    if (is_numeric($comment)) {
                        $comment = '';
                    }
                ?>
                <tr align="left" >
                  <td class="label" >
                  <?php
                  if ($Relation->Confirmed=='Yes') {
                    echo "<b>",$Relation->Username,"</b>" ;
                  }
                  else {
                    echo $Relation->Username ;
                  }
                  ?><br />
                    <img class="framed"  src="members/avatar/<?=$Relation->Username?>?xs"  height="50px"  width="50px"  alt="Profile" />
                  </td>
                  <td>
                    <?php 
                    echo "<textarea cols=\"40\" rows=\"6\" name=\"", "RelationComment_" . $Relation->id, "\">";
                    echo $comment;
                    echo "</textarea>\n";
                    ?>
                  </td>
                  <td>
                    <a href="/members/<?php echo $member->Username; ?>/relations/delete/<?php echo $Relation->id; ?>?redirect=editmyprofile#!specialrelations" onclick="return confirm('<?php echo $words->get('Relation_delete_confirmation'); ?>');"><?php echo $words->getFormatted("delrelation", $Relation->Username); ?></a>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </fieldset>
          <?php endif; ?>

<?php if (1 == 0) : ?>
          <? // Groups (restored by JeanYves) -- disabled ?>
            <?php
            $my_groups =$vars['Groups']; 
            // $my_groups=array() ; // uncomment this line if you don't want to have groups inside edit profile
            if (!empty($my_groups)) {
            ?>
          <fieldset>
            <legend class="icon groups22" ><?=$words->get('MyGroups')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <?php
                for ($i = 0; $i < count($my_groups) ; $i++) {
                    $group=$my_groups[$i] ;
                    $group_img = ((strlen($my_groups[$i]->Picture) > 0) ? "groups/thumbimg/{$group->getPKValue()}" : 'images/icons/group.png' );
                    $group_id = $group->getPKValue() ;
                    $group_name_translated = $words->get("Group_".$group->Name);
                    $group_comment_translated = htmlspecialchars($words->mInTrad($member->getGroupMembership($group)->Comment,$profile_language), ENT_QUOTES);
                ?>
                <tr align="left" >
                  <td class="label" ><a href="groups/<?=$group_id?>" ><?php echo $group_name_translated," ",$group->Location ;?></a></td>
                  <td colspan="2" >
                    <input type="hidden" Name="Group_id<?=$group->id?>" value="<?=$group->id?>">
                    <textarea cols="50"  rows="6"  name="GroupMembership_<?=$member->getGroupMembership($group)->id?>" ><?=$group_comment_translated?></textarea>
                <?php
                /*
                if ($Rights->hasRight("Beta","GroupMessage")) {
                       echo "<br /> BETA ";
                       echo "                <input type=\"checkbox\" name=\"AcceptMessage_".$group->od."\" ";
                       if ($group->IacceptMassMailFromThisGroup=="yes") echo "checked";
                       echo " />\n";
                       echo $words->get('AcceptMessageFromThisGroup');
                    }
                    else {
                       echo "<input type=\"hidden\" name=\"AcceptMessage_".$group->od."\" value=\"".$group->IacceptMassMailFromThisGroup."\" />\n";
                    }
                */
                ?>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </fieldset>
            <?php
            } // end if (!empty($my_groups) 
            ?>
          <?php endif; ?> 
          <table>
            <tbody>
              <tr>
                <td colspan="3"  align="center" >
                  <input type="submit"  id="submit"  name="submit"  value="<?=$words->get('Save Profile')?>" />
                </td>
              </tr>
            </tbody>
          </table>
