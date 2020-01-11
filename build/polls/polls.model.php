<?php
/**
 * Members verification
 *
 * @package about verifymembers
 * @author jeanyves
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class PollsModel extends RoxModelBase {

    /**
     * this function returns true if the user can contribute to this poll, false elsewher
		 * @IdPoll is the id of the poll
		 * @$Email is the mandatory Email which must be provided for a not logged user
     **/
    function CanUserContribute($IdPoll,$Email="") {
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll." /* can user contribute */") ;

// Check that the poll is open
			if ($rPoll->Status!="Open") {
				MOD_log::get()->write("CanUserContribute in a closed poll","polls") ;
				return(false) ;
			}
// Check that we are is the range time people can contribute
	  	 if (time()<strtotime($rPoll->Started)) {
				MOD_log::get()->write("CanUserContribute in a not started poll time()=".time()." strtotime('".$rPoll->Started."')=".$rPoll->Started,"polls") ;
			 	 return(false) ;
			 }
	  	 if ((time()>strtotime($rPoll->Ended)) and ($rPoll->Ended!="0000-00-00 00:00:00")) {
//			 echo " time()=",time()," strtotime(\$rPoll->Ended)=",strtotime($rPoll->Ended)," ",$rPoll->Ended ;
		     	 MOD_log::get()->write("CanUserContribute in an already ended poll","polls") ;
			 	 return(false) ;
			 }

