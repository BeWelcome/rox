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
        $sRet= "Normal" ;
        if ($member_id > 0) {
            // everything is cool
        } else if ($this->_session->has( "IdMember" )) {
            // use the member who is currently logged in.
            $member_id=$_SESSION["IdMember"] ;
        } else {
            // can't help it, so we return false.
            return false;
        }

        $rr = $this->SingleLookup(
            "
SELECT  max(Type) AS Type
FROM    verifiedmembers
WHERE   IdVerified = $member_id
            "
                    );
        if ($rr) {
        	if ("Buggy" == $rr->Type) {
            // problem
            	throw new PException('Buggy Value in verifiedmembers for IdMember=".$IdMember." !');
        	}
         	else
         	{
            	if (!empty($rr->Type)) {
            		$sRet = $rr->Type;
            	}
         	}
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

    //      echo "\$post=" ;print_r($post) ;
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

        $data = new stdClass();

        if (!$m = $this->checkPassword($cid,$given_password)) {
            // user not found! explain something?
            return array(); // Returns empty array if no value found
        }
        // Password has been verified, load the encrypted data
        foreach (array('FirstName', 'SecondName', 'LastName') as $key) {
            $data->$key = MOD_crypt::AdminReadCrypted($m->$key);
        }
        foreach (array('HouseNumber', 'StreetName', 'Zip') as $key) {
            if(!isset($m->address)) {
                $housenumber = $m->get_housenumber();
            }
            $data->$key = MOD_crypt::AdminReadCrypted($m->address->$key);
        }

        $data->CityName = $m->get_city();

        return $data;

    } // LoadPrivateData


    /**
     * Get verifications a member has received.
     *
     * @param int|string $cid User ID or username of member
     * @return array List of verifications
     *
     * TODO: Restrict method usage to either user ID *or* username, not both
     */
    public function LoadVerifiers($cid) {
        if (is_numeric($cid)) {
            $userId = intval($cid);
            $query = "
                SELECT
                    m1.Username,
                    AddressVerified,
                    NameVerified,
                    verifiedmembers.Comment AS Comment,
                    verifiedmembers.Type AS VerificationType,
                    geonames_cache.name AS CityName,
                    m1.Gender,
                    verifiedmembers.created AS VerificationDate
                FROM
                    members m1,
                    members m2,
                    verifiedmembers,
                    geonames_cache
                WHERE
                    m1.id = verifiedmembers.IdVerifier
                    AND
                    m2.id = verifiedmembers.IdVerified
                    AND
                    geonames_cache.geonameId = m1.IdCity
                    AND
                    (
                        m1.Status IN ('Active', 'OutOfRemind')
                    )
                    AND
                    m2.id = $userId
                ";
        } else {
            $username = mysql_real_escape_string($cid);

            // This is almost the same query as for user ID, but to improve
            // code readability it's repeated here
            $query = "
                SELECT
                    m1.Username,
                    AddressVerified,
                    NameVerified,
                    verifiedmembers.Comment AS Comment,
                    verifiedmembers.Type AS VerificationType,
                    geonames_cache.name AS CityName,
                    m1.Gender,
                    verifiedmembers.created AS VerificationDate
                FROM
                    members m1,
                    members m2,
                    verifiedmembers,
                    geonames_cache
                WHERE
                    m1.id = verifiedmembers.IdVerifier
                    AND
                    m2.id = verifiedmembers.IdVerified
                    AND
                    geonames_cache.geonameId = m1.IdCity
                    AND
                    (
                        m1.Status IN ('Active', 'OutOfRemind')
                    )
                    AND
                    m2.Username = '$username'
                ";
        }

        if (!is_array($rows = $this->bulkLookup($query))) {
             // empty array means no verifier
            return array();
        } else {
            // $rows can be empty or not, we don't care at this point.
            return $rows;
        }

    }

    /**
     * this function load the list of the approved verifiers
     * @ returns a structure with the data with the list of verifications or and empty structure if password/Username dont match
     **/
    public function LoadApprovedVerifiers()
    {
        $layoutbits = new MOD_layoutbits();
        $query = "
            SELECT
                m1.*,
                geonames_cache.name AS CityName,
                geonames_countries.name AS CountryName
            FROM
                members m1,
                geonames_cache,
                rightsvolunteers,
                rights,
                geonames_countries
            WHERE
                m1.id = rightsvolunteers.IdMember
                AND
                geonames_cache.geonameId = m1.IdCity
                AND
                geonames_cache.fk_countrycode = geonames_countries.iso_alpha2
                AND
                rights.id = rightsvolunteers.IdRight
                AND
                rights.Name = 'Verifier'
                AND
                rightsvolunteers.Level > 0
                AND
                m1.Status = 'Active'
            ORDER BY
                CityName
            ";

        $mm = $this->createEntity('Member')->findBySQLMany($query);
        if (!$mm) {
            throw new PException('verifymembers::LoadApprovedVerifiers Could not retrieve the verifiers list!');
        }

        $tt=array();

        // for all the records
        foreach ($mm as $m) {
            $rComment=$this->singleLookup("select count(*) as cnt from comments where IdToMember=".$m->id) ;

            $m->MemberSince=strftime('%d/%m/%Y',strtotime($m->created)) ;
            // Load Age
            $m->age = $layoutbits->fage_value($m->BirthDate, $m->HideBirthDate);
            $m->NbComments=$rComment->cnt ;

            // Load full name
            $m->FullName = ($m->name);
            array_push( $tt,$m) ;
        }


        return($tt) ;
    } // LoadApprovedVerifiers

    /**
     * Get verifications a member has given.
     *
     * @param string $username Username of member
     * @return array List of verifications, empty if none
     */
    public function LoadVerified($username) {
        $usernameEscaped = mysql_real_escape_string($username);
        $query = "
            SELECT
                m1.Username,
                AddressVerified,
                NameVerified,
                verifiedmembers.Comment AS Comment,
                verifiedmembers.Type AS VerificationType,
                geonames_cache.name AS CityName,
                m1.Gender
            FROM
                members m1,
                members m2,
                verifiedmembers,
                geonames_cache
            WHERE
                m1.id = verifiedmembers.IdVerified
                AND
                m2.id = verifiedmembers.IdVerifier
                AND
                geonames_cache.geonameId = m1.IdCity
                AND
                (
                    m1.Status IN ('Active', 'OutOfRemind')
                )
                AND
                    m2.username = '$usernameEscaped'
            ";

        if (!is_array($rows = $this->bulkLookup($query))) {
            return array();
        } else {
            return $rows;
        }
    }


    /**
     * Checks and gets the username if the member can be displayed as
     * a verified (he must have the proper Status) @ci the id of the
     * member (can also be the IdMember, it will be converted to a
     * Username) @ returns the username or an empty string
     **/
    protected function CheckAndGetUsername($cid) {
        $where_cid = is_numeric($cid) ? 'members.id='.$cid : 'members.Username=\''.mysql_real_escape_string($cid).'\'';
         if ($m=$this->singleLookup("SELECT Username FROM members WHERE (Status='Active' OR Status='OutOfRemind') AND ".$where_cid)) {
            return($m->Username) ;
         }
//   echo "is_numeric($cid)=",is_numeric($cid) ;
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
         if ($m1 && $m1->Status != 'Active') $errors[] .=  'member1_notactive';
         if ($m2 && $m2->Status != 'Active') $errors[] .=  'member2_notactive';
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
          $where_cid = is_numeric($cid) ? 'id='.(int)$cid : 'Username="'.$this->dao->escape($cid).'"';
          $password = $this->dao->escape($given_password);

          $where = $where_cid. " AND PassWord=PASSWORD('{$password}')" ;
          if (!$m = $this->createEntity('Member')->findByWhere($where)) {
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
