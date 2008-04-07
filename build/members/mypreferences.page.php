<?php


class MyPreferencesPage extends MemberPage
{
    protected function leftSidebar()
    {
        ?>
          <H3>No Actions</H3>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'mypreferences';
    }
    
    
    protected function column_col3()
    {
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
        
        echo '
        <DIV class="info" >
        <FORM method="post" id="preferences" >';
        
        echo $callback_tag;
        
        $this->myPreferencesFormContents();
        
        echo '
        </FORM>
        </DIV>';
    }
    
    
    protected function myPreferencesFormContents()
    {
        ?>
          <TABLE id="preferencesTable" >
            <INPUT type="hidden"  name="cid"  value="14" >
            <INPUT type="hidden"  name="action"  value="Update" >
            <TBODY>
              <TR>
                <TD>
                  <P class="preflabel" >Preferred language</P>
                </TD>
                <TD>Default language</TD>
                <TD>
                  <SELECT name="PreferenceLanguage"  class="prefsel" >
                    <OPTION value="2" >??????????</OPTION>
                    <OPTION value="4" >?????????</OPTION>
                    <OPTION value="22" >???????</OPTION>
                    <OPTION value="8" >??????????</OPTION>
                    <OPTION value="17" >????????</OPTION>
                    <OPTION value="12" >????????</OPTION>
                    <OPTION value="10" >???????</OPTION>
                    <OPTION value="5" >??</OPTION>
                    <OPTION value="13" >angol</OPTION>
                    <OPTION value="29" >catÃ la</OPTION>
                    <OPTION value="28" >dansk</OPTION>
                    <OPTION value="6" >deutsch</OPTION>
                    <OPTION value="7" >Eesti keel</OPTION>
                    <OPTION value="0" >English</OPTION>
                    <OPTION value="9" >español</OPTION>
                    <OPTION value="27" >esperanton</OPTION>
                    <OPTION value="1" >français</OPTION>
                    <OPTION value="14" >italiano</OPTION>
                    <OPTION value="33" >ÎµÎ»Î»Î·Î½Î¹ÎºÎ¬</OPTION>
                    <OPTION value="16" >LatvieÃƒâ€¦Ã‚Â¡u</OPTION>
                    <OPTION value="32" >Latviešu</OPTION>
                    <OPTION value="15" >lietuviu</OPTION>
                    <OPTION value="18" >Nederlands</OPTION>
                    <OPTION value="34" >norsk</OPTION>
                    <OPTION value="19" >Polski</OPTION>
                    <OPTION value="3" >Português (bra)</OPTION>
                    <OPTION value="20" >portuguese</OPTION>
                    <OPTION value="31" >prog</OPTION>
                    <OPTION value="21" >Româna</OPTION>
                    <OPTION value="11" >suomi</OPTION>
                    <OPTION value="23" >svenska</OPTION>
                    <OPTION value="24" >Türkçe</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD>
                  <P class="preflabel" >Spam Check</P>
                </TD>
                <TD>
                  By default the messages other members send to you are 
                  <B>not</B>
                   checked by our Spam checking volunteers.
                  <BR>
                  If you want the messages you receive to be manually checked, select 
                  <B>Yes</B>
                   .
                  <BR>
                  Note that the messages sent by members you trust or by members who are replying to a message you have sent them will not be monitored by our Spam checking volunteers.
                </TD>
                <TD>
                  <SELECT name="PreferenceCheckMyMail"  class="prefsel" >
                    <OPTION value="Yes" >Yes</OPTION>
                    <OPTION value="No" >No</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD>
                  <P class="preflabel" >Spam Folder</P>
                </TD>
                <TD>
                  If, unfortunately, you receive some spam messages they will be kept in your spam folder for one month if you choose 
                  <B>Yes</B>
                   or discarded if you choose 
                  <B>No</B>
                  .
                </TD>
                <TD>
                  <SELECT name="PreferenceInSpamFolder"  class="prefsel" >
                    <OPTION value="Yes" >Yes</OPTION>
                    <OPTION value="No" >No</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD>
                  <P class="preflabel" >Style sheet</P>
                </TD>
                <TD>Here you will be able to choose among several style sheets, just take the one you prefer (do not forget to press F5 to refresh your browser after having submitted your new preferences)</TD>
                <TD>
                  <SELECT name="PreferenceStyleSheet"  class="prefsel" >
                    <OPTION value="stylesheet1" >Default (Orange)</OPTION>
                    <OPTION value="stylesheet2" >HC Nostalgia</OPTION>
                    <OPTION value="stylesheet3" >Micha old experiment</OPTION>
                    <OPTION value="YAML" >YAML experiment</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD>
                  <P class="preflabel" >Advanced features</P>
                </TD>
                <TD>
                  Here you can choose if you want to see the additional links provided by advanced features.
                  <BR>
                  Choose 
                  <B>No</B>
                   if you want to keep a light interface
                  <BR>
                  Choose 
                  <B>Yes</B>
                   if you want to be able to add notes on profiles, to save your mails as draft, do put a link to your special relations on your profile ... and some more new features to come ...
                </TD>
                <TD>
                  <SELECT name="PreferenceAdvanced"  class="prefsel" >
                    <OPTION value="Yes" >Yes</OPTION>
                    <OPTION value="No" >No</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD>
                  <P class="preflabel" >Public profile</P>
                </TD>
                <TD>
                  If you choose 
                  <B>Yes</B>
                   your profile will be publically accessible on the web (including on Google).
                  <BR>
                  Note that in this case, addresses, phone number, chat id will not be displayed to not logged members.
                  <BR>
                  If you choose 
                  <B>No</B>
                   (default value) only members will be able to read your profile
                </TD>
                <TD>
                  <SELECT name="PreferencePublicProfile"  class="prefsel" >
                    <OPTION value="Yes" >Yes</OPTION>
                    <OPTION value="No" >No</OPTION>
                  </SELECT>
                </TD>
              </TR>
              <TR>
                <TD align="center"  colspan="3" >
                  <INPUT type="submit"  id="submit"  value="Submit" >
                </TD>
              </TR>
            </TBODY>
          </TABLE>
        <?php
    }
}


?>