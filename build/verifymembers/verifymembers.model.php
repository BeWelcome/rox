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
    public function sVerifierLevel($member_id=-1)    {
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
        if (MOD_right::get()->hasRight("Verifier","ApprovedVerifier")) {
            // TODO: HasRight does only check the currently logged-in user, not the given argument!
            $sRet= "VerifiedByApproved" ;  
        }
      	 return $sRet;
    } // end of sVerifierLevel


    /**
     * this function inserts 2 new verified members (or replace the record if one from the same verifier exist for this member)
	  * @post is the post from the previous form
	  * nota a member cannot verified himself 
     **/
    public function AddNewVerified($input) 
    { 	
        $vars_all = $this->prepareVerificationData($input);
        $result = array();
        foreach ($vars_all as $vars) {
            // accept both 
            if ($m = $this->singleLookup("SELECT  id,Username from members where Status='Active' and id=".$vars["IdMemberToVerify"]))  {
    		    $IdVerifiedMember=$m->id ; 
    		} else return(false) ; // return false if verification failed
		 
    		$VerifierLevel=$this->sVerifierLevel($vars['IdVerifier']) ;
		 
    //	    echo "\$post=" ;print_r($post) ;
    		$AddressConfirmed='False';
    		$NameConfirmed='False';
    		if (isset($vars['NameConfirmed']) and $vars['NameConfirmed']='on') {
    		   $NameConfirmed='True';
    		}
    		if (isset($vars['AddressConfirmed']) and $vars['AddressConfirmed']='on') {
    		   $AddressConfirmed='True';
    		}
		
    		// Check if the current member has allready verified this one, if so it will be an update
    		$AllreadyVerified=$this->singleLookup("SELECT  * from verifiedmembers where IdVerifier=".$vars['IdVerifier']." and IdVerified=".$IdVerifiedMember) ;
    		if (isset($AllreadyVerified->id)) { // If the member was already verified : do an update
    			$ss="update verifiedmembers set IdVerifier=".$vars['IdVerifier'].",IdVerified=".$IdVerifiedMember.",AddressVerified='".$AddressConfirmed."',NameVerified='".$NameConfirmed."',Comment='".mysql_real_escape_string(addslashes($vars["comment"]))."',Type='".$VerifierLevel."' where id=".$AllreadyVerified->id ;
            	MOD_log::get()->write("Update Verify members ".$m->Username." previous value comment[".$AllreadyVerified->Comment."] AddressVerified=".$AllreadyVerified->AddressVerified.",NameVerified=".$AllreadyVerified->NameVerified,"VerifyMember") ;
			
    		}
    		else {
    			$ss="insert into verifiedmembers(created,IdVerifier,IdVerified,AddressVerified,NameVerified,Comment,Type) values(now(),".$vars['IdVerifier'].",".$IdVerifiedMember.",'".$AddressConfirmed."','".$NameConfirmed."','".mysql_real_escape_string(addslashes($vars["comment"]))."','".$VerifierLevel."')" ;
            	MOD_log::get()->write("Has verify member ".$m->Username,"VerifyMember") ;
    		}
      		$s = $this->dao->query($ss);

       	    if (!$s) {
                throw new PException('Failed to verify member '.$m->Username);
       	    }
       	    $result[] = $s;
        }
        if (!$result[0] || !$result[1]) return false;
		else return(true);
    } // AddNewVerified

    /**
     * This function loads the private data of member IdMember
     * @cid can be an id or a username of the member.
     * @given_password is the password sent with the form.  
     * @return a structure with the data, or false if password/Username dont match
     **/
    public function LoadPrivateData($cid, $given_password)   {
        // comment by lemon-head: I think we should encrypt the pw on PHP side, not in SQL.
        // It is said in MySQL documentation
        // that the PASSWORD() function is not recommended to be used by applications.
        // - correct, but as long as we're stuck with the mysql password function (and for now
        //   we are, then we either have to replicate the password function [it doesn't exist in php]
        //   or rely on mysql. Sucks to be us.
                
        if (!$m = $this->checkPassword($cid,$given_password)) {
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
    IdMember = {$m->id}            AND
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
            $m->$key = MOD_crypt::AdminReadCrypted($m->$key);
        }
        foreach (array('HouseNumber', 'StreetName', 'Zip') as $key) {
            $m->$key = MOD_crypt::AdminReadCrypted($rAddresse->$key);
        }
        
        $m->CityName = $rAddresse->CityName ;
        
        return $m ;
        
    } // LoadPrivateData
    
    
    /**
     * this function load the list of verification done for member Username
     * @Username the id of the member (can also be the IdMember, it will be converted to a Username)
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    public function LoadVerifiers($cid)
    {
	     
        $where_cid = is_numeric($cid) ? 'm2.id='.(int)$cid : 'm2.Username=\''.mysql_real_escape_string($cid).'\'';        
        $ss="select m1.Username,AddressVerified,NameVerified,verifiedmembers.Comment as Comment,verifiedmembers.Type as VerificationType,cities.Name as CityName,m1.Gender,verifiedmembers.created as VerificationDate". 
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
     * this function load the list of the approved verifiers
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    public function LoadApprovedVerifiers() 
    {
        $ss="select m1.*,cities.Name as CityName,countries.Name as CountryName". 
		 	 " from members m1,cities,rightsvolunteers,rights,countries ".
			 " where m1.id=rightsvolunteers.IdMember and cities.IdCountry=countries.id and rights.id=rightsvolunteers.IdRight and rights.Name='Verifier' and rightsvolunteers.Level>0 and cities.id=m1.IdCity and (m1.Status='Active') order by CityName" ;
      	$qry = $this->dao->query($ss);
      	if (!$qry) {
            throw new PException('verifymembers::LoadApprovedVerifiers Could not retrieve the verifiers list!');
      	}

		$tt=array() ;

		// for all the records
      	while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
			$rComment=$this->singleLookup("select count(*) as cnt from comments where IdToMember=".$rr->id) ;
		
			$rr->MemberSince=strftime('%d/%m/%Y',strtotime($rr->created)) ;
			// Load Age
			$rr->age = fage($rr->BirthDate, $rr->HideBirthDate);
			$rr->NbComments=$rComment->cnt ;

			// Load full name
			$rr->FullName = fFullName($rr);
			array_push( $tt,$rr) ;
		}

        
		return($tt) ;
    } // LoadApprovedVerifiers
		
    /**
     * this function load the list of verification done for by member Username
     * @Username the id of the member (can also be the IdMember, it will be converted to a Username)
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    public function LoadVerified($cid)
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
    protected function CheckAndGetUsername($cid) {
        $where_cid = is_numeric($cid) ? 'members.id='.$cid : 'members.Username=\''.mysql_real_escape_string($cid).'\'';
		 if ($m=$this->singleLookup("SELECT Username FROM members WHERE (Status='Active' OR Status='ChoiceInactive') AND ".$where_cid)) {
		 	return($m->Username) ;
		 }
//	 echo "is_numeric($cid)=",is_numeric($cid) ;
		 return(false) ;
		  		 
	 } // end of CheckAndGetUsername
	 
         /**
      * Check if the entered password is correct
      * @vars an array of values from the form
      * @return an (empty) array of errors
      **/
     public function checkPasswordsOfMembers($vars)   {
         // jeanYves : there is a TODO to avoid this query if slow to go in logs with a plain text password
         // comment by lemon-head: I think we should encrypt the pw on PHP side, not in SQL.
         // It is said in MySQL documentation
         // that the PASSWORD() function is not recommended to be used by applications.

         $errors = array();
         if (!isset($vars['cid1']) || !$vars['cid1'])
             $errors[] .=  'cid1_notset';
         if (!isset($vars['cid2']) || !$vars['cid2'])
             $errors[] .=  'cid2_notset';
         if (!isset($vars['password1']) || !$vars['password1'])
             $errors[] .=  'password1_notset';
         if (!isset($vars['password2']) || !$vars['password2'])
             $errors[] .=  'password2_notset';
         if (!empty($errors)) return $errors;
         
         $m1 = $this->checkPassword($vars['cid1'],$vars['password1']);
         $m2 = $this->checkPassword($vars['cid2'],$vars['password2']);
         if (empty($m1)) $errors[] .=  'password1_wrong';
         if (empty($m2)) $errors[] .=  'password2_wrong';
         return $errors;
     }
     
     /**
      * Check if the entered password is correct
      * @vars an array of values from the form
      * @return an (empty) array of errors
      **/
     protected function checkPassword($cid,$given_password)   {

          // accept both 
          $where_cid = is_numeric($cid) ? 'id='.(int)$cid : 'Username="'.mysql_real_escape_string($cid).'"';

  		 $ss="
  SELECT  *
  FROM    members
  WHERE   $where_cid
  AND     PassWord=PASSWORD('{$this->dao->escape($given_password)}')" ;
          if (!$m = $this->singleLookup($ss)) {
              // user not found! explain something?
              return array(); // Returns empty array if no value found
  		 } else return $m;
     }
     
         /**
      * Check if all values are correct
      * @vars an array of values from the form
      * @return an (empty) array of errors
      **/
     public function checkVerificationForm($vars)   {
         $errors = array();
         if ((!isset($vars['NameConfirmed1']) || !$vars['NameConfirmed1']) && (!isset($vars['AddressConfirmed1']) || !$vars['AddressConfirmed1']) && (!isset($vars['NameConfirmed2']) || !$vars['NameConfirmed2']) && (!isset($vars['AddressConfirmed2']) || !$vars['AddressConfirmed2']))
         {
             $errors[] .=  'nofieldset';
         }
         return $errors;
     }
     
         /**
      * Check if all values are correct
      * @vars an array of values from the form
      * @return an (empty) array of errors
      **/
     public function prepareVerificationData($vars)
     {
         $n = array();
         if (isset($vars['NameConfirmed1'])) $n[1]['NameConfirmed'] = $vars['NameConfirmed1'];
         if (isset($vars['AddressConfirmed1'])) $n[1]['AddressConfirmed'] = $vars['AddressConfirmed1'];

         $n[1]['comment'] = (isset($vars['comment1'])) ? $vars['comment1'] : '';
         $n[1]['IdVerifier'] = $vars['idmember1'];
         $n[1]['IdMemberToVerify'] = $vars['idmember2'];

         if (isset($vars['NameConfirmed2'])) $n[2]['NameConfirmed'] = $vars['NameConfirmed2'];
         if (isset($vars['AddressConfirmed2'])) $n[2]['AddressConfirmed'] = $vars['AddressConfirmed2'];
         
         $n[2]['comment'] = (isset($vars['comment2'])) ? $vars['comment2'] : '';
         $n[2]['IdVerifier'] = $vars['idmember2'];
         $n[2]['IdMemberToVerify'] = $vars['idmember1'];

        return $n;
     }

}

?>
