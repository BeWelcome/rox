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

		$member = $this->member;
		//just to showcase the language selection method below while the
		//profile language switch isn't ready for action 
		//not sure if non-english profile should be shown as default in production
		$profile_language = $_SESSION['IdLanguage'];
        
        ?>
          <DIV class="info" >
            <H3 class="icon info22" >Profile summary</H3>
            	<?php echo $member->get_trad("ProfileSummary", $profile_language); ?>
            <H4>Languages</H4>
            <P></P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon sun22" >My interests</H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                	<?php echo $member->get_trad("Hobbies", $profile_language); ?></DIV>
              </DIV>
              <DIV class="c503" >
                <DIV class="subcl" ></DIV>
              </DIV>
            </DIV>
            <H4>Organizations I belong to</H4>
            <P><?php echo $member->get_trad("Organizations", $profile_language); ?></P>
          </DIV>
          <DIV class="info" >
            <H3 class="icon world22" >Travel experiences</H3>
            <H4>Past trips</H4>
            <P><?php echo $member->get_trad("PastTrips", $profile_language); ?></P>
            <H4>Planned trips</H4>
            <P><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></P>
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
                  <TD><?php echo $member->MaxGuest ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Maximum length of stay:</TD>
                  <TD><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >I Live With:</TD>
                  <TD><?php echo $member->get_trad("ILiveWith", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" > Other information for guests:</TD>
                  <TD><?php echo $member->get_trad("InformationToGuest", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Restrictions:</TD>
                  <TD><?php echo $member->get_trad("Restrictions", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" >Other restrictions:</TD>
                  <TD><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></TD>
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
                    <LI><?php echo $member->name()?></LI><!--name & address business logic (=what to display) should be handled in the model-->
                  </UL>
                  <UL>
                    <LI class="label" >Address</LI>
                    <LI><?php echo $member->street()?></LI>
                    <LI><?php echo $member->zip	()?></LI>
                    <LI><?php echo $member->region()?></LI>
                    <LI><?php echo $member->country()?></LI>
                  </UL>
                </DIV>
              </DIV>
              <DIV class="c50r" >
                <DIV class="subcr" >
                  <?php 
                  $messengers = $member->messengers();
                  $website = $member->WebSite;
                  		
                  if(isset($messengers)) 
                  { ?>
                  <UL>
                    <LI class="label" >Messenger</LI>
                    <LI>
                      <!--<IMG src="./images/icons1616/icon_gtalk.png"  width="16"  height="16"  title="Google Talk"  alt="Google Talk" >-->
                      <!--GoogleTalk: Hidden-->
                      <?php
                      	
                      	foreach($messengers as $m) {
                      		echo "<IMG src='".SCRIPT_BASE."htdocs/images/icons/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
                      			.$m["network"].": ".$m["address"]."<br />";
                      	}
                      ?>
                    </LI>
                  </UL>
                  <?php 
                  } 
                  
                  if(isset($website)) 
                  { ?>
                  <UL>
                    <LI class="label" >Web site</LI>
                    <LI>
                      <A href="http://<?php echo $member->WebSite ?>" ><?php echo $member->WebSite ?></A>
                    </LI>
                  </UL>
                  <?php } ?>
                </DIV>
              </DIV>
            </DIV>
          </DIV>
        <?php
        
        $member = $this->member;
        echo '<pre><h1>this->member</h1><br />';
        print_r($member);
        echo '<hr><h1>this->member->trads</h1><br />';
        print_r($member->trads);
        //echo '<hr><h1>this->member->address</h1><br />';
        //print_r($member->address);
        
        echo '</pre>';
                
    }
}


?>