// If it is a memberonly poll check that the member is logged in
			if ($rPoll->ForMembersOnly=="Yes") {
				if ((!$this->session->has( "IdMember" ) or ($this->session->get("MemberStatus")!="Active"))) {
					MOD_log::get()->write("trying to vote in an member only post and not logged in","polls") ;
					return (false) ;
				}
			}
			else { // case not for member only, and Email must be provided
				if (empty($Email)) {
      	  MOD_log::get()->write("CanUserContribute in without being logged but without email","polls") ;
					return(false) ;
				}
				if (($rPoll->CanChangeVote=='No') and ($this->HasAlreadyContributed($IdPoll,$Email))) {
					MOD_log::get()->write("CanUserContribute in an already contributed post with Email".$Email,"polls") ;
					return(false) ;
				}
			}
			if (($rPoll->CanChangeVote=='No') and ($this->HasAlreadyContributed($IdPoll,"",$this->session->get("IdMember")))) {
		      	  MOD_log::get()->write("CanUserContribute in an already contributed post ","polls") ;
					return(false) ;
			}
			return(true) ;
		} // end of CanUserContribute



    /**
     * this function returns false if the result of the poll are not available
		 * @IdPoll is the id of the poll
     **/
    function GetPollResults($IdPoll,$Email="",$IdMember=0) {
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll." /* GetPollResults */") ;
			if ($rPoll->ResultsVisibility=='Not Visible') {
				return(false) ;
			}
			// Todo: proceed with status VisibleAfterVisit in a better way (for now someone who knows the results of a not closed poll)

			$Data = new \stdClass();
			$Data->rPoll=$rPoll ;
			$rr=$this->singlelookup("select count(*) as TotContrib from polls_contributions where  IdPoll=".$IdPoll) ;
			$TotContrib=$Data->TotContrib=$rr->TotContrib ;
			$Data->Choices=$this->bulkLookup("select *,(Counter/".$TotContrib.")*100 as Percent from polls_choices where IdPoll=".$IdPoll." order by Counter,created desc") ;
			$Data->Contributions=$this->bulkLookup("select comment,Username from polls_contributions,members where  IdPoll=".$IdPoll." and comment <>'' and members.id=polls_contributions.IdMember") ;

			return($Data) ;

	  } // end of GetPollResults

    /**
     * this function returns true if the user has allready contributed to the specific post
		 * @IdPoll is the id of the poll
		 * @$Email is the mandatory Email which must be provided for a not logged user (optional)
		 * @$IdMember id of the member (optional)
     **/
    function HasAlreadyContributed($IdPoll,$Email="",$IdMember=0) {
			if (!empty($IdMember)) {
				$rr=$this->singleLookup("select count(*) as cnt from polls_contributions where IdMember=".$IdMember." and IdPoll=".$IdPoll) ;
				if ($rr->cnt>0) return(true) ;
			}
			if (!empty($Email)) {
				$rr=$this->singleLookup("select count(*) as cnt from polls_contributions where Email='".$Email." and IdPoll=".$IdPoll) ;
				if ($rr->cnt>0) return(true) ;
			}
			return(false) ;
		} // end of HasAlreadyContributed

    /**
     * this function allows to create/update  poll
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
		 * according to $post['IdPoll'] the poll will be inserted or updated
     **/
    function UpdatePoll($post) {
			$words = new MOD_words();
		  if (empty($post['IdPoll'])) {
				$IdPoll=0 ;
			}
			else {
				$IdPoll=$post['IdPoll'] ;
			}
			if ($IdPoll==0) { // IF it is a new poll
				$IdGroup=0 ;


				$ss="insert into polls(IdCreator,IdGroupCreator,created,Title,Description,Status,AllowComment,ResultsVisibility,Started) " ;
				$ss=$ss."values(".$this->session->get("IdMember").",0,now(),0,0," ;
				$ss=$ss."'Project','No','Not Visible',now())" ;
 				$result = $this->dao->query($ss);
				if (!$result) {
	   			throw new PException('UpdatePoll::Failed to insert a poll ');
				}
				$IdPoll=$result->insertId();

				$ss=$this->dao->escape($post['Title']) ;
				$IdTitle=$words->InsertInFTrad($ss,"polls.Title",$IdPoll, $this->session->get("IdMember"), 0) ;

				$ss=$this->dao->escape($post['Description']) ;
				$IdDescription=$words->InsertInFTrad($ss,"polls.Description",$IdPoll, $this->session->get("IdMember"), 0) ;

				$ss="update polls set Title=$IdTitle,Description=$IdDescription where id=$IdPoll" ;
 				$result = $this->dao->query($ss);
				if (!$result) {
					throw new PException('UpdatePoll::Failed to add back the Title and Description ');
				}

				$TIdGrouRestricted=explode(",",$post["GroupIdLimit"]) ;
				for ($ii=0;$ii<count($TIdGrouRestricted);$ii++) {
					$IdGroup=(int)$TIdGrouRestricted[$ii] ;
					if ($IdGroup==0) continue ;
					$sSql="insert into polls_list_allowed_groups(IdPoll,IdGroup) values(".$IdPoll.",".$IdGroup.") " ;
					$rPoll=$this->dao->query($sSql) ;
				}

				MOD_log::get()->write("poll : ".$post["Title"]." created IdPoll=#".$IdPoll,"polls") ;
			} // end if it is a new poll
			else {
				$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll) ;
			if (!( ($this->session->has( "IdMember" ) and ($rPoll->IdCreator==$this->session->get("IdMember")) and ($rPoll->Status=="Projet")) or (MOD_right::get()->HasRight("Poll","update")) )) {
			MOD_log::get()->write("Forbidden to update  poll #".$IdPoll ,"polls") ;
					die ("Sorry forbidden for you") ;
			}

			$IdCreator=$rPoll->IdCreator ;
				if (isset($post['CreatorUsername'])) {
					$rr=$this->singleLookup("select id from members where Username='".$post['CreatorUsername']."' and Status='Active'") ;
					if (isset($rr->id)) {
						$IdCreator=$rr->id ;
					}
				}


				if (($post["Status"]=="Open")and ($rPoll->Started=="0000-00-00 00:00:00")) {
					$Started="now()" ;
					MOD_log::get()->write("Starting Id Poll#".$IdPoll." for the first time","polls") ;
				}
				else {
					$Started="'".$rPoll->Started."'" ;
				}

				$ss="update polls set IdCreator=".$IdCreator.",IdGroupCreator=".$rPoll->IdGroupCreator ;
				$ss=$ss.",Status='".$post["Status"]."'" ;
				$ss=$ss.",TypeOfChoice='".$post["TypeOfChoice"]."'" ;
				$ss=$ss.",Started=".$Started ;
				$ss=$ss.",Ended='".$post["Ended"]."'" ;
				$ss=$ss.",ResultsVisibility='".$post["ResultsVisibility"]."'" ;
				$ss=$ss.",AllowComment='".$post["AllowComment"]."'" ;
				$ss=$ss.",Anonym='".$post["Anonym"]."'" ;
				$ss=$ss.",Ended='".$post["Ended"]."'" ;
				$ss=$ss." where id=".$IdPoll;
 				$result = $this->dao->query($ss);


				$ss=$this->dao->escape($post['Description']) ;
		 		$words->ReplaceInFTrad($ss,"polls.Description",$rPoll->id,$rPoll->Description) ;

				$ss=$this->dao->escape($post['Title']) ;
		 		$words->ReplaceInFTrad($ss,"polls.Title",$rPoll->id,$rPoll->Title) ;

				$TIdGrouRestricted=explode(",",$post["GroupIdLimit"]) ;
				$this->dao->query("delete from polls_list_allowed_groups where IdPoll=".$IdPoll) ;
				for ($ii=0;$ii<count($TIdGrouRestricted);$ii++) {
					$IdGroup=(int)$TIdGrouRestricted[$ii] ;
					if ($IdGroup==0) continue ;
					$this->dao->query("insert into polls_list_allowed_groups(IdPoll,IdGroup) values(".$IdPoll.",".$IdGroup.") ") ;
				}


				MOD_log::get()->write("poll : ".$post["Title"]." updating IdPoll=#".$IdPoll." Set Status=".$rPoll->Status." to ".$post["Status"],"polls") ;
			}
		} // end of UpdatePoll

    /**
     * this function allows to add a choice to a give poll
		 * @post all parameters from  argpost
		 * returns true if choice was added with success
     **/
    function AddChoice($post) {
			$words = new MOD_words();

			$IdPoll=$post['IdPoll'] ;
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll." /* Add Choice */") ;

			if (!( ($this->session->has( "IdMember" ) and ($rPoll->IdCreator==$this->session->get("IdMember")) and ($rPoll->Status=="Projet")) or (MOD_right::get()->HasRight("Poll","update")) )) {
			MOD_log::get()->write("Forbidden to add poll choice for poll #".$IdPoll ,"polls") ;
					die ("Sorry forbidden for you") ;
			}


			$ss="insert into polls_choices(IdPoll,IdChoiceText,Counter,created) values(".$rPoll->id.",0,0,now())" ;
 			$result = $this->dao->query($ss);
			if (!$result) {
	   			throw new PException('AddChoice::Failed to add back the insert the choice ');
			}
			$IdChoice=$result->insertId();

			$ss=$this->dao->escape($post['ChoiceText']) ;
			$IdChoiceText=$words->InsertInFTrad($ss,"polls_choices.IdChoiceText",$IdChoice, $this->session->get("IdMember"), 0) ;

			$ss="update polls_choices set IdChoiceText=$IdChoiceText where id=$IdChoice" ;
 			$result = $this->dao->query($ss);
			if (!$result) {
	   			throw new PException('AddChoice::Failed update the IdChoiceText ');
			}

			MOD_log::get()->write("pollchoice : <b>".$post["ChoiceText"]."</b> created IdPollChoice=#".$IdChoice." for poll #".$IdPoll ,"polls") ;

			return(true) ;
		} // end of AddChoice

    /**
     * this function allows to add a choice to a give poll
		 * @post all parameters from  argpost
		 * returns true if choice was added with success
     **/
    function UpdateChoice($post) {
			$words = new MOD_words();

			$IdPoll=$post['IdPoll'] ;
			$IdPollChoice=$post['IdPollChoice'] ;
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll." /* UpdatedChoice */") ;

			if ((empty($rPoll->id)) or $rPoll->Status=='Open') {
					$sLog="it is not possible to change The possible choices for poll #".$rPoll->id." because it is an Open one or there is no such a poll" ;
   			   		MOD_log::get()->write($sLog,"polls") ;
			}

			if (!( ($this->session->has( "IdMember" ) and ($rPoll->IdCreator==$this->session->get("IdMember")) and ($rPoll->Status=="Projet")) or (MOD_right::get()->HasRight("Poll","update")) )) {
			MOD_log::get()->write("Forbidden to update poll choice for poll #".$IdPoll ,"polls") ;
					die ("Sorry forbidden for you") ;
			}


			$rPollChoice=$this->singleLookup("select * from polls_choices where id=".$IdPollChoice." /* UpdatedChoice*/") ;

			$ss=$this->dao->escape($post['ChoiceText']) ;
		 	$words->ReplaceInFTrad($ss,"polls_choices.IdChoiceText",$rPollChoice->id,$rPollChoice->IdChoiceText) ;

			MOD_log::get()->write("pollchoice : update to <b>".$post["ChoiceText"]."</b> IdPollChoice=#".$IdPollChoice." for poll #".$IdPoll ,"polls") ;

			return(true) ;
		} // end of UpdatedChoice

    /**
     * this function adds the vote for a given member
		 * @post is the array of the arg_post to be given by the controller
		 * @$Email is the mandatory Email which must be provided for a not logged user (optional)
		 * @$IdMember id of the member (optional)
		 * returns true if the vote is added with success
     **/
    function AddVote($post,$Email="",$IdMember=0) {

		  if (empty($post['IdPoll'])) {
				die ("Fatal error In AddVote \$post['IdPoll'] is missing") ;
			}
			$IdPoll=$post['IdPoll'] ;
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll." and Status='Open' /* Add Vote */") ;

			if (empty($rPoll->id)) {
					$sLog="it is not possible to contribute to poll #".$IdPoll ;
   			   		MOD_log::get()->write($sLog,"polls") ;
 		   			throw new PException($sLog);
			}

			// If there is a group list, test if the current member is in the group list
			if (!$this->IsMemberAllowed($rPoll)) {
					$sLog="To contribute to this poll ".$rPoll->id ." specific membership in some group is needed ";
					MOD_log::get()->write($sLog,"polls") ;
					throw new PException($sLog);
			}



