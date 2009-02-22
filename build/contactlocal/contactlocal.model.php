<?php
/**
 * Members verification
 * 
 * @package about ContactLocals
 * @author jeanyves
 * @copyright Copyright (c) 2009, BeVolunteer Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ContactlocalsModel extends RoxModelBase {

	public $IdMess ;
    /**
	* this function returns an structured array with the possible location the member is allow to send a message in
	**/
    function GetAllowedLocation() { 
		$Scope=MOD_right::get()->rightScope("ContactLocation") ;
		$Scope=str_replace(",",";",$Scope) ;
		$tt_temp=explode(";",$Scope) ;
		$tt=array() ;
		for ($ii=0;$ii<count($tt_temp);$ii++) {
			$IdLoc=$tt_temp[$ii] ;
			if ((is_numeric($tt_temp[$ii])) and (!empty($tt_temp[$ii]))) {
				$rr=$this->singleLookup("select id,Name from cities where id=".$IdLoc) ;
				if (isset($rr->id)) {
					$rr->Type="City" ;
					$rr->Choice=" City: ".$rr->Name ;
				}
				else {
					$rr=$this->singleLookup("select id,Name from regions where id=".$IdLoc) ;
					if (isset($rr->id)) {
						$rr->Type="Region" ;
						$rr->Choice=" Region: ".$rr->Name ;
					}
					else {
						$rr=$this->singleLookup("select id,Name from countries where id=".$IdLoc) ;
						if (isset($rr->id)) {
							$rr->Type="Country" ;
							$rr->Choice=" Country: ".$rr->Name ;
						}
						else {
							$ss="Found Location #".$IdLoc." in scope, does'nt match any city or region or country" ;
						    MOD_log::get()->write($ss,"contactlocation") ; 				
							die ($ss) ;
						}
					}
				}
				array_push($tt,$rr) ;
			}
		}
		return($tt) ;
	} // end of GetAllowedLocation
		

    /**
	* this function returns an structured array with the possible location for a specified message
	**/
    function GetMessageLocation($IdMess) { 
		$tt_temp=$this->bulkLookup("select * from localvolmessages_location where IdLocalVolMessage=".$IdMess) ;
		$tt=array() ;
		for ($ii=0;$ii<count($tt_temp);$ii++) {
			$IdLoc=$tt_temp[$ii]->IdLocation ;
			$rr=$this->singleLookup("select id,Name from cities where id=".$IdLoc) ;
			if (isset($rr->id)) {
				$rr->Type="City" ;
				$rr->Choice=" City: ".$rr->Name ;
			}
			else {
				$rr=$this->singleLookup("select id,Name from regions where id=".$IdLoc) ;
				if (isset($rr->id)) {
					$rr->Type="Region" ;
					$rr->Choice=" Region: ".$rr->Name ;
				}
				else {
					$rr=$this->singleLookup("select id,Name from countries where id=".$IdLoc) ;
					if (isset($rr->id)) {
						$rr->Type="Country" ;
						$rr->Choice=" Country: ".$rr->Name ;
					}
					else {
						$ss="Found Location #".$IdLoc." in scope, does'nt match any city or region or country" ;
					    MOD_log::get()->write($ss,"contactlocation") ; 				
						die ($ss) ;
					}
				}
			}
			array_push($tt,$rr) ;
		}
		return($tt) ;
	} // end of GetMessageLocation
		
    /**
	* this function returns an structured array with the possible location the member is allow to send a message in
	**/
    function GetMemberLanguages() { 
		 
		$sQuery="SELECT memberslanguageslevel.IdLanguage AS IdLanguage,languages.Name as LanguageName,languages.ShortCode AS ShortCode from languages,memberslanguageslevel " ;
		$sQuery.=" where IdMember=".$_SESSION["IdMember"]." and memberslanguageslevel.IdLanguage =languages.id order by memberslanguageslevel.Level asc" ;
		$qry = $this->dao->query($sQuery);
		if (!$qry) {
			throw new PException('contactlocalmodel::GetMemberLanguages Could not retrieve the  languages!');
		}

		// for all the records
		$tt=array() ;
		while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
			array_push( $tt,$rr) ;
		}
