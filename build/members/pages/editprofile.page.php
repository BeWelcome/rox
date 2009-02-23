<?php


class EditProfilePage extends MemberPage
{
    protected function leftSidebar()
    {
        $member = $this->member;
        $words = $this->getWords();
        ?>
          <h3><?=$words->get('actions')?></h3>
          <ul class="linklist" >
            <li class="icon contactmember16" >
              <a href="contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember')?></a>
            </li>
            <li class="icon addcomment16" >
              <a href="addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments')?></a>
            </li>
          </ul>
        <?php
    }


    protected function getSubmenuActiveItem()
    {
        return 'editmyprofile';
    }


    protected function editMyProfileFormPrepare($member)
    {
        $Rights = MOD_right::get();
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $profile_language_name = $lang->Name;
        $words = $this->getWords();
        $ReadCrypted = 'AdminReadCrypted';

        $vars = array();

        // Prepare $vars
        $vars['ProfileSummary'] = ($member->ProfileSummary > 0) ? $member->get_trad('ProfileSummary', $profile_language) : '';
        $vars['BirthDate'] = $member->BirthDate;
        $vars['HideBirthDate'] = $member->HideBirthDate;
        $vars['Occupation'] = ($member->Occupation > 0) ? $member->get_trad('Occupation', $profile_language) : '';

        $vars['language_levels'] = $member->language_levels;
        $vars['languages_all'] = $member->languages_all;
        $vars['languages_selected'] = $member->languages_spoken;

        $address = $member->address;
        $vars['FirstName'] = ($member->FirstName > 0) ? MOD_crypt::MemberReadCrypted($member->FirstName) : '';
        $vars['SecondName'] = ($member->SecondName > 0) ? MOD_crypt::MemberReadCrypted($member->SecondName) : '';
        $vars['LastName'] = ($member->LastName > 0) ? MOD_crypt::MemberReadCrypted($member->LastName) : '';
        $vars['HouseNumber'] = ($member->address->HouseNumber > 0) ? MOD_crypt::MemberReadCrypted($member->address->HouseNumber) : '';
        $vars['Street'] = ($member->address->StreetName > 0) ? MOD_crypt::MemberReadCrypted($member->address->StreetName) : '';
        $vars['Zip'] = ($member->address->Zip > 0) ? MOD_crypt::MemberReadCrypted($member->address->Zip) : '';
        $vars['IsHidden_FirstName'] = MOD_crypt::IsCrypted($member->FirstName);
        $vars['IsHidden_SecondName'] = MOD_crypt::IsCrypted($member->SecondName);
        $vars['IsHidden_LastName'] = MOD_crypt::IsCrypted($member->LastName);
        $vars['IsHidden_Address'] = MOD_crypt::IsCrypted($member->Address);
        $vars['IsHidden_Zip'] = MOD_crypt::IsCrypted($member->zip);
        $vars['IsHidden_HomePhoneNumber'] = MOD_crypt::IsCrypted($member->HomePhoneNumber);
        $vars['IsHidden_CellPhoneNumber'] = MOD_crypt::IsCrypted($member->CellPhoneNumber);
        $vars['IsHidden_WorkPhoneNumber'] = MOD_crypt::IsCrypted($member->WorkPhoneNumber);
        $vars['HomePhoneNumber'] = ($member->HomePhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->HomePhoneNumber) : '';
        $vars['CellPhoneNumber'] = ($member->CellPhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->CellPhoneNumber) : '';
        $vars['WorkPhoneNumber'] = ($member->WorkPhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->WorkPhoneNumber) : '';
        $vars['Email'] = ($member->Email > 0) ? MOD_crypt::MemberReadCrypted($member->Email) : '';
        $vars['WebSite'] = $member->WebSite;

        $vars['messengers'] = $member->messengers();

        $vars['Accomodation'] = $member->Accomodation;
        $vars['MaxGuest'] = $member->MaxGuest;
        $vars['MaxLenghtOfStay'] = $member->get_trad("MaxLenghtOfStay", $profile_language);
        $vars['ILiveWith'] = $member->get_trad("ILiveWith", $profile_language);
        $vars['PleaseBring'] = $member->get_trad("PleaseBring", $profile_language);
        $vars['OfferGuests'] = $member->get_trad("OfferGuests", $profile_language);
        $vars['OfferHosts'] = $member->get_trad("OfferHosts", $profile_language);
        $vars['TabTypicOffer'] = $member->TabTypicOffer;
        $vars['PublicTransport'] = $member->get_trad("PublicTransport", $profile_language);
        $vars['TabRestrictions'] = $member->TabRestrictions;
        $vars['OtherRestrictions'] = $member->get_trad("OtherRestrictions", $profile_language);
        $vars['AdditionalAccomodationInfo'] = $member->get_trad("AdditionalAccomodationInfo", $profile_language);
        $vars['OfferHosts'] = $member->get_trad("OfferHosts", $profile_language);
        $vars['Hobbies'] = $member->get_trad("Hobbies", $profile_language);
        $vars['Books'] = $member->get_trad("Books", $profile_language);
        $vars['Music'] = $member->get_trad("Music", $profile_language);
        $vars['Movies'] = $member->get_trad("Movies", $profile_language);
        $vars['Organizations'] = $member->get_trad("Organizations", $profile_language);
        $vars['PastTrips'] = $member->get_trad("PastTrips", $profile_language);
        $vars['PlannedTrips'] = $member->get_trad("PlannedTrips", $profile_language);

        return $vars;
    }