// Prevents the same member from voting twice
			if (!empty($IdMember)) {
				$rPreviousContrib=$this->singleLookup("select * from polls_contributions where IdMember=".$IdMember." and IdPoll=".$IdPoll) ;
			}
			elseif (!empty($Email)) {
				$rPreviousContrib=$this->singleLookup("select * from polls_contributions where Email='".$Email."' and IdPoll=".$IdPoll) ;
			}

			if (!(empty($rPreviousContrib->IdPoll))) {
					$sLog="Members #".$IdMember." has already contributed to poll #".$IdPoll ;
   			   		MOD_log::get()->write($sLog,"polls") ;
 		   			throw new PException($sLog);
			}

			$rContribList=$this->bulkLookup("select * from polls_choices  where IdPoll=".$IdPoll) ;

			if ($rPoll->TypeOfChoice=='Exclusive') {
					if (!empty($post['ExclusiveChoice'])) { // blank votes are allowed
							$ss="update polls_choices set Counter=Counter+1 where id=".$post['ExclusiveChoice']." and IdPoll=".$IdPoll ;
  		 				$s = $this->dao->query($ss);
   	 					if (!$s) {
      		   			throw new PException('Failed to add a vote ');
   	 					}
							$Choice=$post['ExclusiveChoice'] ;
					}
					else {
							$Choice=0 ;
					}

					$ss="insert into polls_contributions(IdMember,Email,created,comment,IdPoll) values (".$IdMember.",'".$Email."',now(),'".$this->dao->escape($post['Comment'])."',".$IdPoll.")" ;
  		 		$s = $this->dao->query($ss);
   	 			if (!$s) {
      		   throw new PException('Failed to insert into polls_contributions ');
   	 			}

					if ($rPoll->Anonym=='No') {
						$ss="insert into polls_record_of_choices(IdMember,Email,created,IdPollChoice,IdPoll) values (".$IdMember.",'".$Email."',now(),".$Choice.",".$IdPoll.")" ;
  		 			$s = $this->dao->query($ss);
   	 				if (!$s) {
      		   	throw new PException('Failed to insert into polls_record_of_choices ');
   	 				}

					}

      		MOD_log::get()->write("Vote Exclusive vote from poll #".$IdPoll." for IdMember=#".$IdMember." ".$Email,"polls") ;
			}

			if ($rPoll->TypeOfChoice=='Inclusive') {
				$ss="insert into polls_contributions(IdMember,Email,created,comment,IdPoll) values (".$IdMember.",'".$Email."',now(),'".$this->dao->escape($post['Comment'])."',".$IdPoll.")" ;
  		 	$s = $this->dao->query($ss);
   	 		if (!$s) {
      		   throw new PException('Failed to insert into polls_contributions ');
   	 		}
				for ($ii=0;$ii<count($rContribList);$ii++) {
				$rContrib=$rContribList[$ii] ;
//				echo "\$post[\"choice_".$rContrib->id."\"]=",$post["choice_".$rContrib->id],"<br />" ;
					if ((isset($post["choice_".$rContrib->id])) and ($post["choice_".$rContrib->id]=='on')) { // if this choice was made
						$ss="update polls_choices set Counter=Counter+1 where id=".$rContrib->id ;
  		 			$s = $this->dao->query($ss);
						$Choice=$rContrib->id ;
   	 				if (!$s) {
      		   throw new PException('Failed to add a vote ');
   	 				}

						if ($rPoll->Anonym=='No') {
							$ss="insert into polls_record_of_choices(IdMember,Email,created,IdPollChoice,IdPoll) values (".$IdMember.",'".$Email."',now(),".$Choice.",".$IdPoll.")" ;
  		 				$s = $this->dao->query($ss);
   	 					if (!$s) {
      		   		throw new PException('Failed to insert into polls_record_of_choices ');
   	 					}
						}
					} // end if this choice was made

				}
      	MOD_log::get()->write("add Inclusive vote from poll #".$IdPoll." for IdMember=#".$IdMember." ".$Email,"polls") ;
			}
			if ($rPoll->TypeOfChoice=='Ordered') {
				die("Add  in ordered votes not implemented") ;
			}

			return(true) ;
		} // end of AddVote


		 /**
     * this function cancels the vote for a given member
		 * @IdPoll is the id of the poll
		 * @$Email is the mandatory Email which must be provided for a not logged user (optional)
		 * @$IdMember id of the member (optional)
		 * returns true if the vote is cancelled
     **/
    function CancelVote($IdPoll,$Email="",$IdMember=0) {
			$rPoll=$this->singleLookup("select * from polls where id=".$IdPoll) ;
			if ($rPoll->Status=="Closed") {
      	  MOD_log::get()->write("Cannot cancel vote from poll #".$IdPoll." which is closed","polls") ;
					return(false) ;
			}
			if ($rPoll->CanChangeVote=="No") {
      	  MOD_log::get()->write("Cannot cancel vote from poll #".$IdPoll." which doesn't allow to change vote","polls") ;
					return(false) ;
			}
			$rContrib=array() ;

			$wherefordelete=" (false==true) " ; // very important to avoid to delete all votes
			if (!empty($IdMember)) {
				$rr=$this->singleLookup("select * from polls_contributions where IdPoll=".$IdPoll) ;
				if (!isset($rr->id)) return(false) ;
				$wherefordelete="IdMember='".$IdMember."'" ;
			}
			elseif (!empty($Email)) {
				$rr=$this->singleLookup("select * from polls_contributions where Email='".$Email."'") ;
				if (!isset($rr->id)) return(false) ;
				$wherefordelete="Email='".$Email."'" ;
			}

			$rContrib=$this->bulkLookup("select * from polls_record_of_choices  where IdPoll=".$IdPoll." and ".$wherefordelete) ;

			if ($rPoll->TypeOfChoice=='Exclusive') {
				for ($ii=0;$ii<count($rContrib);$ii++) { // In fact we should have just one record here
					$ss="update polls_choices set Counter=Counter-1 where id=".$rContrib[$ii]->IdPollChoice ;
  		 		$s = $this->dao->query($ss);
   	 			if (!$s) {
      		   throw new PException('Failed to delete a vote ');
   	 			}
				}
				$ss="delete from polls_contributions where IdPoll=".$IdPoll." and ".$wherefordelete ;
  		 	$s = $this->dao->query($ss);
   	 		if (!$s) {
      		   throw new PException('Failed to delete a vote (contribution)');
   	 		}

				$ss="delete from polls_record_of_choices where IdPollChoice=".$IdPoll." and ".$wherefordelete ;
  		 	$s = $this->dao->query($ss);
   	 		if (!$s) {
      		   throw new PException('Failed to delete a vote (polls_record_of_choices)');
   	 		}
      	MOD_log::get()->write("Cancelling Exclusive vote from poll #".$IdPoll." for IdMember=#".$IdMember." ".$Email,"polls") ;

			}
			if ($rPoll->TypeOfChoice=='Inclusive') {
				for ($ii=0;$ii<count($rContrib);$ii++) {
					$ss="update polls_choices set Counter=Counter-1 where id=".$rContrib[$ii]->IdPollChoice ;
  		 		$s = $this->dao->query($ss);
   	 			if (!$s) {
      		   throw new PException('Failed to delete a vote ');
   	 			}

				}
				$ss="delete from polls_contributions where IdPoll=".$IdPoll." and ".$wherefordelete ;
  		 	$s = $this->dao->query($ss);
   	 		if (!$s) {
      		   throw new PException('Failed to delete a vote (contribution)');
   	 		}

				$ss="delete from polls_record_of_choices where IdPollChoice=".$IdPoll." and ".$wherefordelete;
  		 	$s = $this->dao->query($ss);
   	 		if (!$s) {
      		   throw new PException('Failed to delete a vote (polls_record_of_choices)');
   	 		}
      	MOD_log::get()->write("Cancelling Inclusive vote from poll #".$IdPoll." for IdMember=#".$IdMember." ".$Email,"polls") ;
			}
			if ($rPoll->TypeOfChoice=='Ordered') {
				die("Delete of ordered votes not implemented") ;
			}

			return(true) ;
		} // end of CancelVote

    /**
     * this function prepares the contribution for a poll
		 * @IdPoll is the id of the poll
     **/
    function PrepareContribute($IdPoll=0) {
    	$Data = new \stdClass();
			$Data->rPoll=$this->singleLookup("select * from polls where id=".$IdPoll) ;
			$choices_alreadydone=array() ;
			if ($this->HasAlreadyContributed($IdPoll)) {
				if ($Data->rPoll->Anonym=="No") {
					$ss="select * from polls_record_of_choices where IdMember=".$this->session->get("IdMember")." and IdPoll=".$IdPoll ;
					$choices_alreadydone=$this->bulkLookup($ss) ;
				}
			}
			$Data->choices_alreadydone=$choices_alreadydone ;
			$Data->Choices=$this->bulkLookup("select * from polls_choices where IdPoll=".$IdPoll) ;
			return($Data) ;
		} // end of PrepareContribute

    /**
     * this function loads the data of a poll
		 * @IdPoll is the id of the poll
     **/
    function LoadPoll($IdPoll=0) {
			$ss="select polls.*,members.Username as 'CreatorUsername', g.id as IdGroupRestricted from (polls)" ;
			$ss.=" left join members on members.id=polls.IdCreator " ;
			$ss.=" left join polls_list_allowed_groups pg on polls.id = pg.IdPoll " ;
			$ss.=" left join groups g on pg.IdGroup = g.id ";
			$ss=$ss. " where polls.id=".$IdPoll ;
			$Data = new \stdClass();
			$Data->rPoll=$this->singleLookup($ss) ;
			$Data->Choices=$this->bulkLookup("select * from polls_choices where IdPoll=".$IdPoll." order by created asc") ;

			return($Data) ;
		} // end of LoadPoll

    /**
     * this function load the list of the polls with a certain status
		 * @PollStatus is the statuis which allow to filter for the status of some poll
     **/
    function LoadList($PollStatus="") {
				$words = new MOD_words();
				if (empty($PollStatus)) {
					$where="" ;
				}
				else {
					$where = " WHERE p.Status='".$PollStatus."'";
				}


        $sQuery="SELECT p.*, m.Username AS 'CreatorUsername', g.id AS GroupId, g.name AS GroupName from polls p " ;
				$sQuery.=" left join members m on m.id=p.IdCreator " ;
				$sQuery.=" left join polls_list_allowed_groups pg on pg.IdPoll = p.id " ;
				$sQuery.=" left join groups g on g.id=pg.IdGroup " ;
				$sQuery=$sQuery.$where." order by p.created desc" ;
				$tt=array() ;
      	$qry = $this->dao->query($sQuery);
      	if (!$qry) {
            throw new PException('polls::LLoadList Could not retrieve the polls!');
      	}

		if ($this->session->has( "IdMember" )) {
			$IdMember=$this->session->get("IdMember") ;
		}
		else {
			$IdMember=0 ;
		}

		// for all the records
      	while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {

					// If there is a group list, test if the current member is in the group list
				if (!$this->IsMemberAllowed($rr)) {
							continue ; // Skip this record
				}


					if (!empty($rr->IdGroupCreator)) { // In case the polls is created by a group find back the name of this group
						$rGroup=$this->singleLookup("select * from groups where id=".$rr->IdGroupCreator) ;
						$rr->GroupCreatorName=$words->getFormatted("Group_" . $rGroup->Name);
					}
					$rContrib=$this->singleLookup("select count(*) as cnt from polls_contributions where IdPoll=".$rr->id) ;
					$rr->NbContributors=$rContrib->cnt ;

					// This is the logic for the possible action (may be this could be better in the controller)
					$rr->PossibleActions = "";

					// Only owner of admin with proper right can update the poll
					if ( ($this->session->has( "IdMember" ) and ($rr->IdCreator==$this->session->get("IdMember")) and ($rr->Status=="Projet")) or (MOD_right::get()->HasRight("Poll","update")) ) {
						$rr->PossibleActions=$rr->PossibleActions."<a class='btn btn-sm btn-primary' href=\"polls/update/".$rr->id."\">".$words->getFormatted("polls_adminlink")."</a>" ;
					}


					if ($this->HasAlreadyContributed($rr->id,"",$this->session->get("IdMember"))) {
						$rr->PossibleActions=$words->getFormatted("polls_youhavealreadyvoted") . "<br>" ;
						if (($rr->CanChangeVote=="Yes") and ($rr->Status=="Open") ) {
						  $rr->PossibleActions.="<a class='btn btn-sm btn-primary' href=\"polls/cancelvote/".$rr->id."\">".$words->getFormatted("polls_remove_vote")."</a>" ;
						}
						if (($rr->ResultsVisibility=="VisibleAfterVisit") and ($rr->Status!="Closed")) {
						  $rr->PossibleActions=$rr->PossibleActions."<a class='btn btn-sm btn-primary' href=\"polls/seeresults/".$rr->id."\">".$words->getFormatted("polls_seeresults")."</a>" ;
						}
					}
					if ($this->CanUserContribute($rr->id,"",$this->session->get("IdMember"))) {
						$rr->PossibleActions=$rr->PossibleActions."<a class='btn btn-sm btn-primary' href=\"polls/contribute/".$rr->id."\">".$words->getFormatted("polls_contribute")."</a>" ;
					}
					if ($rr->Status=="Closed") {
						$rr->PossibleActions.="<a class='btn btn-sm btn-primary' href=\"polls/results/".$rr->id."\">".$words->getFormatted("polls_seeresults")."</a>" ;
					}

					array_push( $tt,$rr) ;

				}
				return($tt) ;

    } // end of LoadList

    /**
     * this function retruns true if th member is allowed to contribute to the poll according to
	 * his groups membership
	 * @$rPoll is a record of a poll table
	 * @$IdMember is the member to consider if it is 0, teh current member will be used
	**/
    function IsMemberAllowed($rPoll,$_IdMember=0) {
		if  (empty($_IdMember)) {
			$IdMember=0 ;
			if (!empty($this->session->get("IdMember"))) {
				$IdMember=$this->session->get("IdMember") ;
				if ($rPoll->IdCreator==$IdMember) {
					return(true) ; // It makes sense that the creator of the poll can always access it
				}
			}
		}
		else {
			$IdMember=$_IdMember ;
		}
		$rCount=$this->singleLookup("select count(*) as cnt from polls_list_allowed_groups as p  where p.IdPoll=".$rPoll->id) ;
		if ($rCount->cnt >0) { // If they are groups limitation, we are going to test in the member is within these limits
			$rCount=$this->singleLookup("select count(*) as cnt from membersgroups as m,polls_list_allowed_groups as p  where m.IdGroup=p.IdGroup and m.IdMember=".$IdMember." and m.Status='In'  and p.IdPoll=".$rPoll->id) ;
			if ($rCount->cnt<=0) {
				return(false) ;
			}
		}

		if (!empty($rPoll->WhereToRestrictMember)) { // If there is another special restriction
																					 // ie something the currend member Must match
																					 // for exemple select count(*) as cnt from members where Gender='Female' and members.id=$IdMember" to only query for female members
			$sSQL=str_replace("\$IdMember",$IdMember,$rPoll->WhereToRestrictMember) ;
			$rPossible=$this->singleLookup($sSQL) ;
			if ($rPossible->cnt<=0) {
				return(false) ;
			}
		}
		return(true) ; // all test ok, member can use the poll
	}

} // end if IsMemberAllowed




?>
