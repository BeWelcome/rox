<?php


class EditMyProfilePage extends MemberPage
{
    protected function leftSidebar()
    {
        ?>
          <h3>Action</h3>
          <ul class="linklist" >
            <li class="icon contactmember16" >
              <a href="contactmember.php?cid=1" >Send message</a>
            </li>
            <li class="icon addcomment16" >
              <a href="addcomments.php?cid=1" >Add Comment</a>
            </li>
          </ul>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'editmyprofile';
    }
    
    
    protected function column_col3()
    {
        $member = $this->member;
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'editMyProfileCallback');
        
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $languages = $member->get_profile_languages(); 
        
        $vars = $this->editMyProfileFormPrepare($member);
        
        if (!$memory = $formkit->getMemFromRedirect()) {
            // no memory
            // echo 'no memory';
        } else {
            // from previous form
            if ($memory->post) {
                foreach ($memory->post as $key => $value) {
                    $vars[$key] = $value;
                }
                // update $vars for messengers
                if(isset($vars['messengers'])) { 
                    $ii = 0;
                    foreach($vars['messengers'] as $me) {
                        $val = 'chat_' . $me['network_raw'];
                        $vars['messengers'][$ii++]['address'] = $vars[$val];
                    }
                }
                // update $vars for $languages
                if(!isset($vars['languages_selected'])) { 
                    $vars['languages_selected'] = array();
                }
                $ii = 0;
                $ii2 = 0;
                $lang_used = array();
                foreach($vars['memberslanguages'] as $lang) {
                    if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                        $vars['languages_selected'][$ii]->IdLanguage = $lang;
                        $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                        array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                        $ii++;
                    }
                    $ii2++;
                }
            }
            
            // problems from previous form
            if (is_array($memory->problems)) {
                require_once 'templates/editmyprofile_warning.php';
            }
        }
        // var_dump($vars);
        ?>
        <div>
            Edit your profile in 
        <?php 
        foreach($languages as $language) { 
            $css = 'opacity: 0.5';
            if ($language == $profile_language_code) $css = '';
        ?>
            <a href="editmyprofile/<?=$language ?>">
             <img height="11px"  width="16px"  src="bw/images/flags/<?=$language ?>.png" style="<?=$css?>" alt="<?=$language ?>.png">
            </a>       	
        <?php } ?>
        <?php /* 
            <strong>english</strong> | <a href="editmyprofile/fr"/>french</a> |
            <select>
                <option>add new language</option>
                <optgroup label="Your languages">
                    <option>german</option>
                    <option>chinese</option>
                </optgroup>
                <optgroup label="All languages">
                    <option>africaans</option>
                    <option>brasilian portuguese</option>
                </optgroup>
            </select>
             */ ?>
        </div>
        <hr>
        <br />
        <form method="post" action="<?=$page_url?>" name="signup" id="profile">
        <input type="hidden"  name="memberid"  value="<?=$member->id?>" />
        <?php
        
        echo $callback_tag;
        
        $this->editMyProfileFormContent($vars);
        
?>
        </form>
        <script type="text/javascript">//<!--
            var iterator = 1;
            function insertNewTemplate(event){
                var element = Event.element(event);
                if (iterator == 7) {
                    Event.stopObserving(element, 'click', insertNewTemplate);
                    element.disable;
                }
                var node1 = $('lang'+iterator);
                var node2 = node1.cloneNode(true);
                iterator++;
                node2.setAttribute('id', 'lang'+iterator);
                node1.parentNode.appendChild(node2);
            }
            
            document.observe("dom:loaded", function() {
              new FieldsetMenu('profile-edit-form', {active: "profilesummary"});
              $('langbutton').observe('click',insertNewTemplate);
            });
        //-->
        </script>
        </div>
