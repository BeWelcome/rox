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
class VerifyMembersModel extends PAppModel {
/**
* sVerifierLevel function returns the current verification level of member
* @IdMember (optional) the Id of the member to obtaon verification level, if not provided current member in session will be use
* returns : a string with the member level 
* a member can be a "Normal" member (one who was not veridied)
*                   "VerifiedByNormal" member (if he was verified by a normal member)
*                   "VerifiedByVerified" member (if he was verified by an approved verifier)
*                   "VerifiedByApproved" member (if he has right to be a verifier)
*					  
**/
 	   function sVerifierLevel($cid=-1) {
	   			$sRet="Normal" ;
				$IdMember=0 ; // TODO here we should add a mustlogin() thing
				if ($cid>0) {
				   $IdMember=$cid ;
				}
				else {
				   if (isset($_SESSION["IdMember"])) {
				   	  $IdMember=$_SESSION["IdMember"] ;
				   }
				}
        		$query ="SELECT max(Type) as Type from verifiedmembers where IdVerified=".$IdMember ; 
        		$s = $this->dao->query($query);
        		if (!$s) {
            	   		 throw new PException('Could not retrieve verifiedmembers for ".$IdMember." !');
        		}
				$rr = $s->fetch(PDB::FETCH_OBJ);
				if (isset($rr->Type)) {
				   		 $sRet=$rr->Type ;
						 if ($sRet=="Buggy") {
            	   		 	throw new PException('Buggy Value in verifiedmembers for IdMember=".$IdMember." !');
						 }
				}
				

				// if the member is a verifier and has ApprovedVerifier scope, this information will supersed all others			
				if (HasRight("Verifier","ApprovedVerifier")) {
				   $sRet="ApprovedVerifier" ;  
				}
				return($sRet) ;
				
	   } // end of sVerifierLevel


/**
* this function insert a new verified member (or replace the record if one from the same verifier exist for this member) 
**/
 	   function AddNewVerified() {
	   } // AddNewVerified

/**
* this function load the private data of member IdMember
* @Username the id of the member (can also be the IdMember, it will be converted to a Username)
* @Password is the password of the member to access to private data, it must  
* @ returns a structure with the data or and empty structure if password/Username dont match
**/
 	   function LoadPrivateData($Username="",$EnterPassord="") {

	   			if (is_int($Username)) { // Will aslo be ready to accept an IdMember
        		   $query ="SELECT * from members where id=".$Username." and password=PassWord=PASSWORD('" . trim($EnterPassord)."')" ; // there is a TODO to avoid thi query if slow to go in logs with a plain text password 
				} 
				else {
        		   $query ="SELECT * from members where Username='".$Username."' and PassWord=PASSWORD('" . trim($EnterPassord)."')" ;// there is a TODO to avoid thi query if slow to go in logs with a plain text password 
				}
				
        		$s = $this->dao->query($query);
        		if (!$s) {
            	   		 throw new PException('Failed in verifiedmembers.model::LoadPrivateData for '.$Username);
        		}
				$m = $s->fetch(PDB::FETCH_OBJ);
				if (!isset($m->id)) { // If no data found it means password or username was wrong
				   return(NULL) ;
				}

				// Retrieve the addresse		
				$query="select addresses.id as IdAddress,StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.id as IdRegion,cities.Name as CityName from addresses,countries,regions,cities where IdMember=" . $m->id . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry" ;
				$s = $this->dao->query($query);
        		if (!$s) {
            	   		 throw new PException('Problem reading member address in in verifiedmembers.model: for '.$m->Username);
        		}
				$rAddresse = $s->fetch(PDB::FETCH_OBJ) ;
				if (!isset($rAddresse->IdAddress)) {
            	   		 throw new PException('Problem reading member address in in verifiedmembers.model: no address found for '.$m->Username);
				}
				
				
				// Password has been verified, load the privated data
				$m->FirstName=AdminReadCrypted($m->FirstName) ;
				$m->SecondName=AdminReadCrypted($m->SecondName) ;
				$m->LastName=AdminReadCrypted($m->LastName) ;

				$m->CityName=$rAddresse->CityName ;
				
				$m->HouseNumber=AdminReadCrypted($rAddresse->HouseNumber) ;
				$m->StreetName=AdminReadCrypted($rAddresse->StreetName) ;
				$m->Zip=AdminReadCrypted($rAddresse->Zip) ;
				
				
				return($m) ;
	   } // LoadPrivateData

/**
* this function load the list of verification done for member Username
* @Username the id of the member (can also be the IdMember, it will be converted to a Username)
* @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
**/
 	   function LoadVerifiers($Username="") {
/*
	   			if (is_int($Username)) { // Will aslo be ready to accept an IdMember
        		   $query ="SELECT * from members where id=".$Username." " ; 
				} 
				else {
        		   $query ="SELECT * from members where Username='".$Username."' and PassWord=PASSWORD('" . trim($EnterPassord)."')" ;// there is a TODO to avoid thi query if slow to go in logs with a plain text password 
				}
				
        		$s = $this->dao->query($query);
        		if (!$s) {
            	   		 throw new PException('Failed in verifiedmembers.model::LoadPrivateData for '.$Username);
        		}
				
				$tt=array() ;
				while ($rr = $s->fetch(PDB::FETCH_OBJ);
				
				}

				
				
				return($tt) ;
				*/
	   } // LoadVerifiers
}




?>
