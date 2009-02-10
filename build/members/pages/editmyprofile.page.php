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
        $m = $this->member;
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
        
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $languages = $m->get_profile_languages(); 
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
        </div>
        <hr>
        <br />
        <?php
        /*
        echo '
        <DIV class="info" >
        <P class="note" ></P>
        <P class="note" >
        <B>Warning: everything you write here will be considered English. If you want to enter text in another language, please click on the appropriate flag at the bottom of this page and choose the language you want to use. Thank you!</B>
        </P>
        <FORM id="profile-edit-form"  method="post"  action="editmyprofile.php">';
        */
        
        echo $callback_tag;
        
        $this->editMyProfileFormContent();
        
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
                node1.appendChild(node2);
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
    
    
    protected function editMyProfileFormContent()
    {
        $Rights = MOD_right::get();
        $m = $this->member;
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $words = $this->getWords();
        $CanTranslate = false;
        $ReadCrypted = 'AdminReadCrypted';
        $messengers = $m->messengers();
        
        $groups = $m->get_group_memberships();
        
        ?>
          <fieldset id="profilesummary">
            <legend class="icon info22" ><?=$words->get('ProfileSummary')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileSummary')?>:</td>
                  <td>
                    <textarea name="ProfileSummary"  cols="40"  rows="8" >
                        <?php
                        if ($m->ProfileSummary > 0)
                		echo $m->get_trad($m->ProfileSummary, $profile_language);
                        ?>
                    </textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupBirthDate')?>:</td>
                  <td colspan="2" >
                    <?php
                        echo $m->BirthDate;
                        echo '&nbsp;&nbsp;&nbsp;&nbsp; <input name="HideBirthDate" type="checkbox"';
                        if ($m->HideBirthDate == "Yes")
                            echo ' checked="checked"';
                        echo ' /> ', $words->get("Hidden");
                    ?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOccupation')?>:</td>
                  <td>
                    <?php
                    	if ($m->Occupation > 0)
                        echo $m->get_trad($m->Occupation, $profile_language);
                    ?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileLanguagesSpoken')?>:</td>
                  <td>
                    <table>
                      <tbody>
                      <?php
                        $tt = $m->language_levels;
                        $maxtt = count($tt);
                        $max = count($m->languages_spoken);
                        for ($ii = 0; $ii < $max; $ii++) {
                        ?>
                        <tr>
                          <td><?=$m->languages_spoken[$ii]->Name?></td>
                          <td>
                            <select name="memberslanguageslevel_level_id_<?=$m->languages_spoken[$ii]->id?>" >
                            <?php
                                for ($jj = 0; $jj < $maxtt; $jj++) {
                                    echo "                              <option value=\"" . $tt[$jj] . "\"";
                                    if ($tt[$jj] == $m->languages_spoken[$ii]->Level)
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
                      </tbody>
                      </table>
                      <div id="lang1">
                      <table border="0" >
                      <colgroup>
                        <col width="25%" ></col>
                        <col width="75%" ></col>
                      </colgroup>
                      <tbody>
                        <?php
                        $tt = $m->language_levels;
                        $maxtt = count($tt);
                        $max = count($m->languages_other);
                        // var_dump($m->languages_other);
                        ?>
                        <tr>
                            <td>
                            <select id="memberslanguageslevel_new_1" name="memberslanguageslevel_newIdLanguage[]" >
                            <option selected="selected" >-<?=$words->get("ChooseNewLanguage")?>-</option>
                            <?php
                                for ($jj = 0; $jj < $max; $jj++) {
                                    echo "<option value=\"" . $tt[$jj] . "\">";
                                    echo $m->languages_other[$jj]->Name."</option>\n";
                                }
                            ?>
                            </select>
                          </td>
                          <td>
                            <select name="memberslanguageslevel_newLevel[]" >
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
                    </div>
                    <input type="button" id="langbutton" class="button" name="addlang" value="Add Language" />
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset id="contactinfo">
            <legend class="icon contact22" ><?=$words->get('ContactInfo')?></legend>
            <input type="hidden"  name="cid"  value="14" >
            <input type="hidden"  name="action"  value="update" >
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
                  <td><?=$m->firstname?></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_FirstName" 
                    <?php if (MOD_crypt::IsCrypted($m->FirstName))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SecondName')?>:</td>
                  <td><?=$m->secondname?></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_SecondName" 
                    <?php if (MOD_crypt::IsCrypted($m->SecondName))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('LastName')?>:</td>
                  <td><?=$m->lastname?></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_LastName" 
                    <?php if (MOD_crypt::IsCrypted($m->LastName))
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
                  <td><?=$m->housenumber?> <?=$m->street?></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_Address" 
                    <?php if (MOD_crypt::IsCrypted($m->Address))
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
                  <td><?=$m->zip?></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_Zip" 
                    <?php if (MOD_crypt::IsCrypted($m->zip))
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
                    <?=$m->city?>
                    <BR>
                    <?=$m->region?>
                    <BR>
                    <?=$m->country?>
                    <BR>
                  </td>
                  <td>
                    <a href="setlocation" ><?=$words->get('UpdateMyLocation')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHomePhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="HomePhoneNumber"  value="nothing" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_HomePhoneNumber"  checked="checked" 
                    <?php if (MOD_crypt::IsCrypted($m->zip))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileCellPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="CellPhoneNumber" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_CellPhoneNumber"  
                    <?php if (MOD_crypt::IsCrypted($m->CellPhoneNumber))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileWorkPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="WorkPhoneNumber" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_WorkPhoneNumber"  
                    <?php if (MOD_crypt::IsCrypted($m->WorkPhoneNumber))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupEmail')?>:</td>
                  <td>
                    <input type="text"  name="Email"  value="<?=$m->email?>" >
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>
                  <td>
                    <input type="submit"  id="submit"  name="action"  value="Email test"  title="Click to test your email" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Website')?>:</td>
                  <td>
                    <input type="text"  name="WebSite"  value="<?=$m->WebSite?>" >
                  </td>
                </tr>

                <?php
                if(isset($messengers)) { 
                    foreach($messengers as $me) {
                    $val = 'chat_' . $me['network'];
                ?>
                <tr align="left" >
                  <td class="label" ><?=$me["network"]?> 
                  <?="<img src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$me["image"]."' width='16' height='16' title='".$me["network"]."' alt='".$me["network"]."' />"?>
                  </td>
                  <td>
                    <input type="text"  name="chat_<?=$me["network"]?>" value="<?=$me["address"]?>">
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_<?=$me["network"]?>"  
                    <?php if (MOD_crypt::IsCrypted($me["address_id"]))
                        echo "checked";
                    ?>>
                    <?=$words->get('hidden')?>
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
                        if ($tt[$ii] == $m->Accomodation)
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
                    <input name="MaxGuest"  type="text"  size="3"  value="<?=$m->get_trad("MaxGuest", $profile_language) ?>" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMaxLenghtOfStay')?>:</td>
                  <td colspan="2" >
                    <input name="MaxLenghtOfStay"  type="text"  size="40"  value="<?=$m->get_trad("MaxLenghtOfStay", $profile_language)?>" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileILiveWith')?>:</td>
                  <td colspan="2" >
                    <input name="ILiveWith"  type="text"  size="40"  value="<?=$m->get_trad("ILiveWith", $profile_language)?>" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePleaseBring')?>:</td>
                  <td colspan="2" >
                    <input name="PleaseBring"  type="text"  size="40" value="<?=$m->get_trad("PleaseBring", $profile_language)?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferGuests')?>:</td>
                  <td colspan="2" >
                    <input name="OfferGuests"  type="text"  size="40" value="<?=$m->get_trad("OfferGuests", $profile_language)?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferHosts')?>:</td>
                  <td colspan="2" >
                    <input name="OfferHosts"  type="text"  size="40" value="<?=$m->get_trad("OfferHosts", $profile_language)?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ICanAlsoOffer')?>:</td>
                  <td colspan="2" >
                    <ul>
                    <?php
                    	for ($ii = 0; $ii < $max; $ii++) {
                    		echo "<li><input type=\"checkbox\" name=\"check_" . $m->TabTypicOffer[$ii] . "\" ";
                    		if (strpos($m->TypicOffer, $m->TabTypicOffer[$ii]) !== false)
                    			echo "checked=\"checked\"";
                    		echo " />";
                    		echo "&nbsp;&nbsp;", $words->get("TypicOffer_" . $m->TabTypicOffer[$ii]), "</li>\n";
                    	}
                    ?>
                    </ul>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePublicTransport')?>:</td>
                  <td colspan="2" >
                    <input name="PublicTransport"  type="text"  size="40" value="<?=$m->get_trad("PublicTransport", $profile_language)?>">
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileRestrictionForGuest')?>:</td>
                  <td colspan="2" >
                    <ul>
                    <?php
                        for ($ii = 0; $ii < $max; $ii++) {
                    		echo "<li><input type=\"checkbox\" name=\"check_" . $m->TabRestrictions[$ii] . "\" ";
                    		if (strpos($m->Restrictions, $m->TabRestrictions[$ii]) !== false)
                    			echo "checked=\"checked\"";
                    		echo " />";
                    		echo "&nbsp;&nbsp;", $words->get("Restriction_" . $m->TabRestrictions[$ii]), "</li>\n";
                    	}
                    ?>
                    </ul>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOtherRestrictions')?>:</td>
                  <td colspan="2" >
                    <textarea name="OtherRestrictions"  cols="40"  rows="3" ><?=$m->get_trad("OtherRestrictions", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAdditionalAccomodationInfo')?>:</td>
                  <td colspan="2" >
                    <textarea name="AdditionalAccomodationInfo"  cols="40"  rows="4" ><?=$m->get_trad("AdditionalAccomodationInfo", $profile_language)?></textarea>
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
                    <textarea name="Hobbies"  cols="40"  rows="4" ><?=$m->get_trad("Hobbies", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileBooks')?>:</td>
                  <td>
                    <textarea name="Books"  cols="40"  rows="4" ><?=$m->get_trad("Books", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMusic')?>:</td>
                  <td>
                    <textarea name="Music"  cols="40"  rows="4" ><?=$m->get_trad("Music", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMovies')?>:</td>
                  <td>
                    <textarea name="Movies"  cols="40"  rows="4" ><?=$m->get_trad("Movies", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOrganizations')?>:</td>
                  <td>
                    <textarea name="Organizations"  cols="40"  rows="4" ><?=$m->get_trad("Organizations", $profile_language)?></textarea>
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
                    <textarea name="PastTrips"  cols="40"  rows="4" ><?=$m->get_trad("PastTrips", $profile_language)?></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePlannedTrips')?>:</td>
                  <td>
                    <textarea name="PlannedTrips"  cols="40"  rows="4" ><?=$m->get_trad("PlannedTrips", $profile_language)?></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <?php
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
                    $group_comment_translated = $m->get_trad_by_tradid($group->Comment, $profile_language);
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
                    $relations = $m->relations;
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