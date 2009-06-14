          <fieldset id="profilesummary">
            <legend><?=$words->getInLang('ProfileSummary', $profile_language)?></legend>
            <a name="profilepic" />
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->getInLang('ProfilePicture', $profile_language)?>:<br/><img src="members/avatar/<?=$member->Username?>?xs" title="Current picture" alt="Current picture" style="padding: 1em"/></td>
                  <td>
                    <label for="profile_picture"><?= $words->get('uploadselectpicture'); ?></label><br /><input id="profile_picture" name="profile_picture" type="file" />
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->getInLang('ProfileSummary', $profile_language)?>:</td>
                  <td>
                    <textarea name="ProfileSummary" id="ProfileSummary" class="long" cols="50"  rows="6" ><?=$vars['ProfileSummary']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupBirthDate')?>:</td>
                  <td colspan="2" >
                        <input type='text' value='<?=$vars['BirthDate']?>' name='BirthDate'/>
                        &nbsp;&nbsp;&nbsp;&nbsp; 
                        <input name="HideBirthDate" value="Yes" type="checkbox"
                        <?php
                        if ($vars['HideBirthDate'] == "Yes")
                            echo ' checked="checked"';
                        echo ' /> ', $words->get("Hidden");
                        if (in_array('SignupErrorInvalidBirthDate', $vars['errors']))
                        {
                            echo '<div class="error">'.$words->get('SignupErrorInvalidBirthDate').'</div>';
                        }
                    ?>
                  </td>
                </tr>

                <tr align='left'>
                    <td class='label'><?= $words->get('Gender'); ?></td>
                    <td colspan='2'>
                        <input class="radio" type="radio" id="genderF" name="gender" value="female" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'female') ? ' checked="checked"' : ''); ?>/><label for='genderF'><?= $words->get('female'); ?></label>
                    <input class="radio" type="radio" id='genderM' name="gender" value="male" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'male') ? ' checked="checked"' : '');?>/><label for='genderM'><?= $words->get('male'); ?></label>
                    <input class="radio" type="radio" id='genderX' name="gender" value="IDontTell" <?= ((isset($vars['Gender']) && $vars['Gender'] == 'IDontTell') ? ' checked="checked"' : '');?>/><label for='genderX'><?= $words->get('IDontTell'); ?></label>
                        <input name="HideGender" value="Yes" type="checkbox" id='HideGender' <?= ((isset($vars['HideGender']) && $vars['HideGender'] == "Yes") ? ' checked="checked"' : '');?>/><label for='HideGender'><?= $words->get("Hidden");?></label>
                    <?php
                        if (in_array('SignupErrorInvalidGender', $vars['errors']))
                        {
                            echo '<div class="error">'.$words->get('SignupErrorInvalidGender').'</div>';
                        }
                    ?>
                </tr>

                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOccupation')?>:</td>
                  <td>
                    <input type="text"  name="Occupation" value="<?=$vars['Occupation']?>" />
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileLanguagesSpoken')?>:</td>
                  <td>
                    <table>
                      <tbody>
                      <?php
                        $tt = $vars['language_levels'];
                        $maxtt = count($tt);
                        $ll = $vars['languages_all'];
                        $maxll = count($ll);
                        $max = count($vars['languages_selected']);
                        for ($ii = 0; $ii < $max; $ii++) {
                        ?>
                        <tr>
                          <td>
                            <select name="memberslanguages[]" >
                            <option>-<?=$words->get("ChooseNewLanguage")?>-</option>
                            <?php
                                for ($jj = 0; $jj < $maxll; $jj++) {
                                    echo "<option value=\"" . $ll[$jj]->id . "\"";
                                    if ($ll[$jj]->id == $vars['languages_selected'][$ii]->IdLanguage)
                                        echo " selected=\"selected\"";
                                    echo ">".$ll[$jj]->Name."</option>\n";
                                }
                            ?>
                            </select>
                          </td>
                          <td>
                            <select name="memberslanguageslevel[]" >
                            <?php
                                for ($jj = 0; $jj < $maxtt; $jj++) {
                                    echo "                              <option value=\"" . $tt[$jj] . "\"";
                                    if ($tt[$jj] == $vars['languages_selected'][$ii]->Level)
                                        echo " selected=\"selected\"";
                                    echo ">", $words->get("LanguageLevel_" . $tt[$jj]), "</option>\n";
                                }
                            ?>
                            </select>
                          </td>
                        </tr>
                      <?php
                        }
                      ?>
                        <tr id="lang1">
                          <td>
                            <select name="memberslanguages[]" >
                            <option selected="selected">-<?=$words->get("ChooseNewLanguage")?>-</option>
                            <?php
                                for ($jj = 0; $jj < $maxll; $jj++) {
                                    echo "<option value=\"" . $ll[$jj]->id . "\"";
                                    echo ">".$ll[$jj]->Name."</option>\n";
                                }
                            ?>
                            </select>
                          </td>
                          <td>
                            <select name="memberslanguageslevel[]" >
                            <?php
                                for ($jj = 0; $jj < $maxtt; $jj++) {
                                    echo "                              <option value=\"" . $tt[$jj] . "\"";
                                    echo ">", $words->get("LanguageLevel_" . $tt[$jj]), "</option>\n";
                                }
                            ?>
                            </select>
                          </td>
                        </tr>
                      </tbody>
                      </table>
                    <input type="button" id="langbutton" class="button" name="addlang" value="Add Language" />
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="contactinfo">
            <legend><?=$words->get('ContactInfo')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="25%" ></col>
                <col width="15%" ></col>
                <col width="35%" ></col>
              </colgroup>
              <tbody>
