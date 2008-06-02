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
        $sRet = "Normal";
        
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
            // nothing found
        } else if ("Buggy" == $rr->Type) {
            // problem
            throw new PException('Buggy Value in verifiedmembers for IdMember=".$IdMember." !');
        }
        
        
        // if the member is a verifier and has ApprovedVerifier scope, this information will supersed all others
        // comment by lemon-head: Better do this in the controller?		
        if (HasRight("Verifier","ApprovedVerifier")) {
            // TODO: HasRight does only check the currently logged-in user, not the given argument!
            return "ApprovedVerifier" ;  
        }
        return $rr->Type;
    } // end of sVerifierLevel


    /**
     * this function insert a new verified member (or replace the record if one from the same verifier exist for this member) 
     **/
    function AddNewVerified()
    {
    } // AddNewVerified

    /**
     * This function loads the private data of member IdMember
     * @cid can be an id or a username of the member.
     * @given_password is the password sent with the form.  
     * @return a structure with the data, or false if password/Username dont match
     **/
    function LoadPrivateData($cid, $given_password)
    {
        // there is a TODO to avoid thi query if slow to go in logs with a plain text password
        // comment by lemon-head: I think we should encrypt the pw on PHP side, not in SQL.
        // It is said in MySQL documentation
        // that the PASSWORD() function is not recommended to be used by applications.
        
        // accept both 
        $where_cid = is_int($cid) ? 'id='.(int)$cid : 'Username="'.mysql_real_escape_string($cid).'"';
        
        // TODO: What does this "password=PassWord=PASSWORD(...)" mean?  --lemon-head
        if (!$m = $this->singleLookup(
            "
SELECT  *
FROM    members
WHERE   $where_cid
AND     password=PassWord=PASSWORD('".trim($given_password)."')
            "
        )) {
            // user not found! explain something?
            return false;
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
        foreach (array('FirstName', 'SecondName', 'LastName', 'HouseNumber', 'StreetName', 'Zip') as $key) {
            $m->$key = AdminReadCrypted($m->$key);
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
        /*
        $where_cid = is_int($cid) ? 'id='.(int)$cid : 'Username="'.$this->escape($cid).'"';
        
        if (!is_array($rows = $this->bulkLookup(
            "
SELECT  *
FROM    members
WHERE   $where_cid
            "
        ))) {
            // query failed..
            return false;
        } else {
            // $rows can be empty or not, we don't care at this point.
            return $rows;
        }
        */
    } // LoadVerifiers
}




?>