    protected function editMyProfileFormContent($vars)
    {
        $member = $this->member;
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $profile_language_name = $lang->Name;
        $words = $this->getWords();
        $CanTranslate = false;
        ?>
          <fieldset id="profilesummary">
            <legend><?=$words->getInLang('ProfileSummary', $profile_language)?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->getInLang('ProfilePicture', $profile_language)?>:</td>
                  <td>
                    <label for="profile_picture"><?= $words->get('uploadselectpicture'); ?></label><br /><input id="profile_picture" name="profile_picture" type="file" />
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->getInLang('ProfileSummary', $profile_language)?>:</td>
                  <td>
                    <textarea name="ProfileSummary" class="long" cols="60"  rows="6" ><?=$vars['ProfileSummary']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupBirthDate')?>:</td>
                  <td colspan="2" >
                        <?=$vars['BirthDate']?>
                        &nbsp;&nbsp;&nbsp;&nbsp; <input name="HideBirthDate" value="Yes" type="checkbox"
                        <?php
                        if ($vars['HideBirthDate'] == "Yes")
                            echo ' checked="checked"';
                        echo ' /> ', $words->get("Hidden");
                    ?>
                  </td>
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
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyName')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Address')?>:</td>
                  <td><?=$vars['HouseNumber']?> <?=$vars['Street']?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Address"
                    <?php if ($vars['IsHidden_Address'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyAddress')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Zip:</td>
                  <td><?=$vars['Zip']?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Zip"
                    <?php if ($vars['IsHidden_Zip'])
                        echo 'checked="checked"';
                    ?> />
                    <?=$words->get('hidden')?>
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyZip')?></a>
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
                    <input type="text" size="25" name="Email"  value="<?=$vars['Email']?>" />
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
                    <textarea name="MaxLenghtOfStay" class="long"  cols="40" rows="4" ><?=$vars['MaxLenghtOfStay']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileILiveWith')?>:</td>
                  <td colspan="2" >
                    <textarea name="ILiveWith" class="long"  cols="40" rows="4" ><?=$vars['ILiveWith']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePleaseBring')?>:</td>
                  <td colspan="2" >
                    <textarea name="PleaseBring" class="long"  cols="40" rows="4" ><?=$vars['PleaseBring']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferGuests')?>:</td>
                  <td colspan="2" >
                    <textarea name="OfferGuests" class="long"  cols="60" rows="4" ><?=$vars['OfferGuests']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferHosts')?>:</td>
                  <td colspan="2" >
                    <textarea name="OfferHosts" class="long"  cols="60" rows="4" ><?=$vars['OfferHosts']?></textarea>
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
                    <textarea name="PublicTransport" class="long"  cols="60" rows="4" ><?=$vars['PublicTransport']?></textarea>
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
                    <textarea name="OtherRestrictions" class="long" cols="60" rows="4" ><?=$vars['OtherRestrictions']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAdditionalAccomodationInfo')?>:</td>
                  <td colspan="2" >
                    <textarea name="AdditionalAccomodationInfo" class="long" cols="60"  rows="4" ><?=$vars['AdditionalAccomodationInfo']?></textarea>
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
                    <textarea name="Hobbies" class="long" cols="60"  rows="4" ><?=$vars['Hobbies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileBooks')?>:</td>
                  <td>
                    <textarea name="Books" class="long" cols="60"  rows="4" ><?=$vars['Books']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMusic')?>:</td>
                  <td>
                    <textarea name="Music" class="long" cols="60"  rows="4" ><?=$vars['Music']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMovies')?>:</td>
                  <td>
                    <textarea name="Movies" class="long" cols="60"  rows="4" ><?=$vars['Movies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOrganizations')?>:</td>
                  <td>
                    <textarea name="Organizations" class="long" cols="60"  rows="4" ><?=$vars['Organizations']?></textarea>
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
                    <textarea name="PastTrips" class="long" cols="60"  rows="4" ><?=$vars['PastTrips']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePlannedTrips')?>:</td>
                  <td>
                    <textarea name="PlannedTrips" class="long" cols="60"  rows="4" ><?=$vars['PlannedTrips']?></textarea>
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
                    <textarea cols="40"  rows="6"  name="Group_<?=$group->Name?>" ><?=$group_comment_translated?></textarea>
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
          <fieldset id="myrelations">
            <legend><?=$words->get('MyRelations');?></legend>
            <table align="left"  border="0" >
              <tbody>
                <?php
                    $relations = $member->relations;
                    $ii = 0;
                    foreach ($relations as $rel) {
                ?>
                <tr>
                  <td>
                    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
                      <img class="framed"  src="<?=PVars::getObj('env')->baseuri?>/photos/???"  height="50px"  width="50px"  alt="Profile" />
                    </a>
                    <br />
                    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
                  </td>
                  <td align="right"  colspan="2" >
                    <textarea class="long" cols="60" rows="4"  name="RelationComment_<?=$ii++?>" ><?=$rel->Comment?></textarea>
                  </td>
                  <td>
                    <a href="editmyprofile.php?action=delrelation&amp;Username=<?=$rel->Username?>"  onclick="return confirm('Confirm delete ?');" ><?=$words->get("delrelation",$rel->Username)?></a>
                  </td>
                </tr>
              <?php } ?>

              </tbody>
            </table>
          </fieldset>
          <table>
            <tbody>
              <tr>
                <td colspan="3"  align="center" >
                  <input type="submit"  id="submit"  name="submit"  value="<?=$words->get('SubmitForm')?>" >
                </td>
              </tr>
            </tbody>
          </table>
        <?php
    }
}




?>