<?php
    if (!$CanTranslate) { // member translator is not allowed to update crypted data
?>
                <tr align="left" >
                  <td class="label" ><?=$words->get('FirstName')?>:</td>
                  <td><?=$vars['FirstName']?></td>
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
                  <td><?=$vars['SecondName']?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_SecondName"
                    <?php if ($vars['IsHidden_SecondName'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('LastName')?>:</td>
                  <td><?=$vars['LastName']?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_LastName"
                    <?php if ($vars['IsHidden_LastName'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Street')?> / <?=$words->get('HouseNumber')?>:</td>
                  <td>
                      <input type='text' name='Street' id='Street' value='<?=$vars['Street']?>'/>
                      <input type='text' name='HouseNumber' id='HouseNumber' value='<?=$vars['HouseNumber']?>' size="5" />     
                    <?php
                        if (in_array('SignupErrorInvalidAddress', $vars['errors']))
                        {
                            echo '<div class="error">'.$words->get('SignupErrorInvalidAddress').'</div>';
                        }
                    ?>
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
                  <td class="label" >Zip:</td>
                  <td><input type='text' name='Zip' value='<?=$vars['Zip']?>'/></td>
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
                  <td colspan="2" >
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
                    <input type="text" size="25" name="HomePhoneNumber"  value="<?=$vars['HomePhoneNumber']?>" />
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
                    <input type="text" size="25" name="CellPhoneNumber" value="<?=$vars['CellPhoneNumber']?>" />
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
                    <input type="text" size="25"  name="WorkPhoneNumber" value="<?=$vars['WorkPhoneNumber']?>" />
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
                  <td class="label" ><?=$words->get('SignupEmail')?>:</td>
                  <td>
                    <input type="text" size="25" name="Email"  value="<?=str_replace('%40', '@', $vars['Email'])?>" />
                    <?php
                      if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
                          echo '<div class="error">'.$words->get('SignupErrorInvalidEmail').'</div>';
                      }
                    ?>
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>

                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Website')?>:</td>
                  <td>
                    <input type="text" size="25"  name="WebSite"  value="<?=$vars['WebSite']?>" />
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
                    <input type="text" size="25" name="<?=$val?>" value="<?=$me["address"]?>" />
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
            <table border="0" >
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
          </fieldset>
          <fieldset id="profiletravelexperience">
            <legend><?=$words->get('ProfileTravelExperience')?></legend>
            <table border="0" >
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
          <?php
          // Groups are out of editmyprofile now!
          /*
          <fieldset>
            <legend class="icon groups22" ><?=$words->get('MyGroups')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <?php
                foreach($groups as $group) {
                    $group_id = $group->IdGroup;
                    $group_name_translated = $words->getInLang($group->Name, $profile_language_code);
                    $group_comment_translated = $member->get_trad_by_tradid($group->Comment, $profile_language);
                ?>
                <tr align="left" >
                  <td class="label" ><a href="groups/<?=$group_id?>" ><?php echo $group_name_translated," ",$group->Location ;?></a></td>
                  <td colspan="2" >
                    <textarea cols="50"  rows="6"  name="Group_<?=$group->Name?>" ><?=$group_comment_translated?></textarea>
                <?php
                if ($Rights->hasRight("Beta","GroupMessage")) {
                       echo "<br /> BETA ";
                       echo "                <input type=\"checkbox\" name=\"AcceptMessage_".$group->Name."\" ";
                       if ($group->IacceptMassMailFromThisGroup=="yes") echo "checked";
                       echo " />\n";
                       echo $words->get('AcceptMessageFromThisGroup');
                    }
                    else {
                       echo "<input type=\"hidden\" name=\"AcceptMessage_".$group->Name."\" value=\"".$group->IacceptMassMailFromThisGroup."\" />\n";
                    }
                ?>
                  </td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </fieldset>
           */ ?>
          <table>
            <tbody>
              <tr>
                <td colspan="3"  align="center" >
                  <input type="submit"  id="submit"  name="submit"  value="<?=$words->get('SubmitForm')?>" >
                </td>
              </tr>
            </tbody>
          </table>
