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
        $words = $this->getWords();
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
                		echo get_trad($m->ProfileSummary);
                        ?>
                    </textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupBirthDate')?>:</td>
                  <td colspan="2" >
                    1873-05-17
                    <input name="HideBirthDate"  type="checkbox" >
                     Hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOccupation')?>:</td>
                  <td>
                    <input type="text"  name="Occupation"  value="Writer" >
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
                <tr align="left" >
                  <td class="label" ><?=$words->get('FirstName')?>:</td>
                  <td>nothing</td>
                  <td>
                    <input type="checkbox"  name="IsHidden_FirstName" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SecondName')?>:</td>
                  <td></td>
                  <td>
                    <input type="checkbox"  name="IsHidden_SecondName" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('LastName')?>:</td>
                  <td>nothing</td>
                  <td>
                    <input type="checkbox"  name="IsHidden_LastName" >
                     hidden
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyName')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Address')?>:</td>
                  <td>14 rue S�same</td>
                  <td>
                    <input type="checkbox"  name="IsHidden_Address" >
                     hidden
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyAddress')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Zip:</td>
                  <td>50014</td>
                  <td>
                    <input type="checkbox"  name="IsHidden_Zip" >
                     hidden
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyZip')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Location')?>:</td>
                  <td colspan="2" >
                    Paris
                    <BR>
                    �le-de-France
                    <BR>
                    France
                    <BR>
                  </td>
                  <td>
                    <a href="updatemandatory.php?cid=14" ><?=$words->get('UpdateMyLocation')?></a>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileHomePhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="HomePhoneNumber"  value="nothing" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_HomePhoneNumber"  checked="checked" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileCellPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="CellPhoneNumber" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_CellPhoneNumber" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileWorkPhoneNumber')?>:</td>
                  <td>
                    <input type="text"  name="WorkPhoneNumber" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_WorkPhoneNumber" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('SignupEmail')?>:</td>
                  <td>
                    <input type="text"  name="Email"  value="henri@bv.org" >
                  </td>
                  <td><?=$words->get('EmailIsAlwayHidden')?></td>
                  <td>
                    <input type="submit"  id="submit"  name="action"  value="Email test"  title="Click to test your email" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('Website')?>:</td>
                  <td>
                    <input type="text"  name="WebSite"  value="http://www.henri-barbusse.net/" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Skype:</td>
                  <td>
                    <input type="text"  name="chat_SKYPE" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_SKYPE" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >ICQ:</td>
                  <td>
                    <input type="text"  name="chat_ICQ" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_ICQ" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >MSN:</td>
                  <td>
                    <input type="text"  name="chat_MSN" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_MSN" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Aol:</td>
                  <td>
                    <input type="text"  name="chat_Aol" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_Aol" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label icon yahoo16" >Yahoo:</td>
                  <td>
                    <input type="text"  name="chat_YAHOO" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_YAHOO" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" >Google Talk:</td>
                  <td>
                    <input type="text"  name="chat_GOOGLE" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_GOOGLE" >
                     hidden
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('chat_others')?>:</td>
                  <td>
                    <input type="text"  name="chat_Others" >
                  </td>
                  <td>
                    <input type="checkbox"  name="IsHidden_chat_Others" >
                     hidden
                  </td>
                </tr>
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
                      <option value="dependonrequest"  selected="selected" >Maybe</option>
                      <option value="neverask" >No, sorry</option>
                      <option value="anytime" >Yes, be welcome</option>
                    </select>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileNumberOfGuests')?>:</td>
                  <td>
                    <input name="MaxGuest"  type="text"  size="3"  value="3" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMaxLenghtOfStay')?>:</td>
                  <td colspan="2" >
                    <input name="MaxLenghtOfStay"  type="text"  size="40"  value="no more than one month" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileILiveWith')?>:</td>
                  <td colspan="2" >
                    <input name="ILiveWith"  type="text"  size="40"  value="some friends" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePleaseBring')?>:</td>
                  <td colspan="2" >
                    <input name="PleaseBring"  type="text"  size="40" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferGuests')?>:</td>
                  <td colspan="2" >
                    <input name="OfferGuests"  type="text"  size="40" >
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOfferHosts')?>:</td>
                  <td colspan="2" >
                    <input name="OfferHosts"  type="text"  size="40" >
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
                    <input name="PublicTransport"  type="text"  size="40" >
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
                    <textarea name="OtherRestrictions"  cols="40"  rows="3" >Please don't bring any weaponsPlease don't bring any weapons</textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileAdditionalAccomodationInfo')?>:</td>
                  <td colspan="2" >
                    <textarea name="AdditionalAccomodationInfo"  cols="40"  rows="4" >I can offer you a wonderful visit of the catacombsI can offer you a wonderful visit of the catacombs</textarea>
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
                    <textarea name="Hobbies"  cols="40"  rows="4" ></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileBooks')?>:</td>
                  <td>
                    <textarea name="Books"  cols="40"  rows="4" ></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMusic')?>:</td>
                  <td>
                    <textarea name="Music"  cols="40"  rows="4" ></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileMovies')?>:</td>
                  <td>
                    <textarea name="Movies"  cols="40"  rows="4" ></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfileOrganizations')?>:</td>
                  <td>
                    <textarea name="Organizations"  cols="40"  rows="4" >Communist party Communist party </textarea>
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
                    <textarea name="PastTrips"  cols="40"  rows="4" ></textarea>
                  </td>
                </tr>
                <tr align="left" >
                  <td class="label" ><?=$words->get('ProfilePlannedTrips')?>:</td>
                  <td>
                    <textarea name="PlannedTrips"  cols="40"  rows="4" ></textarea>
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