<?php
    }
    
    
    protected function editMyProfileFormPrepare($member)
    {
        $Rights = MOD_right::get();
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
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
        $vars['Email'] = $member->email;
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
        $words = $this->getWords();
        $CanTranslate = false;
        ?>
          <fieldset id="profilesummary">
            <legend class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language)?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->getInLang('ProfileSummary', $profile_language)?>:</td>
                  <td>
                    <textarea name="ProfileSummary"  cols="40"  rows="8" ><?=$vars['ProfileSummary']?></textarea>
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
                        echo ' > ', $words->get("Hidden");
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
            <legend class="icon contact22" ><?=$words->get('ContactInfo')?></legend>
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
                  <td><?=$member->firstname?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_FirstName" 
                    <?php if ($vars['IsHidden_FirstName'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SecondName')?>:</td>
                  <td><?=$member->secondname?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_SecondName" 
                    <?php if ($vars['IsHidden_SecondName'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('LastName')?>:</td>
                  <td><?=$member->lastname?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_LastName" 
                    <?php if ($vars['IsHidden_LastName'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyName')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Address')?>:</td>
                  <td><?=$member->housenumber?> <?=$member->street?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Address" 
                    <?php if ($vars['IsHidden_Address'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyAddress')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Zip:</td>
                  <td><?=$member->zip?></td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_Zip" 
                    <?php if ($vars['IsHidden_Zip'])
                        echo "checked";
                    ?>>
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
                    <BR>
                    <?=$member->region?>
                    <BR>
                    <?=$member->country?>
                    <BR>
                  </td>
                  <td>
                    <a href="setlocation" ><?=$words->get('UpdateMyLocation')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHomePhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="HomePhoneNumber"  value="<?=$vars['HomePhoneNumber']?>" >
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_HomePhoneNumber" 
                    <?php if ($vars['IsHidden_HomePhoneNumber'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileCellPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="CellPhoneNumber" value="<?=$vars['CellPhoneNumber']?>">
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_CellPhoneNumber"  
                    <?php if ($vars['IsHidden_CellPhoneNumber'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileWorkPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="WorkPhoneNumber" value="<?=$vars['WorkPhoneNumber']?>">
                  </td>
                  <td>
                    <input type="checkbox"  value="Yes"  name="IsHidden_WorkPhoneNumber"  
                    <?php if ($vars['IsHidden_WorkPhoneNumber'])
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupEmail')?>:</td>
                  <td>
                    <input type="text"  name="Email"  value="<?=$vars['Email']?>" >
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>
                  <td>
                    <input type="submit"  id="submit"  name="action"  value="Email test"  title="Click to test your email" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Website')?>:</td>
                  <td>
                    <input type="text"  name="WebSite"  value="<?=$vars['WebSite']?>" >
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
                    <input type="text"  name="<?=$val?>" value="<?=$me["address"]?>">
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
            <legend class="icon accommodation22" ><?=$words->get('ProfileAccommodation')?></legend>
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
                    <input name="MaxLenghtOfStay"  type="text"  size="40"  value="<?=$vars['MaxLenghtOfStay']?>" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileILiveWith')?>:</td>
                  <td colspan="2" >
                    <input name="ILiveWith"  type="text"  size="40"  value="<?=$vars['ILiveWith']?>" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePleaseBring')?>:</td>
                  <td colspan="2" >
                    <input name="PleaseBring"  type="text"  size="40" value="<?=$vars['PleaseBring']?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferGuests')?>:</td>
                  <td colspan="2" >
                    <input name="OfferGuests"  type="text"  size="40" value="<?=$vars['OfferGuests']?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferHosts')?>:</td>
                  <td colspan="2" >
                    <input name="OfferHosts"  type="text"  size="40" value="<?=$vars['OfferHosts']?>">
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
                    <input name="PublicTransport"  type="text"  size="40" value="<?=$vars['PublicTransport']?>">
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
                    <textarea name="OtherRestrictions"  cols="40"  rows="3" ><?=$vars['OtherRestrictions']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAdditionalAccomodationInfo')?>:</td>
                  <td colspan="2" >
                    <textarea name="AdditionalAccomodationInfo"  cols="40"  rows="4" ><?=$vars['AdditionalAccomodationInfo']?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="profileinterests">
            <legend class="icon sun22" ><?=$words->get('ProfileInterests')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHobbies')?>:</td>
                  <td>
                    <textarea name="Hobbies"  cols="40"  rows="4" ><?=$vars['Hobbies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileBooks')?>:</td>
                  <td>
                    <textarea name="Books"  cols="40"  rows="4" ><?=$vars['Books']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMusic')?>:</td>
                  <td>
                    <textarea name="Music"  cols="40"  rows="4" ><?=$vars['Music']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMovies')?>:</td>
                  <td>
                    <textarea name="Movies"  cols="40"  rows="4" ><?=$vars['Movies']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOrganizations')?>:</td>
                  <td>
                    <textarea name="Organizations"  cols="40"  rows="4" ><?=$vars['Organizations']?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="profiletravelexperience">
            <legend class="icon world22" ><?=$words->get('ProfileTravelExperience')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePastTrips')?>:</td>
                  <td>
                    <textarea name="PastTrips"  cols="40"  rows="4" ><?=$vars['PastTrips']?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePlannedTrips')?>:</td>
                  <td>
                    <textarea name="PlannedTrips"  cols="40"  rows="4" ><?=$vars['PlannedTrips']?></textarea>
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
            <legend class="icon groups22" ><?=$words->get('MyRelations');?></legend>
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
                      <img class="framed"  src="<?=PVars::getObj('env')->baseuri?>/photos/???"  height="50px"  width="50px"  alt="Profile">
                    </a>
                    <br />
                    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
                  </td>
                  <td align="right"  colspan="2" >
                    <textarea cols="40"  rows="6"  name="RelationComment_<?=$ii++?>" ><?=$rel->Comment?></textarea>
                  </td>
                  <td>
                    <a href="editmyprofile.php?action=delrelation&Username=<?=$rel->Username?>"  onclick="return confirm('Confirm delete ?');" ><?=$words->get("delrelation",$rel->Username)?></a>
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
                  <input type="submit"  id="submit"  name="submit"  value="submit" >
                </td>
              </tr>
            </tbody>
          </table>
        <?php
    }
}




?>