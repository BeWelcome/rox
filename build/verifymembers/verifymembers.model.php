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
class VerifyMembersModel extends RoxModelBase
{
    /**
     * sVerifierLevel function returns the current verification level of member
     * @IdMember (optional) the Id of the member to obtaon verification level, if not provided current member in session will be use
     * returns : a string with the member level 
     * a member can be a "Normal" member (one who was not veridied)
     *                   "VerifiedByNormal" member (if he was verified by a normal member)
     *                   "VerifiedByVerified" member (if he was verified by an approved verifier)
     *                   "VerifiedByApproved" member (if he has right to be a verifier)
     *					  
     */
    function sVerifierLevel($member_id=-1)
    {
        $member_id = (int)$member_id;
        
        $sRet= "VerifiedByNormal" ;  
        if ($member_id > 0) {
            // everything is cool
        } else if (isset($_SESSION["IdMember"])) {
            // use the member who is currently logged in.
            $member_id=$_SESSION["IdMember"] ;
        } else {
            // can't help it, so we return false.
            return false;
        }
        
        if (!$rr = $this->singleLookup( 
            "
SELECT  max(Type) AS Type
FROM    verifiedmembers
WHERE   IdVerified = $member_id
            "
        )) {
        } else if ("Buggy" == $rr->Type) {
            // problem
            throw new PException('Buggy Value in verifiedmembers for IdMember=".$IdMember." !');
        }
		 else {
        	$sRet = $rr->Type;
		 }
        
        
        // if the member is a verifier and has ApprovedVerifier scope, this information will supersed all others
        // comment by lemon-head: Better do this in the controller?		
        if (HasRight("Verifier","ApprovedVerifier")) {
            // TODO: HasRight does only check the currently logged-in user, not the given argument!
            $sRet= "VerifiedByApproved" ;  
        }
      	 return $sRet;
    } // end of sVerifierLevel


    /**
     * this function insert a new verified member (or replace the record if one from the same verifier exist for this member)
	  * @post is the post from the previous form
	  * nota a member cannot verified himself 
     **/
    function AddNewVerified($post) {
	 	 
        // accept both 
        if ($m = $this->singleLookup("SELECT  id,Username from members where Status='Active' and id=".$post["IdMemberToVerify"]))  {
			$IdVerifiedMember=$m->id ; 
		 }
		 else {
		 	  return(false) ; // Return walse if verification faile
		 }
		 
		 
		 $VerifierLevel=$this->sVerifierLevel($_SESSION["IdMember"]) ;
		 
//	    echo "\$post=" ;print_r($post) ;
		$AddressConfirmed='False' ;
		$NameConfirmed='False' ;
		if (isset($post['NameConfirmed']) and $post['NameConfirmed']='on') {
		   $NameConfirmed='True' ;
		}
		if (isset($post['AddressConfirmed']) and $post['AddressConfirmed']='on') {
		   $AddressConfirmed='True' ;
		}
		
		 // Check if the current member has allready verified this one, if so it will be an update
		 $AllreadyVerified=$this->singleLookup("SELECT  * from verifiedmembers where IdVerifier=".$_SESSION["IdMember"]." and IdVerified=".$IdVerifiedMember) ;
		 if (isset($AllreadyVerified->id)) { // If the member was already verified : do an update
		 	$ss="update verifiedmembers set IdVerifier=".$_SESSION["IdMember"].",IdVerified=".$IdVerifiedMember.",AddressVerified='".$AddressConfirmed."',NameVerified='".$NameConfirmed."',Comment='".mysql_real_escape_string(addslashes($post["comment"]))."',Type='".$VerifierLevel."' where id=".$AllreadyVerified->id ;
        	MOD_log::get()->write("Update Verify members ".$m->Username." previous value comment[".$AllreadyVerified->Comment."] AddressVerified=".$AllreadyVerified->AddressVerified.",NameVerified=".$AllreadyVerified->NameVerified,"VerifyMember") ;
			
		 }
		 else {
		 	$ss="insert into verifiedmembers(created,IdVerifier,IdVerified,AddressVerified,NameVerified,Comment,Type) values(now(),".$_SESSION["IdMember"].",".$IdVerifiedMember.",'".$AddressConfirmed."','".$NameConfirmed."','".mysql_real_escape_string(addslashes($post["comment"]))."','".$VerifierLevel."')" ;
        	MOD_log::get()->write("Has verify member ".$m->Username,"VerifyMember") ;
		 }
  		 $s = $this->dao->query($ss);

   	 if (!$s) {
      		   throw new PException('Failed to verify member '.$m->Username);
   	 }
		 return(true) ;
    } // AddNewVerified

