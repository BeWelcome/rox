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
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
        
        echo '
        <div>
        Edit your profile in <strong>english</strong> | <a href="editmyprofile/french"/>french</a> | <select>
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
        <br>';
        
        /*
        echo '
        <DIV class="info" >
        <P class="note" ></P>
        <P class="note" >
        <B>Warning: everything you write here will be considered English. If you want to enter text in another language, please click on the appropriate flag at the bottom of this page and choose the language you want to use. Thank you!</B>
        </P>
        <FORM id="preferences"  method="post"  action="editmyprofile.php">';
        */
        
        echo $callback_tag;
        
        $this->editMyProfileFormContent();
        
        echo '
        </form>
        </div>';
    }
    
    
    protected function editMyProfileFormContent()
    {
        $m = $this->member;
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $words = $this->getWords();
        $CanTranslate = false;
        $ReadCrypted = 'AdminReadCrypted';
        $messengers = $m->messengers();
        ?>
          <fieldset>
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
                        <tr>
                          <td>English</td>
                          <td>
                            <select name="memberslanguageslevel_level_id_1" >
                              <option value="MotherLanguage" >Mother Tongue</option>
                              <option value="Expert" >Expert</option>
                              <option value="Fluent" >Fluent</option>
                              <option value="Intermediate"  selected="selected" >Intermediate</option>
                              <option value="Beginner" >Beginner</option>
                              <option value="HelloOnly" >Can only say Welcome!</option>
                              <option value="DontKnow" >Not known</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>fran�ais</td>
                          <td>
                            <select name="memberslanguageslevel_level_id_2" >
                              <option value="MotherLanguage" >Mother Tongue</option>
                              <option value="Expert"  selected="selected" >Expert</option>
                              <option value="Fluent" >Fluent</option>
                              <option value="Intermediate" >Intermediate</option>
                              <option value="Beginner" >Beginner</option>
                              <option value="HelloOnly" >Can only say Welcome!</option>
                              <option value="DontKnow" >Not known</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <select name="memberslanguageslevel_newIdLanguage" >
                              <option selected="selected" >-Choose new language-</option>
                              <option value="12" >????????</option>
                              <option value="2" >??????????</option>
                              <option value="3" >Portugu�s (bra)</option>
                              <option value="4" >?????????</option>
                              <option value="5" >??</option>
                              <option value="6" >deutsch</option>
                              <option value="7" >Eesti keel</option>
                              <option value="8" >??????????</option>
                              <option value="9" >espa�ol</option>
                              <option value="10" >???????</option>
                              <option value="11" >suomi</option>
                              <option value="13" >angol</option>
                              <option value="14" >italiano</option>
                              <option value="15" >lietuviu</option>
                              <option value="16" >LatvieÃ…Â¡u</option>
                              <option value="17" >????????</option>
                              <option value="18" >Nederlands</option>
                              <option value="19" >Polski</option>
                              <option value="20" >portuguese</option>
                              <option value="21" >Rom�na</option>
                              <option value="22" >???????</option>
                              <option value="23" >svenska</option>
                              <option value="24" >T�rk�e</option>
                              <option value="27" >esperanton</option>
                              <option value="28" >dansk</option>
                              <option value="29" >cat� la</option>
                              <option value="31" >prog</option>
                              <option value="32" >Latvie�u</option>
                              <option value="33" >ελληνικά</option>
                              <option value="34" >norsk</option>
                            </select>
                          </td>
                          <td>
                            <select name="memberslanguageslevel_newLevel" >
                              <option value="MotherLanguage" >Mother Tongue</option>
                              <option value="Expert" >Expert</option>
                              <option value="Fluent" >Fluent</option>
                              <option value="Intermediate" >Intermediate</option>
                              <option value="Beginner" >Beginner</option>
                              <option value="HelloOnly" >Can only say Welcome!</option>
                              <option value="DontKnow" >Not known</option>
                            </select>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset>
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
          <fieldset>
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
          <fieldset>
            <legend class="icon sun22" ><?=$words->get('ProfileInterests')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHobbies')?>ProfileHobbies:</td>
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
          <fieldset>
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
          <fieldset>
            <legend class="icon groups22" ><?=$words->get('MyGroups')?></legend>
            <table border="0" >
              <colgroup>
                <col width="25%" ></col>
                <col width="75%" ></col>
              </colgroup>
              <tbody>
                <tr align="left" >
                  <td class="label" >Rugby</td>
                  <td colspan="2" >
                    <textarea cols="40"  rows="6"  name="Group_Rugby" >I love all ball sportsI love all ball sports</textarea>
                    <input type="hidden"  name="AcceptMessage_Rugby"  value="no" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Sailors</td>
                  <td colspan="2" >
                    <textarea cols="40"  rows="6"  name="Group_Sailors" >I love boat and other sailing devicesI love boat and other sailing devices</textarea>
                    <input type="hidden"  name="AcceptMessage_Sailors"  value="no" >
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
          <fieldset>
            <legend class="icon groups22" ><?=$words->get('MyRelations');?></legend>
            <table align="left"  border="0" >
              <tbody>
                <tr>
                  <td>
                    <a href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin"  title="See profile admin" >
                      <img class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                    </a>
                    <BR>
                    admin
                  </td>
                  <td align="right"  colspan="2" >
                    <textarea cols="40"  rows="6"  name="RelationComment_2" >this is a testthis is a test</textarea>
                  </td>
                  <td>
                    <a href="editmyprofile.php?action=delrelation&Username=admin"  onclick="return confirm('Confirm delete ?');" >remove this relation</a>
                  </td>
                </tr>
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