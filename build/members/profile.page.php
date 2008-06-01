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
    	$member = $this->member;
		$lang = $this->model->get_profile_language();
		$profile_language = $lang->id;
		$profile_language_code = $lang->ShortCode;
    	
		$words = $this->getWords();
        ?>
        
          <H3><?=$words->get('Actions')?></H3>
          <UL class="linklist" >
            <LI class="icon contactmember16" >
              <A href="contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></A>
            </LI>
            <LI class="icon addcomment16" >
              <A href="addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></A>
            </LI>
            <LI class="icon forumpost16" >
              <A href="http://localhost/bw-trunk-new/htdocs/forums/member/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?>View Forum Posts</A>
            </LI>
          </UL>
          <H3><?=$words->get('MyRelations');?></H3>
          <UL class="linklist" >
	          <?php 
		          $relations = $member->relations;
		          foreach ($relations as $rel) {
	          ?>
          
            <LI>
              <A href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>" >
                <IMG class="framed"  src="<?=PVars::getObj('env')->baseuri?>/photos/???"  height="50px"  width="50px"  alt="Profile" >
              </A>
              <BR>
              <A href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></A>
              <BR>
            </LI>
            <?php } ?>
            <!--<LI>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri"  title="See profile henri" >
                <IMG class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
              </A>
              <BR>
              <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri" >henri</A>
              <BR>
            </LI>-->
          </UL>
        <?php        
    }
    
    
    protected function column_col3()
    {

		$member = $this->member;
		//print_r($this->model->get_profile_language());
		//just to showcase the language selection method below while the
		//profile language switch isn't ready for action 
		//not sure if non-english profile should be shown as default in production
		//$profile_language = $_SESSION['IdLanguage'];
		$lang = $this->model->get_profile_language();
		$profile_language = $lang->id;
		$profile_language_code = $lang->ShortCode;
		 
		$words = $this->getWords();		
		//$words->setLanguage('fr');
        
        ?>
          <DIV class="info" >
            <H3 class="icon info22" ><?=$words->getInLang('ProfileSummary', $profile_language_code);?></H3>
            	<?=$member->get_trad("ProfileSummary", $profile_language); ?>
            <H4><?=$words->getInLang('Languages', $profile_language_code);?></H4>
            
            <P></P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon sun22" ><?=$words->getInLang('ProfileInterests', $profile_language_code);?></H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                	<?php echo $member->get_trad("Hobbies", $profile_language); ?></DIV>
              </DIV>
              <DIV class="c503" >
                <DIV class="subcl" ></DIV>
              </DIV>
            </DIV>
            <H4><?=$words->getInLang('ProfileOrganizations', $profile_language_code);?></H4>
            <P><?php echo $member->get_trad("Organizations", $profile_language); ?></P>
          </DIV>
          <DIV class="info" >
            <H3 class="icon world22" ><?=$words->getInLang('ProfileTravelExperience', $profile_language_code);?></H3>
            <H4><?=$words->getInLang('ProfilePastTrips', $profile_language_code);?></H4>
            <P><?php echo $member->get_trad("PastTrips", $profile_language); ?></P>
            <H4><?=$words->getInLang('ProfilePlannedTrips', $profile_language_code);?></H4>
            <P><?php echo $member->get_trad("PlannedTrips", $profile_language); ?></P>
          </DIV>
          <DIV class="info highlight" >
            <H3 class="icon groups22" ><?=$words->getInLang('ProfileGroups', $profile_language_code);?></H3>
            <?php
            $groups = $member->get_group_memberships();
            foreach($groups as $group) {
            	 $group_id = $group->IdGroup;
            	 $group_name_translated = $words->getInLang($group->Name, $profile_language_code);
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
            <H3 class="icon accommodation22" ><?=$words->getInLang('ProfileAccommodation', $profile_language_code);?></H3>
            <TABLE id="accommodation" >
              <COLGROUP>
                <COL width="35%" ></COL>
                <COL width="65%" ></COL>
              </COLGROUP>
              <TBODY>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('ProfileNumberOfGuests', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->MaxGuest ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('ProfileMaxLenghtOfStay', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->get_trad("MaxLenghtOfStay", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('ProfileILiveWith', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->get_trad("ILiveWith", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('OtherInfosForGuest', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->get_trad("InformationToGuest", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('ProfileRestrictionForGuest', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->get_trad("Restrictions", $profile_language); ?></TD>
                </TR>
                <TR align="left" >
                  <TD class="label" ><?=$words->getInLang('ProfileOtherRestrictions', $profile_language_code);?>:</TD>
                  <TD><?php echo $member->get_trad("OtherRestrictions", $profile_language); ?></TD>
                </TR>
              </TBODY>
            </TABLE>
          </DIV>
          <DIV class="info highlight" >
             
            <H3 class="icon contact22" ><?=$words->getInLang('ContactInfo', $profile_language_code);?></H3>
            <DIV class="subcolumns" >
              <DIV class="c50l" >
                <DIV class="subcl" >
                  <UL>
                    <LI class="label" ><?=$words->getInLang('Name', $profile_language_code);?></LI>
                    <LI><?php echo $member->name?></LI>
                  </UL>
                  <UL>
                    <LI class="label" ><?=$words->getInLang('Address', $profile_language_code);?></LI>
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
                    <LI class="label" ><?=$words->getInLang('Messenger', $profile_language_code);?></LI>
                
                  <?php 
                  $messengers = $member->messengers();
                  $website = $member->WebSite;
                  		
                  if(isset($messengers)) 
                  { ?>
                    <LI>
                      <?php
                      	foreach($messengers as $m) {
                      		echo "<IMG src='".PVars::getObj('env')->baseuri."bw/images/icons1616/".$m["image"]."' width='16' height='16' title='".$m["network"]."' alt='".$m["network"]."' />"
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
                    <LI class="label" ><?=$words->getInLang('Website', $profile_language_code);?></LI>
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