    /**
     * This function loads the private data of member IdMember
     * @cid can be an id or a username of the member.
     * @given_password is the password sent with the form.  
     * @return a structure with the data, or false if password/Username dont match
     **/
    function LoadPrivateData($cid, $given_password)   {
        // jeanYves : there is a TODO to avoid this query if slow to go in logs with a plain text password
        // comment by lemon-head: I think we should encrypt the pw on PHP side, not in SQL.
        // It is said in MySQL documentation
        // that the PASSWORD() function is not recommended to be used by applications.
        
        // accept both 
        $where_cid = is_numeric($cid) ? 'id='.(int)$cid : 'Username="'.mysql_real_escape_string($cid).'"';
        
        // TODO: What does this "password=PassWord=PASSWORD(...)" mean?  --lemon-head 
		 // it was a bug -- jeanyves
		 $ss="
SELECT  *
FROM    members
WHERE   $where_cid
AND     PassWord=PASSWORD('".trim($given_password)."')" ;
        if (!$m = $this->singleLookup($ss)) {
            // user not found! explain something?
			 
            return array(); // Returns empty array if no value found
		}
        
        // Retrieve the addresse		
        if (!$rAddresse = $this->singleLookup(
            "
SELECT
    addresses.id   AS IdAddress,
    StreetName,
    Zip,
    HouseNumber,
    countries.id   AS IdCountry,
    cities.id      AS IdCity,
    regions.id     AS IdRegion,
    cities.Name    AS CityName
FROM
    addresses,
    countries,
    regions,
    cities
WHERE
    IdMember = $m->id              AND
    addresses.IdCity = cities.id   AND
    regions.id = cities.IdRegion   AND
    countries.id = cities.IdCountry
            "
        )) {
            // address not found -> we are not amused.
            return false;
        }
        
        // Password has been verified, load the encrypted data
        foreach (array('FirstName', 'SecondName', 'LastName') as $key) {
            $m->$key = AdminReadCrypted($m->$key);
        }
        foreach (array('HouseNumber', 'StreetName', 'Zip') as $key) {
            $m->$key = AdminReadCrypted($rAddresse->$key);
        }
        
        $m->CityName = $rAddresse->CityName ;
        
        return $m ;
        
    } // LoadPrivateData
    
    
    /**
     * this function load the list of verification done for member Username
     * @Username the id of the member (can also be the IdMember, it will be converted to a Username)
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    function LoadVerifiers($cid)
    {
	     
        $where_cid = is_numeric($cid) ? 'm2.id='.(int)$cid : 'm2.Username=\''.mysql_real_escape_string($cid).'\'';
        
        $ss="select m1.Username,AddressVerified,NameVerified,verifiedmembers.Comment as Comment,verifiedmembers.Type as VerificationType,cities.Name as CityName,m1.Gender". 
		 	 " from members m1,members m2, verifiedmembers,cities ".
			 " where m1.id=verifiedmembers.IdVerifier and m2.id=verifiedmembers.IdVerified and cities.id=m1.IdCity and (m1.Status='Active' or m1.Status='ChoiceInactive') and ".$where_cid ;
        if (!is_array($rows = $this->bulkLookup($ss))) {
            return array(); // empty array means no verifier
        } else {
            // $rows can be empty or not, we don't care at this point.
            return $rows;
        }
        
    } // LoadVerifiers


    /**
     * this function load the list of verification done for by member Username
     * @Username the id of the member (can also be the IdMember, it will be converted to a Username)
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    function LoadVerified($cid)
    {
	     
        $where_cid = is_numeric($cid) ? 'm2.id='.(int)$cid : 'm2.Username=\''.mysql_real_escape_string($cid).'\'';
        
        $ss="select m1.Username,AddressVerified,NameVerified,verifiedmembers.Comment as Comment,verifiedmembers.Type as VerificationType,cities.Name as CityName,m1.Gender". 
		 	 " from members m1,members m2, verifiedmembers,cities ".
			 " where m1.id=verifiedmembers.IdVerified and m2.id=verifiedmembers.IdVerifier and cities.id=m1.IdCity and (m1.Status='Active' or m1.Status='ChoiceInactive') and ".$where_cid ;
        if (!is_array($rows = $this->bulkLookup($ss))) {
            return array(); // empty array means no verifier
        } else {
            // $rows can be empty or not, we don't care at this point.
            return $rows;
        }
        
    } // LoadVerified


    /**
     * Checks and gets the username if the member can be displayed as
     * a verified (he must have the proper Status) @ci the id of the
     * member (can also be the IdMember, it will be converted to a
     * Username) @ returns the username or an empty string
     **/
    function CheckAndGetUsername($cid) {
        $where_cid = is_numeric($cid) ? 'members.id='.$cid : 'members.Username=\''.mysql_real_escape_string($cid).'\'';
		 if ($m=$this->singleLookup("SELECT Username FROM members WHERE (Status='Active' OR Status='ChoiceInactive') AND ".$where_cid)) {
		 	return($m->Username) ;
		 }
//	 echo "is_numeric($cid)=",is_numeric($cid) ;
		 return(false) ;
		  		 
	 } // end of CheckAndGetUsername

}




?>