//		print_r($tt) ; die(0) ;
		return($tt) ;
	} // end of GetMemberLanguages

    /**
     * this function returns the list of pending messages
     **/
    function LoadList($IdMess=0) {
	if (empty($IdMess)) {
		$sQuery="select  localvolmessages.*,localvolmessages.id as IdMess,members.id as 'IdCreator', members.Username as 'CreatorUsername' from localvolmessages,members where members.id=localvolmessages.IdSender order by localvolmessages.Status,localvolmessages.created" ;
	}
	else {
		$this->IdMess=$IdMess ;
		$sQuery="select  localvolmessages.*,localvolmessages.id as IdMess,members.id as 'IdCreator', members.Username as 'CreatorUsername' from localvolmessages,members where members.id=localvolmessages.IdSender and localvolmessages.id=".$IdMess ;
	}
	$qry = $this->dao->query($sQuery);
    if (!$qry) {
        throw new PException('polls::LoadList Could not retrieve the localvolmessages!');
    }

	// for all the records
	$tt=array() ;
    while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
		$rr->ChosenLocations=$this->GetMessageLocation($rr->IdMess) ;

		// retrieve all trads for Title
        $query = "select translations.*,EnglishName,ShortCode,translations.id as IdTrans,IdLanguage from translations,languages where IdLanguage=languages.id and IdTrad=".$rr->IdTitleText." order by translations.created asc" ;
        $s = $this->dao->query($query);
		if (!$s) {
			throw new PException('failed for '.$query.'!');
		}
		$rr->ListTitleText=array() ;
		while ($row=$s->fetch(PDB::FETCH_OBJ)) {
			array_push($rr->ListTitleText,$row) ;
		}

		// retrieve all trads for Message
        $query = "select translations.*,EnglishName,ShortCode,translations.id as IdTrans from translations,languages where IdLanguage=languages.id and IdTrad=".$rr->IdMessageText." order by translations.created asc" ;
        $s = $this->dao->query($query);
		$rr->ListMessageText=array() ;
		while ($row=$s->fetch(PDB::FETCH_OBJ)) {
			array_push($rr->ListMessageText,$row) ;
		}


		array_push( $tt,$rr) ;
	}
	return($tt) ;
} // end of LoadList
    
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
     * this function delete a location for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function DelLocation($post) {
		$this->IdMess=$IdMess=$post['IdMess'] ;
		$IdLocation=$post['IdLocation'] ;
		$ss="delete from localvolmessages_location where IdLocation=".$IdLocation." and IdLocalVolMessage=".$IdMess;
		$result = $this->dao->query($ss);
		if (!$result) {
			throw new PException('DelLocation::Failed to DelLocation to message #'.$IdMess);
		}
		MOD_log::get()->write("contactlocal : delete location #".$IdLocation." IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of DelLocation

    /**
     * this function delete a translation for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function DelTranslation($post) {
		$words = new MOD_words();
		$this->IdMess=$IdMess=$post['IdMess'] ;
		$IdLanguage=$post["IdLanguage"] ;
		$rMess=$this->singleLookup("select * from localvolmessages where id=".$IdMess) ;

		$squery="delete from translations where IdTrad=".$rMess->IdTitleText." and IdLanguage=".$IdLanguage ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('DeletetTranslation::Failed to delete IdLanguage #'.$IdLanguage.' (the translations) for message #'.$IdMess);
		}

			
		MOD_log::get()->write("contactlocal : deleting translation in language #".$IdLanguage." to IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of DelTranslation

    /**
     * this function delete a whole a message
		 * @IdMess is teh id of the message
		 * returns true if the poll is added with success
     **/
    function DeleteMessage($IdMess) {
		$this->IdMess=$IdMess ;
		$rMess=$this->singleLookup("select * from localvolmessages where id=".$IdMess) ;

		$squery="delete from translations where IdTrad=".$rMess->IdTitleText ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('DeleteMessage::Failed to delete full message #'.$IdMess);
		}


		$squery="delete from localvolmessages_location where IdLocalVolMessage=".$IdMess ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('DeleteMessage::Failed to delete IdLanguage #'.$IdLanguage.' (the locations) for message #'.$IdMess);
		}

			

		$squery="delete from localvolmessages where id=".$IdMess ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('DeleteMessage::Failed to delete IdLanguage #'.$IdLanguage.' (master record) for message #'.$IdMess);
		}


			
		MOD_log::get()->write("contactlocal : full deleting for IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of DeleteMessage

	
	
    /**
     * this function adds a translation for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function AddTranslation($post) {
		$words = new MOD_words();
		$this->IdMess=$IdMess=$post['IdMess'] ;
		$IdLanguage=$post["IdLanguage"] ;
		$rMess=$this->singleLookup("select * from localvolmessages where id=".$IdMess) ;

		$ss=$this->dao->escape($post['IdTitleText']) ;
 		$words->ReplaceInFTrad($ss,"localvolmessages.IdTitleText",$rMess->id,$rMess->IdTitleText) ;
			
		$ss=$this->dao->escape($post['IdMessageText']) ;
 		$words->ReplaceInFTrad($ss,"localvolmessages.IdMessageText",$rMess->id,$rMess->IdMessageText) ;
			
		MOD_log::get()->write("contactlocal : Adding translation in language #".$IdLanguage." to IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of AddTranslation

    /**
     * this function update a translation for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function UpdateTranslation($post) {
		$words = new MOD_words();
		$this->IdMess=$IdMess=$post['IdMess'] ;
		$IdLanguage=$post["IdLanguage"] ;
		$rMess=$this->singleLookup("select * from localvolmessages where id=".$IdMess) ;

		$ss=$this->dao->escape($post['IdTitleText']) ;
 		$words->ReplaceInFTrad($ss,"localvolmessages.IdTitleText",$rMess->id,$rMess->IdTitleText) ;
			
		$ss=$this->dao->escape($post['IdMessageText']) ;
 		$words->ReplaceInFTrad($ss,"localvolmessages.IdMessageText",$rMess->id,$rMess->IdMessageText) ;
			
		MOD_log::get()->write("contactlocal : Updating  translation in language #".$IdLanguage." to IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of UpdateTranslation

    /**
     * this function adds a location for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function AddLocation($post) {
		$this->IdMess=$IdMess=$post['IdMess'] ;
		$IdLocation=$post['IdLocation'] ;
		$ss="replace into localvolmessages_location(IdLocation,IdLocalVolMessage) values(".$IdLocation.",".$IdMess.") " ;
		$result = $this->dao->query($ss);
		if (!$result) {
			throw new PException('AddLocation::Failed to AddLocation to message #'.$IdMess);
		}
		MOD_log::get()->write("contactlocal : Adding new location #".$IdLocation." IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of AddLocation

		/**
     * this function allows to create a new message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
		 * according to $post['IdMess'] the message will be inserted or updated
     **/
    function recordnewmessage($post) {
			$words = new MOD_words();
			if (empty($post['IdMess'])) {
				$IdMess=0 ;
			}
			else {
				$IdMess=$post['IdMess'] ;
			}
			$IdLanguage=$post['IdLanguage'] ;
			if (isset($post["PurposeDescription"])) {
				$PurposeDescription=$this->dao->escape($post["PurposeDescription"]) ;
			}
			if ($IdMess==0) { // IF it is a new mssage
				
				$ss="insert into localvolmessages(created,IdSender,IdTitleText,IdMessageText,PurposeDescription) " ;
				$ss=$ss."values(now(),".$_SESSION["IdMember"].",0,0,'".$PurposeDescription."')" ;
 				$result = $this->dao->query($ss);
				if (!$result) {
					throw new PException('recordnewmessage::Failed to insert a Message ');
				}
				$this->IdMess=$IdMess=$result->insertId();

				$ssIdTitleText=$this->dao->escape($post['Title']) ;
				$IdTitleText=$words->InsertInFTrad($ssIdTitleText,"localvolmessages.IdTitleText",$IdMess, $_SESSION["IdMember"], 0) ;

				$ss=$this->dao->escape($post['MessageText']) ;
				$IdMessageText=$words->InsertInFTrad($ss,"localvolmessages.IdMessageText",$IdMess, $_SESSION["IdMember"], 0) ;
				
				$ss="update localvolmessages set IdTitleText=$IdTitleText,IdMessageText=$IdMessageText where id=$IdMess" ;
 				$result = $this->dao->query($ss);
				if (!$result) {
	   			throw new PException('recordnewmessage::Failed to add back the Title and Text ');
				}

				MOD_log::get()->write("contactlocal : ".$ssIdTitleText." created IdMess=#".$IdMess,"contactlocal") ; 
			} // end if it is a new message
		} // end of recordnewmessage

} // end of ContactLocalsModel




?>
