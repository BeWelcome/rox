<?php


class EditMyProfilePage extends MemberPage
{
    protected function leftSidebar()
    {
        ?>
          <H3>Action</H3>
          <UL class="linklist" >
            <LI class="icon contactmember16" >
              <A href="contactmember.php?cid=1" >Send message</A>
            </LI>
            <LI class="icon addcomment16" >
              <A href="addcomments.php?cid=1" >Add Comment</A>
            </LI>
          </UL>
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
        <DIV class="info" >
        <P class="note" ></P>
        <P class="note" >
        <B>Warning: everything you write here will be considered English. If you want to enter text in another language, please click on the appropriate flag at the bottom of this page and choose the language you want to use. Thank you!</B>
        </P>
        <FORM id="preferences"  method="post"  action="editmyprofile.php">';
        
        echo $callback_tag;
        
        $this->editMyProfileFormContent();
        
        echo '
        </form>
        </div>';
    }
    
    
    protected function editMyProfileFormContent()
    {
        ?>
          <FIELDSET>
            <LEGEND class="icon info22" >Profile summary</LEGEND>
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="75%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Profile summary:</TD>
                  <TD>
                    <TEXTAREA name="ProfileSummary"  cols="40"  rows="8" >I am a writer buried in Paris in the cimetiÃ¨re le pÃ¨re lachaiseNota : This is a fake profile created by Jan-Yves for testing.I am a writer buried in Paris in the cimetiÃ¨re le pÃ¨re lachaise

Nota : This is a fake profile created by Jan-Yves for testing.</TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >birth date:</TD>
                  <TD colspan="2" >
                    1873-05-17
                    <INPUT name="HideBirthDate"  type="checkbox" >
                     Hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Occupation:</TD>
                  <TD>
                    <INPUT type="text"  name="Occupation"  value="Writer" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Spoken languages:</TD>
                  <TD>
                    <TABLE>
                      <TBODY>
                        <TR>
                          <TD>English</TD>
                          <TD>
                            <SELECT name="memberslanguageslevel_level_id_1" >
                              <OPTION value="MotherLanguage" >Mother Tongue</OPTION>
                              <OPTION value="Expert" >Expert</OPTION>
                              <OPTION value="Fluent" >Fluent</OPTION>
                              <OPTION value="Intermediate"  selected="selected" >Intermediate</OPTION>
                              <OPTION value="Beginner" >Beginner</OPTION>
                              <OPTION value="HelloOnly" >Can only say Welcome!</OPTION>
                              <OPTION value="DontKnow" >Not known</OPTION>
                            </SELECT>
                          </TD>
                        </TR>
                        <TR>
                          <TD>fran�ais</TD>
                          <TD>
                            <SELECT name="memberslanguageslevel_level_id_2" >
                              <OPTION value="MotherLanguage" >Mother Tongue</OPTION>
                              <OPTION value="Expert"  selected="selected" >Expert</OPTION>
                              <OPTION value="Fluent" >Fluent</OPTION>
                              <OPTION value="Intermediate" >Intermediate</OPTION>
                              <OPTION value="Beginner" >Beginner</OPTION>
                              <OPTION value="HelloOnly" >Can only say Welcome!</OPTION>
                              <OPTION value="DontKnow" >Not known</OPTION>
                            </SELECT>
                          </TD>
                        </TR>
                        <TR>
                          <TD>
                            <SELECT name="memberslanguageslevel_newIdLanguage" >
                              <OPTION selected="selected" >-Choose new language-</OPTION>
                              <OPTION value="12" >????????</OPTION>
                              <OPTION value="2" >??????????</OPTION>
                              <OPTION value="3" >Portugu�s (bra)</OPTION>
                              <OPTION value="4" >?????????</OPTION>
                              <OPTION value="5" >??</OPTION>
                              <OPTION value="6" >deutsch</OPTION>
                              <OPTION value="7" >Eesti keel</OPTION>
                              <OPTION value="8" >??????????</OPTION>
                              <OPTION value="9" >espa�ol</OPTION>
                              <OPTION value="10" >???????</OPTION>
                              <OPTION value="11" >suomi</OPTION>
                              <OPTION value="13" >angol</OPTION>
                              <OPTION value="14" >italiano</OPTION>
                              <OPTION value="15" >lietuviu</OPTION>
                              <OPTION value="16" >LatvieÃ…Â¡u</OPTION>
                              <OPTION value="17" >????????</OPTION>
                              <OPTION value="18" >Nederlands</OPTION>
                              <OPTION value="19" >Polski</OPTION>
                              <OPTION value="20" >portuguese</OPTION>
                              <OPTION value="21" >Rom�na</OPTION>
                              <OPTION value="22" >???????</OPTION>
                              <OPTION value="23" >svenska</OPTION>
                              <OPTION value="24" >T�rk�e</OPTION>
                              <OPTION value="27" >esperanton</OPTION>
                              <OPTION value="28" >dansk</OPTION>
                              <OPTION value="29" >cat� la</OPTION>
                              <OPTION value="31" >prog</OPTION>
                              <OPTION value="32" >Latvie�u</OPTION>
                              <OPTION value="33" >ελληνικά</OPTION>
                              <OPTION value="34" >norsk</OPTION>
                            </SELECT>
                          </TD>
                          <TD>
                            <SELECT name="memberslanguageslevel_newLevel" >
                              <OPTION value="MotherLanguage" >Mother Tongue</OPTION>
                              <OPTION value="Expert" >Expert</OPTION>
                              <OPTION value="Fluent" >Fluent</OPTION>
                              <OPTION value="Intermediate" >Intermediate</OPTION>
                              <OPTION value="Beginner" >Beginner</OPTION>
                              <OPTION value="HelloOnly" >Can only say Welcome!</OPTION>
                              <OPTION value="DontKnow" >Not known</OPTION>
                            </SELECT>
                          </TD>
                        </TR>
                      </TBODY>
                    </TABLE>
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          &lt;FIELDSET>
            <LEGEND class="icon contact22" >Contact Information</LEGEND>
            <INPUT type="hidden"  name="cid"  value="14" >
            <INPUT type="hidden"  name="action"  value="update" >
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="25%" ></COL>
                <COL width="15%" ></COL>
                <COL width="35%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >First Name:</TD>
                  <TD>nothing</TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_FirstName" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Second Name:</TD>
                  <TD></TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_SecondName" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Last Name:</TD>
                  <TD>nothing</TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_LastName" >
                     hidden
                  </TD>
                  <TD>
                    <A href="updatemandatory.php?cid=14" >Update my name</A>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Address:</TD>
                  <TD>14 rue S�same</TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_Address" >
                     hidden
                  </TD>
                  <TD>
                    <A href="updatemandatory.php?cid=14" >Update my address</A>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Zip:</TD>
                  <TD>50014</TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_Zip" >
                     hidden
                  </TD>
                  <TD>
                    <A href="updatemandatory.php?cid=14" >Update my zip</A>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Location:</TD>
                  <TD colspan="2" >
                    Paris
                    <BR>
                    �le-de-France
                    <BR>
                    France
                    <BR>
                  </TD>
                  <TD>
                    <A href="updatemandatory.php?cid=14" >Update my location</A>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Home phone:</TD>
                  <TD>
                    <INPUT type="text"  name="HomePhoneNumber"  value="nothing" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_HomePhoneNumber"  checked="checked" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Mobile:</TD>
                  <TD>
                    <INPUT type="text"  name="CellPhoneNumber" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_CellPhoneNumber" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Work phone</TD>
                  <TD>
                    <INPUT type="text"  name="WorkPhoneNumber" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_WorkPhoneNumber" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Email:</TD>
                  <TD>
                    <INPUT type="text"  name="Email"  value="henri@bv.org" >
                  </TD>
                  <TD>Always hidden</TD>
                  <TD>
                    <INPUT type="submit"  id="submit"  name="action"  value="Email test"  title="Click to test your email" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Web site:</TD>
                  <TD>
                    <INPUT type="text"  name="WebSite"  value="http://www.henri-barbusse.net/" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Skype:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_SKYPE" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_SKYPE" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >ICQ:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_ICQ" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_ICQ" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >MSN:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_MSN" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_MSN" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >AOL:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_AOL" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_AOL" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label icon yahoo16" >Yahoo:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_YAHOO" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_YAHOO" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Google Talk:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_GOOGLE" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_GOOGLE" >
                     hidden
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Other IM (chat) accounts:</TD>
                  <TD>
                    <INPUT type="text"  name="chat_Others" >
                  </TD>
                  <TD>
                    <INPUT type="checkbox"  name="IsHidden_chat_Others" >
                     hidden
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <FIELDSET>
            <LEGEND class="icon accommodation22" >Accommodation</LEGEND>
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="75%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Accommodation</TD>
                  <TD>
                    <SELECT name="Accomodation" >
                      <OPTION value="dependonrequest"  selected="selected" >Maybe</OPTION>
                      <OPTION value="neverask" >No, sorry</OPTION>
                      <OPTION value="anytime" >Yes, be welcome</OPTION>
                    </SELECT>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Max number of guests:</TD>
                  <TD>
                    <INPUT name="MaxGuest"  type="text"  size="3"  value="3" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Maximum length of stay:</TD>
                  <TD colspan="2" >
                    <INPUT name="MaxLenghtOfStay"  type="text"  size="40"  value="no more than one month" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I Live With:</TD>
                  <TD colspan="2" >
                    <INPUT name="ILiveWith"  type="text"  size="40"  value="some friends" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Please bring:</TD>
                  <TD colspan="2" >
                    <INPUT name="PleaseBring"  type="text"  size="40" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I can offer my guests:</TD>
                  <TD colspan="2" >
                    <INPUT name="OfferGuests"  type="text"  size="40" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I can offer my hosts:</TD>
                  <TD colspan="2" >
                    <INPUT name="OfferHosts"  type="text"  size="40" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I can also offer:</TD>
                  <TD colspan="2" >
                    <UL>
                      <LI>
                        <INPUT type="checkbox"  name="check_guidedtour"  checked="checked" >
                        I can offer a guided tour
                      </LI>
                      <LI>
                        <INPUT type="checkbox"  name="check_dinner"  checked="checked" >
                        I can offer a dinner 
                      </LI>
                      <LI>
                        <INPUT type="checkbox"  name="check_CanHostWeelChair" >
                        My place is accessible for someone in a wheelchair
                      </LI>
                    </UL>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Public transport:</TD>
                  <TD colspan="2" >
                    <INPUT name="PublicTransport"  type="text"  size="40" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Restrictions:</TD>
                  <TD colspan="2" >
                    <UL>
                      <LI>
                        <INPUT type="checkbox"  name="check_NoSmoker" >
                        No smoking
                      </LI>
                      <LI>
                        <INPUT type="checkbox"  name="check_NoAlchool"  checked="checked" >
                        No alcohol
                      </LI>
                      <LI>
                        <INPUT type="checkbox"  name="check_NoDrugs" >
                        No drugs
                      </LI>
                      <LI>
                        <INPUT type="checkbox"  name="check_SeeOtherRestrictions"  checked="checked" >
                        Please also consider the following restrictions:
                      </LI>
                    </UL>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Other restrictions:</TD>
                  <TD colspan="2" >
                    <TEXTAREA name="OtherRestrictions"  cols="40"  rows="3" >Please don't bring any weaponsPlease don't bring any weapons</TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Additional accommodation information:</TD>
                  <TD colspan="2" >
                    <TEXTAREA name="AdditionalAccomodationInfo"  cols="40"  rows="4" >I can offer you a wonderful visit of the catacombsI can offer you a wonderful visit of the catacombs</TEXTAREA>
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <FIELDSET>
            <LEGEND class="icon sun22" >My interests</LEGEND>
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="75%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Hobbies:</TD>
                  <TD>
                    <TEXTAREA name="Hobbies"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Books:</TD>
                  <TD>
                    <TEXTAREA name="Books"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Music:</TD>
                  <TD>
                    <TEXTAREA name="Music"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Films:</TD>
                  <TD>
                    <TEXTAREA name="Movies"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Organizations I belong to:</TD>
                  <TD>
                    <TEXTAREA name="Organizations"  cols="40"  rows="4" >Communist party Communist party </TEXTAREA>
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <FIELDSET>
            <LEGEND class="icon world22" >Travel experiences</LEGEND>
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="75%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Past trips:</TD>
                  <TD>
                    <TEXTAREA name="PastTrips"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Planned trips:</TD>
                  <TD>
                    <TEXTAREA name="PlannedTrips"  cols="40"  rows="4" ></TEXTAREA>
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <FIELDSET>
            <LEGEND class="icon groups22" >My groups</LEGEND>
            <TABLE border="0" >
              <COLGROUP>
                <COL width="25%" ></COL>
                <COL width="75%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Rugby</TD>
                  <TD colspan="2" >
                    <TEXTAREA cols="40"  rows="6"  name="Group_Rugby" >I love all ball sportsI love all ball sports</TEXTAREA>
                    <INPUT type="hidden"  name="AcceptMessage_Rugby"  value="no" >
                  </TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Sailors</TD>
                  <TD colspan="2" >
                    <TEXTAREA cols="40"  rows="6"  name="Group_Sailors" >I love boat and other sailing devicesI love boat and other sailing devices</TEXTAREA>
                    <INPUT type="hidden"  name="AcceptMessage_Sailors"  value="no" >
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <FIELDSET>
            <LEGEND class="icon groups22" ><?=$words->get('MyRelations');?></LEGEND>
            <TABLE align="left"  border="0" >
              <TBODY>
                <TR>
                  <TD>
                    <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin"  title="See profile admin" >
                      <IMG class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
                    </A>
                    <BR>
                    admin
                  </TD>
                  <TD align="right"  colspan="2" >
                    <TEXTAREA cols="40"  rows="6"  name="RelationComment_2" >this is a testthis is a test</TEXTAREA>
                  </TD>
                  <TD>
                    <A href="editmyprofile.php?action=delrelation&Username=admin"  onclick="return confirm('Confirm delete ?');" >remove this relation</A>
                  </TD>
                </TR>
              </TBODY>
            </TABLE>
          </FIELDSET>
          <TABLE>
            <TBODY>
              <TR>
                <TD colspan="3"  align="center" >
                  <INPUT type="submit"  id="submit"  name="submit"  value="submit" >
                </TD>
              </TR>
            </TBODY>
          </TABLE>
        <?php
    }
}




?>