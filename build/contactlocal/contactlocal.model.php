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
	private $rMess ;  // Will be used to store the message
	

    /**
	* this function returns true if the current user can do change on current message
	**/
    function CanWorkWith($IdMess=-1) { 
		if ($IdMess>0) {
			$this->rMess=$this->singleLookup("select * from localvolmessages where id=".$IdMess) ;
		}
		if (empty($this->rMess) or ($this->rMess==false)) {
			die("contaclocal : \$this->rMess is not set") ;
		}
		if (MOD_right::get()->rightScope("ContactLocation","All")) return (true) ;
		if ($_SESSION["IdMember"]==$rMess->IdCreator) return(true) ;
		return(false) ;
	} // end of CanWorkWith


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
    function GetLocation($IdLoc) { 
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
		return($rr) ;
	} // end of GetLocation

    /**
	* this function returns an structured array with the possible location for a specified message
	**/
    function GetMessageLocation($IdMess) { 
		$tt_temp=$this->bulkLookup("select * from localvolmessages_location where IdLocalVolMessage=".$IdMess) ;
		$tt=array() ;
		for ($ii=0;$ii<count($tt_temp);$ii++) {
			$rr=$this->GetLocation($tt_temp[$ii]->IdLocation) ;
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
	* this function Triggers a message
	* $IdMess is the id of the message to trig
	* $DoTrigger is to set to true to really trig the message, elsewhere it will just count the message to trig
	* returns a string with a feedback about what happen
	**/
    function Trigger($IdMess=0,$DoTrigger=false) {
		$words = new MOD_words();
		global $fTradIdLastUsedLanguage  ; // This is set for the fTrad function (will define which language to use)
		$sRet="" ; // This will be the return string
		
		if ($DoTrigger) {
			$sRet="Effective triggering\n" ;
		}
		else {
			$sRet="Simulation no effective triggering" ;
		}
	
		if (empty($IdMess)) {
			die("Trigger : parameter IdMess is missing") ;
		}
		else {
			$this->IdMess=$IdMess ;
			$sQuery="select  localvolmessages.*,localvolmessages.id as IdMess,members.id as 'IdCreator', members.Username as 'CreatorUsername' from localvolmessages,members where members.id=localvolmessages.IdSender and localvolmessages.Status='ToSend' and localvolmessages.id=".$IdMess ;
		}
		$rMess=$this->singleLookup($sQuery) ;

		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowe to work with message #".$IdMess) ;
		}
	

		$rMess->ChosenLocations=$this->GetMessageLocation($rMess->IdMess) ;

		$CountMembers=0 ;
		// Loop to enqueue all members who are in the location
		$ListOfUsers=array() ;
		foreach ($rMess->ChosenLocations as $loc) {
			$layoutbits = new MOD_layoutbits();

			$sLog="Enqueing for ".$loc->Choice." IdLocation=#".$loc->id ;
			$sRet=$sRet."<br />\n".$sLog ;
		    if ($DoTrigger) MOD_log::get()->write($sLog,"contactlocation") ; 				
			if ($loc->Type=="City") {
				$squery="select members.id,Username from members  where members.IdCity=".$loc->id." and members.Status in ('Active','Inactive')" ;
			}
			else if ($loc->Type=="Region") {
				$squery="select members.id,Username from members,cities  where members.IdCity=cities.id and cities.IdRegion=".$loc->id." and members.Status in ('Active','Inactive')" ;
			}
			else if ($loc->Type=="Country") {
				$squery="select members.id,Username from members,cities  where members.IdCity=cities.id and cities.IdCountry=".$loc->id." and members.Status in ('Active','Inactive')" ;
			}

			$qry = $this->dao->query($squery);
			while ($m=$qry->fetch(PDB::FETCH_OBJ)) { // Browse all members
				if ($layoutbits->GetPreference("PreferenceLocalEvent",$m->id)!="Yes") {
					continue ; // Skip preferences of members who chosen not to receive localevent notification
				}
				for ($ii=0,$AlreadyIn=false;$ii<count($ListOfUsers);$ii++) {	// Test if the member is already enqueued to avoid duplicates
					if ($ListOfUsers[$ii]==$m->id) {
						$AlreadyIn=true ;
						break ;
					}
				} // end of for $ii
				if (!$AlreadyIn) {
					$CountMembers++ ;
					$fTradIdLastUsedLanguage=$MemberIdLanguage = $layoutbits->GetPreference("PreferenceLanguage",$m->id);

					$MessageText=$this->dao->escape($words->fTrad($rMess->IdMessageText,false,$MemberIdLanguage)) ; // Try to force the translation of the language to suit the receiver default language
					
					$ss="insert into messages(MessageType,IdMessageFromLocalVol,created,IdReceiver,IdSender,Status,Message,IdTriggerer,JoinMemberPict)" ;
					$ss=$ss." values('LocalVolToMember',".$rMess->id.",now(),".$m->id.",".$rMess->IdCreator.",'ToSend','".$MessageText."',".$_SESSION["IdMember"].",'Yes')" ;

					if ($DoTrigger) {
						$sLog=" Enqueing members <b>".$m->Username."</b> in language #".$MemberIdLanguage." for ".$loc->Choice." IdLocation=#".$loc->id ;
						$qry = $this->dao->query($ss);
						if (!$qry) {
							throw new PException('failed for '.$ss.'!');
						}
						$squery="update localvolmessages set localvolmessages.Status='Sent'  where id=".$IdMess ;
						$qry2 = $this->dao->query($squery);
						if (!$qry2) {
							throw new PException('failed for '.$squery.'!');
						}
						MOD_log::get()->write($sLog,"contactlocation") ; 
					}
					else {
						$sLog=" Could be sent to member <b>".$m->Username."</b> in language #".$MemberIdLanguage." for ".$loc->Choice." IdLocation=#".$loc->id ;
					}
					$sRet=$sRet."<br />\n".$sLog ;
				}
			} // end of while
	
			if ($DoTrigger) {
				$sLog=" for Message #".$rMess->id." ".$CountMembers." members notified" ;
				MOD_log::get()->write($sLog,"contactlocation") ; 				
			}
			else {
				$sLog=" for Message #".$rMess->id." ".$CountMembers." are to be notified (when message will be triggered)" ;
			}
			$sRet=$sRet."<br />\n".$sLog ;
	
		} // end of for each location	
		return($sRet) ;
		die(sRet) ;
	} // end of Trigger
    
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
        throw new PException('ContactClocal::LoadList Could not retrieve the localvolmessages!');
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
     * this function delete a location for a message
		 * @post is the array of the arg_post to be given by the controller
		 * returns true if the poll is added with success
     **/
    function DelLocation($post) {
		$this->IdMess=$IdMess=$post['IdMess'] ;
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowe to work with message #".$IdMess) ;
		}
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
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowe to work with message #".$IdMess) ;
		}
		$IdLanguage=$post["IdLanguage"] ;
		$rMess=$this->rMess;

		$squery="delete from translations where IdTrad=".$rMess->IdTitleText." and IdLanguage=".$IdLanguage ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('DeletetTranslation::Failed to delete IdLanguage #'.$IdLanguage.' (the translations) for message #'.$IdMess);
		}

			
		MOD_log::get()->write("contactlocal : deleting translation in language #".$IdLanguage." to IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of DelTranslation

    /**
	* this function SetToSend a whole a message
	 * @IdMess is the id of the message

	**/
    function SetToSend($IdMess) {
		$this->IdMess=$IdMess ;
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowed to work with message #".$IdMess) ;
		}
		$rMess=$this->rMess ;


		$squery="update localvolmessages set Status='ToSend'  where Status='ToApprove' and id=".$IdMess ;
		$result = $this->dao->query($squery);
		if (!$result) {
			throw new PException('SetToSend::Failed to change Status for message #'.$IdMess);
		}
			
		MOD_log::get()->write("contactlocal : set ToSend IdMess=#".$IdMess,"contactlocal") ; 
		
	} // end of SetToSend

    /**
	* this function delete a whole a message
	 * @IdMess is teh id of the message
	**/
    function DeleteMessage($IdMess) {
		$this->IdMess=$IdMess ;
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowed to work with message #".$IdMess) ;
		}
		$rMess=$this->rMess ;

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
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowe to work with message #".$IdMess) ;
		}
		$rMess=$this->rMess ;

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
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowed UdpateTranslation with message #".$IdMess) ;
		}
		$rMess=$this->rMess ;

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
		if (!$this->CanWorkWith($IdMess)) {
			die("Your are not allowed to AddLocation with message #".$IdMess) ;
		}
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
			$ssGivelLocation=" No location done" ;
			
			if (isset($post['IdLocation'])) {
				$IdLocation=$post['IdLocation'] ;
				$ss="replace into localvolmessages_location(IdLocation,IdLocalVolMessage) values(".$IdLocation.",".$IdMess.") " ;
				$result = $this->dao->query($ss);
				if (!$result) {
					throw new PException('recordnewmessage::Failed to AddLocation to message #'.$IdMess);
				}
			}
			MOD_log::get()->write("contactlocal : ".$ssIdTitleText." created IdMess=#".$IdMess.$ssGivelLocation,"contactlocal") ; 
		} // end if it is a new message
	} // end of recordnewmessage

} // end of ContactLocalsModel




?>
