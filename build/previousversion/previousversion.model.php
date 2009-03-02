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
class PreviousversionModel extends RoxModelBase {

	public $IdMess ;
	private $rMess ;  // Will be used to store the message
	public $TableName ;
	public $IdRecord ;


    
    /**
	* this function returns the list matching records
	**/
    function FindRes($post) {
		$swhere=" 1 " ;
		$data->IdRecord=0 ;
		if (!empty($post["IdRecord"])) {
			$data->IdRecord=$post["IdRecord"] ;
			$swhere=$swhere." and IdInTable=".$post["IdRecord"] ;
		}
		$data->IdMember=0 ;
		$data->Username="" ;
		if (!empty($post["IdMember"])) {
			$data->IdMember=$post["IdMember"] ;
			if (is_numeric($data->IdMember)) {
				$m=$this->singleLookup("select id,Username from members where id=".$data->IdMember) ;
			}
			else {
				$m=$this->singleLookup("select id,Username from members where Username='".$data->IdMember."'") ;
			}
			$data->Username=$m->Username ;
			$data->IdMember=$m->id ;
			$swhere=$swhere." and IdMember=".$data->IdMember ;
		}
		
		if (!empty($post["String"])) {
			$data->String=$post["String"] ;
			$swhere=$swhere." and XmlOldVersion like '%".$post["String"]."%'" ;
		}
		
		
		
		$data->current=0 ;
		if (!empty($post["TableName"])) {   // To try to retrieve the previous value
			$data->TableName=$post["TableName"] ;
			$swhere=$swhere." and TableName ='".$data->TableName."'" ;
			$tt=explode(".",$data->TableName) ;
			$TableName=$tt[0]; 
			if (isset($tt[1])) $column=$tt[1]; 
			if (!empty($data->IdRecord)) {
				$data->current=$this->singleLookup("select created,".$column." as CurVal from ".$TableName." where id=".$data->IdRecord) ;
			}
		}
		
		$ss="select * from previousversion where ".$swhere." order by created desc" ;
//		echo $ss,"<br /> " ;
		$data->sQuery=$ss ;
		$data->previousvalues=$this->bulkLookup($ss) ;
		
//		print_r($data) ; die(0) ;
		
		return($data) ;
	} // end of FindRes

	
    
    /**
	* this function returns the list matching records
	**/
    function LoadRes($post) {
		$swhere=" 1 " ;
		$data->IdRecord=0 ;
		if (!empty($post["IdRecord"])) {
			$data->IdRecord=$post["IdRecord"] ;
			$swhere=$swhere." and IdInTable=".$post["IdRecord"] ;
		}
		$data->IdMember=0 ;
		$data->Username="" ;
		if (!empty($post["IdMember"])) {
			$data->IdMember=$post["IdMember"] ;
			if (is_numeric($data->IdMember)) {
				$m=$this->singleLookup("select id,Username from members where id=".$data->IdMember) ;
			}
			else {
				$m=$this->singleLookup("select id,Username from members where Username='".$data->IdMember."'") ;
			}
			$data->Username=$m->Username ;
			$data->IdMember=$m->id ;
			$swhere=$swhere." and IdMember=".$data->IdMember ;
		}
		
		if (!empty($post["String"])) {
			$data->String=$post["String"] ;
			$swhere=$swhere." and XmlOldVersion like '%".$post["String"]."%'" ;
		}
		
		$data->current=0 ;
		if (!empty($post["TableName"])) {   // To try to retrieve the previous value
			$data->TableName=$post["TableName"] ;
			$swhere=$swhere." and TableName ='".$data->TableName."'" ;
			$tt=explode(".",$data->TableName) ;
			$TableName=$tt[0]; 
			if (isset($tt[1])) $column=$tt[1]; 
			if (!empty($data->IdRecord)) {
				$data->current=$this->singleLookup("select created,".$column." as CurVal from ".$TableName." where id=".$data->IdRecord) ;
			}
		}
		
		$ss="select * from previousversion where ".$swhere." order by created desc" ;
//		echo $ss,"<br /> " ;
		$data->sQuery=$ss ;
		$qry = $this->dao->query($data->sQuery);
		if (!$qry) {
			throw new PException('Previousversion:LoadRes Could not retrieve the XML!');
		}

		// for all the records
		$ttN=array() ;
		$ttV=array() ;
		$data->previousvalues=array() ;
		while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
			array_push($data->previousvalues,$rr) ;
			$tNames=$this->GetFieldsName($rr->XmlOldVersion) ;
			$tValues=$this->GetFieldsValues($rr->XmlOldVersion) ;
			array_push($ttN,$tNames) ;
			array_push($ttV,$tValues) ;
		}
		
		$data->Fields=$ttN ;
		$data->Values=$ttV ;
		/*
		echo "<br />Names<br />" ;
		print_r($ttN) ;
		echo "<br />Values<br />" ;
		print_r($ttV) ;
		
		die(0) ;
		*/
		
//		print_r($data) ; die(0) ;
		
		return($data) ;
	} // end of LoadRes	
	
	
	
	function GetFieldsValues($sparam) {
//	echo "\$sparam",htmlentities($sparam),"<br />" ;
		$ss=strip_tags_content($sparam,"<value>") ;
//	echo "\$ss1",htmlentities($ss),"<br />" ;
		$ss=str_replace("</value>","",$ss) ;
//	echo "\$ss2",htmlentities($ss),"<br />" ;
		return(explode("<value>",$ss)) ;
	} 
	function GetFieldsName($sparam) {
//	echo "\$sparam",htmlentities($sparam),"<br />" ;
		$ss=strip_tags_content($sparam,"<field>") ;
//	echo "\$ss1",htmlentities($ss),"<br />" ;
		$ss=str_replace("</field>","",$ss) ;
//	echo "\$ss2",htmlentities($ss),"<br />" ;
		return(explode("<field>",$ss)) ;
	} 
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
} // end of Find



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

function strip_tags_content($text, $tags = '', $invert = FALSE) {

  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
  $tags = array_unique($tags[1]);
   
  if(is_array($tags) AND count($tags) > 0) {
    if($invert == FALSE) {
      return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    else {
      return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
    }
  }
  elseif($invert == FALSE) {
    return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
  }
  return $text;
}


?>
