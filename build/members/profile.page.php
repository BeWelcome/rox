<?php


class ProfilePage extends MemberPage
{
    protected function teaserHeadline()
    {
        echo 'Profile of someone';
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'profile';
    }
    
    
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
            <LI class="icon forumpost16" >
              <A href="http://localhost/bw-trunk-new/htdocs/forums/member/admin" >View Forum Posts</A>
            </LI>
          </UL>
          <H3>My special relations</H3>
          <UL class="linklist" >
            <LI>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin"  title="See profile admin" >
                <IMG class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
              </A>
              <BR>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=admin" >admin</A>
              <BR>
            </LI>
            <LI>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri"  title="See profile henri" >
                <IMG class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
              </A>
              <BR>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri" >henri</A>
              <BR>
            </LI>
          </UL>
        <?php        
    }
    
    
    protected function column_col3()
    {
        ?>
          <DIV class="info" >
            <H3 class="icon info22" >Profile summary</H3>
            <P>One day Australia, in less than two years Turkey</P>
            <H4>Languages</H4>
            <P>français (Fluent), English (Beginner) </P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon sun22" >My interests</H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" ></DIV>
              </DIV>
              <DIV class="c503" >
                <DIV class="subcl" ></DIV>
              </DIV>
            </DIV>
            <H4>Organizations I belong to</H4>
            <P>HC</P>
          </DIV>
          <DIV class="info" >
            <H3 class="icon world22" >Travel experiences</H3>
            <H4>Past trips</H4>
            <P>Japan, Tunisia</P>
            <H4>Planned trips</H4>
            <P>Turkey</P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon groups22" >I am in the following groups</H3>
            <H4>
              <A href="groups.php?action=ShowMembers&IdGroup=4" >Sailors</A>
            </H4>
            <P>I like sails and wind</P>
            <H4>
              <A href="groups.php?action=ShowMembers&IdGroup=9" >Sports</A>
            </H4>
            <P>I like sports! I am a superstar.</P>
          </DIV>
          <DIV class="info" >
            <H3 class="icon accommodation22" >Accommodation</H3>
            <TABLE id="accommodation" >
              <COLGROUP>
                <COL width="35%" ></COL>
                <COL width="65%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" >Max number of guests:</TD>
                  <TD>155</TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Maximum length of stay:</TD>
                  <TD>0 days</TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I Live With:</TD>
                  <TD>My computer</TD>
                </TR>
                <TR align="left" >
                  <TD class="label" > Other information for guests:</TD>
                  <TD>no need to ask it's a technical profile:</TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Restrictions:</TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Other restrictions:</TD>
                  <TD>be happy</TD>
                </TR>
              </TBODY>
            </TABLE>
          </DIV>
          <DIV class="info highlight" >
             
            <H3 class="icon contact22" >Contact Information</H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                  <UL>
                    <LI class="label" >Name</LI>
                    <LI>nothing * *</LI>
                  </UL>
                  <UL>
                    <LI class="label" >Address</LI>
                    <LI>* member doesn't want to display</LI>
                    <LI>* Zip is hidden Vitré</LI>
                    <LI>Bretagne</LI>
                    <LI>France</LI>
                  </UL>
                </DIV>
              </DIV>
              <DIV class="c50r" >
                <DIV class="subcr" >
                  <UL>
                    <LI class="label" >Messenger</LI>
                    <LI>
                      <IMG src="./images/icons1616/icon_gtalk.png"  width="16"  height="16"  title="Google Talk"  alt="Google Talk" >
                       GoogleTalk: Hidden
                    </LI>
                  </UL>
                  <UL>
                    <LI class="label" >Web site</LI>
                    <LI>
                      <A href="http://www.bewelcome.org" >http://www.bewelcome.org</A>
                    </LI>
                  </UL>
                </DIV>
              </DIV>
            </DIV>
          </DIV>
        <?php
    }
}


?>