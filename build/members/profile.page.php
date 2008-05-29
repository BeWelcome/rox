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
		$words = $this->getWords();
        ?>
        
          <H3><?=$words->get('Actions');?></H3>
          <UL class="linklist" >
            <LI class="icon contactmember16" >
              <A href="contactmember.php?cid=1" ><?=$words->get('ContactMember');?></A>
            </LI>
            <LI class="icon addcomment16" >
              <A href="addcomments.php?cid=1" ><?=$words->get('addcomments');?></A>
            </LI>
            <LI class="icon forumpost16" >
              <A href="http://localhost/bw-trunk-new/htdocs/forums/member/admin" ><?=$words->get('ViewForumPosts', 7);?>View Forum Posts</A>
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
		//$profile_language = $_SESSION['IdLanguage'];
		$profile_language = $this->model->get_profile_language(); 
		$words = $this->getWords();		
        
        ?>
          <DIV class="info" >
            <H3 class="icon info22" ><?=$words->get('ProfileSummary');?></H3>
            	<?=$member->get_trad("ProfileSummary", $profile_language); ?>
            <H4><?=$words->get('Languages');?></H4>
            <P></P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon sun22" ><?=$words->get('ProfileInterests');?></H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                	<?php echo $member->get_trad("Hobbies", $profile_language); ?></DIV>
              </DIV>
              <DIV class="c503" >
                <DIV class="subcl" ></DIV>
              </DIV>
            </DIV>
            <H4><?=$words->get('ProfileOrganizations');?></H4>
            <P><?php echo $member->get_trad("Organizations", $profile_language); ?></P>
          </DIV>
          <DIV class="info" >
            <H3 class="icon world22" ><?=$words->get('ProfileTravelExperience');?></H3>
            <H4><?=$words->get('ProfilePastTrips');?></H4>
            <P><?php echo $member->get_trad("PastTrips", $profile_language); ?></P>
            <H4><?=$words->get('ProfilePlannedTrips');?></H4>
            <P><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon groups22" ><?=$words->get('ProfileGroups');?></H3>
            <?php
            $groups = $member->get_group_memberships();
            foreach($groups as $group) {
            	 $group_id = $group->IdGroup;
            	 $group_name_translated = $words->get($group->Name);
            	 $group_comment_translated = $member->get_trad_by_tradid($group->Comment, $profile_language);
			?>
	            <H4>
	              <A href="groups.php?action=ShowMembers&IdGroup=<?=$group_id?>" ><?=$group_name_translated?></A>
	            </H4>
	            <P><?=$group_comment_translated?></P>			
			<?php            	  
            }
            ?>
          </DIV>
          <DIV class="info" >
            <H3 class="icon accommodation22" ><?=$words->get('ProfileAccommodation');?></H3>
            <TABLE id="accommodation" >
              <COLGROUP>
                <COL width="35%" ></COL>
                <COL width="65%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('ProfileNumberOfGuests');?>:</TD>
                  <TD><?php echo $member->MaxGuest ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('ProfileMaxLenghtOfStay');?>:</TD>
                  <TD><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('ProfileILiveWith');?>:</TD>
                  <TD><?php echo $member->get_trad("ILiveWith", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('OtherInfosForGuest');?>:</TD>
                  <TD><?php echo $member->get_trad("InformationToGuest", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('ProfileRestrictionForGuest');?>:</TD>
                  <TD><?php echo $member->get_trad("Restrictions", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->get('ProfileOtherRestrictions');?>:</TD>
                  <TD><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></TD>
                </TR>
              </TBODY>
            </TABLE>
          </DIV>
          <DIV class="info highlight" >
             
            <H3 class="icon contact22" ><?=$words->get('ContactInfo');?></H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                  <UL>
                    <LI class="label" ><?=$words->get('Name');?></LI>
                    <LI><?php echo $member->name?></LI>
                  </UL>
                  <UL>
                    <LI class="label" ><?=$words->get('Address');?></LI>
                    <LI><?php echo $member->street?></LI>
                    <LI><?php echo $member->zip	?></LI>
                    <LI><?php echo $member->region ?></LI>
                    <LI><?php echo $member->country ?></LI>
                  </UL>
                </DIV>
              </DIV>
              <DIV class="c50r" >
                <DIV class="subcr" >
                  <UL>
                    <LI class="label" ><?=$words->get('Messenger');?></LI>
                
                  <?php 
                  $messengers = $member->messengers();
                  $website = $member->WebSite;
                  		
                  if(isset($messengers)) 
                  { ?>
                    <LI>
                      <!--<IMG src="./images/icons1616/icon_gtalk.png"  width="16"  height="16"  title="Google Talk"  alt="Google Talk" >-->
                      <!--GoogleTalk: Hidden-->
                      <?php
                      	foreach($messengers as $m) {
                      		echo "<IMG src='".PVars::getObj('env')->baseuri."bw/images/icons/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
                      			.$m["network"].": ".$m["address"]."<br />";
                      	}
                      ?>
                    </LI>
                  <?php 
                  } 
                  ?></UL><?php
                  if(isset($website)) 
                  { ?>
                  <UL>
                    <LI class="label" ><?=$words->get('Website');?></LI>
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