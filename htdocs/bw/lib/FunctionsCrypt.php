<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

require_once SCRIPT_BASE.'inc/enc.inc.php';

//------------------------------------------------------------------------------
// InsertInCrypted allow to insert a string in Crypted table
// It returns the ID of the created record 
// This is deprecated use NewInsertInCrypted instead
function InsertInCrypted($ss, $_IdMember = "", $IsCrypted = "crypted") {
	return (NewInsertInCrypted($ss,"NotSet",0,$_IdMember,$IsCrypted));
} // end of InsertInCrypted

//------------------------------------------------------------------------------
// NewInsertInCrypted allow to insert a string in Crypted table
// It returns the ID of the created record 
// @$TableComumn must be in the form "members.ProfileSummary"
// @$Idrecord is to be the id of the record in the corresponding $TableColumn, 
// This is not normalized but needed for mainteance
function NewInsertInCrypted($ss,$TableColumn,$IdRecord, $_IdMember = "", $IsCrypted = "crypted") {
	global $_SYSHCVOL; // use global vars
	if ($ss == "")
		return (0); // Dont create a crypted data for a void value
	if ($_IdMember == "") { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}
   $ssA=GetCryptA($ss);
   $ssM=GetCryptM($ss,$IsCrypted);
	$str = "insert into ".$_SYSHCVOL['Crypted']."cryptedfields(AdminCryptedValue,MemberCryptedValue,IdMember,IsCrypted,TableColumn,IdRecord) values(\"" . $ssA . "\",\"" . $ssM . "\"," . $IdMember . ",\"" . $IsCrypted . "\",\"" . $TableColumn . "\",".$IdRecord.")";
	// echo $str,"<br>" ;
	mysql_query($str);
	return (mysql_insert_id());
} // end of NewInsertInCrypted

//------------------------------------------------------------------------------
// MemberCrypt allow a member to Crypt his crypted data
function MemberCrypt($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	$IdMember = $_SESSION['IdMember'];
	$rr=MyLoadRow("select MemberCryptedValue from ".$_SYSHCVOL['Crypted']."cryptedfields where IdMember=" . $IdMember . " and id=" . $IdCrypt);
   $ssM=GetCryptM($rr->MemberCryptedValue);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set IsCrypted='crypted',MemberCryptedValue='".$ssM."' where IsCrypted='not crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	mysql_query($str);
} // end of MemberCrypt

//------------------------------------------------------------------------------
// MemberDecrypt allow a member to Crypt his crypted data
function MemberDecrypt($IdCrypt = 0) {
	global $_SYSHCVOL; // use global vars
	if (($IdCrypt == 0) or ($IdCrypt == ""))
		return (""); // return blank string if no entry
	$IdMember = $_SESSION['IdMember'];
	$rr=MyLoadRow("select MemberCryptedValue from ".$_SYSHCVOL['Crypted']."cryptedfields where IdMember=" . $IdMember . " and id=" . $IdCrypt);
   $ssM=GetDeCryptM($rr->MemberCryptedValue);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set IsCrypted='not crypted',MemberCryptedValue='".$ssM."' where IsCrypted='crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	mysql_query($str);
} // end of MemberDecrypt

//------------------------------------------------------------------------------
// IsCrypted return true if data is crypted
function IsCrypted($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	if ($IdCrypt == 0)
		return (false); // if no value, it is not crypted
	$IdMember = $_SESSION['IdMember'];
	$rr = MyLoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
	switch ($rr->IsCrypted) {
		case "not crypted" :
			return (false);
		case "crypted" :
			return (true);
		case "always" :
			return (true);
		default :
			return (true);

	}
} // end of IsCrypted

//------------------------------------------------------------------------------
// AdminReadCrypted read the crypt field
// todo : complete this function
function AdminReadCrypted($IdCrypt = 0) {
	global $_SYSHCVOL; // use global vars
	if (($IdCrypt == 0) or ($IdCrypt == ""))
		return (""); // return blank string if no entry
	// todo limit to right decrypt or similar
	$rr = MyLoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
	return (GetDeCryptA($rr->AdminCryptedValue));
} // end of AdminReadCrypted

//------------------------------------------------------------------------------
// PublicReadCrypted read the crypt field
// return the plain text if contend is not crypted
// If not return standard "is crypted text"
// todo : complete this function
// if memberdata is crypted, return standard word cryptedhidden or content of optional parameter $returnval 
function PublicReadCrypted($IdCrypt, $returnval = "") {
	global $_SYSHCVOL; // use global vars
	
	$rr = MyLoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);

	if ($rr != NULL)
	{
		if ($rr->IsCrypted == "not crypted") {
			return (GetDeCryptM($rr->MemberCryptedValue));
		}
		if ($rr->MemberCryptedValue == "")
			return (""); // if empty no need to send crypted
	}	
	if ($returnval == "")
		return (ww("cryptedhidden"));
	else
		return ($returnval);
} // end of PublicReadCrypted

//------------------------------------------------------------------------------
// MemberReadCrypted read the crypt field
// return the plain text if the current member is the owner of the crypted object
// If not return standard "is crypted text"
// todo : complete this function
function MemberReadCrypted($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	if ($IdCrypt == 0)
		return (""); // if 0 it mean that the field is empty 
	$rr = MyLoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
	if ($_SESSION["IdMember"] == $rr->IdMember) {
		//	  echo $rr->MemberCryptedValue,"<br>";
		return (GetDeCryptM($rr->MemberCryptedValue));
	} else {
		if ($rr->MemberCryptedValue == "")
			return (""); // if empty no need to send crypted	
		return (ww("cryptedhidden"));
	}
} // end of MemberReadCrypted

//------------------------------------------------------------------------------
// ReverseCrypt  return "decrypt" if $IdCrypt correspond to a crypt field
//               return "crypt" if $IdCrypt correspond to a not crypted field
//               this is used to propose the proper option on layout, no action on DB required here
function ReverseCrypt($IdCrypt) {
	if (IsCrypted($IdCrypt))
		return "decrypt";
	else
		return "crypt";
}

//------------------------------------------------------------------------------
// ReplaceInCrypted allow to replace a string in Crypted table
// It returns the ID of the replaced record 
// This is deprecated use NewReplaceInCrypted instead
function ReplaceInCrypted($ss, $IdCrypt, $_IdMember = 0, $IsCrypted = "crypted") {
	return(NewReplaceInCrypted($ss,"NotSet",0, $IdCrypt, $_IdMember, $IsCrypted)) ;
} // end of ReplaceInCrypted

//------------------------------------------------------------------------------
// NewReplaceInCrypted allow to replace a string in Crypted table
// It returns the ID of the replaced record 
// @$TableComumn must be in the form "members.ProfileSummary"
// @$Idrecord is to be the id of the record in the corresponding $TableColumn, 
// This is not normalized but needed for mainteance
function NewReplaceInCrypted($ss,$TableColumn,$IdRecord, $IdCrypt, $_IdMember = 0, $IsCrypted = "crypted") {
	global $_SYSHCVOL; // use global vars
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}
	if ($IdCrypt == 0) {
		return (NewInsertInCrypted($ss,$TableColumn,$IdRecord, $IdMember, $IsCrypted)); // Create a full new crypt record
	} else {
		$rr = MyLoadRow("select * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
		if (!isset ($rr->id)) { // if no record exist
			return (InsertInCrypted($ss,$TableColumn,$IdRecord, $IdMember, $IsCrypted)); // Create a full new crypt record
		}
		else {
			if (empty($IsCrypted)) {
				$IsCrypted=$rr->IsCrypted ;
			}
		}
	}

	// todo : manage cryptation, manage IdMember when it is not the owner of the record (in this case he must have the proper right)

	$ssA=GetCryptA($ss);
//	LogStr(" Before calling  GetCryptM(\"".addslashes($ss)."\",\"".$IsCrypted."\")","JYH") ;
	$ssM=GetCryptM($ss,$IsCrypted);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IsCrypted='" . $IsCrypted . "',AdminCryptedValue='" . $ssA . "',MemberCryptedValue='" . $ssM . "' where id=" . $rr->id . " and IdMember=" . $rr->IdMember;
	mysql_query($str);
	return ($IdCrypt);
} // end of NewReplaceInCrypted



// -----------------------------------------------------------------------------
// Return the crypted value of $ss according to admin cryptation algorithm
function GetCryptA($ss) {
		  if (strstr($ss,"<admincrypted>")!==false) return($ss); 
		  // todo add right test
		  return ("<admincrypted>".CryptA($ss)."</admincrypted>");
} // end of GetCryptA

// -----------------------------------------------------------------------------
// Return the decrypted value of $ss according to admin cryptation algorithm
function GetDeCryptA($ss) { 
		  if (strstr($ss,"<admincrypted>")===false) return($ss); 
		  $res=strip_tags($ss);
		  // todo add right test
		  return(DecryptA($res));
} // end of GetDeCryptA

//------------------------------------------------------------------------------
// IsCryptedValue return the content of the IsCrypted field if data is crypted
function IsCryptedValue($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	if ($IdCrypt == 0)
		return ("not crypted"); // if no value, it is not crypted
	$IdMember = $_SESSION['IdMember'];
	$rr = MyLoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
	return($rr->IsCrypted) ;
} // end of IsCryptedValue


/*
// -----------------------------------------------------------------------------
// Return the crypted value of $ss according to member cryptation algorithm
function GetCryptM($ss,$IsCrypted="crypted") {
		  if ($IsCrypted!="crypted") return(strip_tags($ss)); 
		  if (strstr($ss,"<membercrypted>")!==false) return($ss); 
		  // todo add right test
		  return ("<membercrypted>".CryptM($ss)."</membercrypted>");
} // end of GetCryptM
*/

// -----------------------------------------------------------------------------
// Return the crypted value of $ss according to member cryptation algorithm
function GetCryptM($ss,$IsCrypted="crypted") {
//	LogStr(" entering GetCryptM(\"".addslashes($ss)."\",\"".$IsCrypted."\")","JYH") ;
	switch ($IsCrypted) {
		 case "crypted" :
		 case "always" :
 			if (strstr($ss,"<membercrypted>")!==false) return($ss); 
 			// todo add right test
 			return ("<membercrypted>".CryptM($ss)."</membercrypted>");
 			break ;
		case "not crypted" :
			return(strip_tags($ss));
			break ;
		default : // we should never come here
			$strlog="FunctionsCrypt.php:: function GetCryptM() Problem to encrypt ".$ss." IsCrypted=[".$IsCrypted."]" ;
			if (function_exists(LogStr)) {
				LogStr($strlog,"Bug") ;
			}
			if (function_exists(bw_error)) {
				bw_error($strlog) ;
			}
			else {
				error_log($strlog) ;
			}
			die ("Major problem with crypting issue") ;
			
	} // end of switch
} // end of GetCryptM


// -----------------------------------------------------------------------------
// Return the decrypted value of $ss according to member cryptation algorithm
function GetDeCryptM($ss) { 
		  if (strstr($ss,"<membercrypted>")===false) return($ss); 
		  $res=strip_tags($ss);
		  // todo add right test
		  return(DecryptM($res));
} // end of GetDeCryptM

// Here the old BW function are made compatible with Rox
function MyLoadRow($ss) {
		  if (function_exists("LoadRow")) {
		  	 return(LoadRow($ss)) ;
		  }
		  else {
		  	   $qq=mysql_query($ss) ;
			   if (!$qq) {
			   	  error_log ("failed in MyLoadRow(".$ss.")") ;
				  die("failure in MyLoadRow") ;
			   }
			   return(mysql_fetch_object($qq)) ;
		  }
} // end of MyLoadRow

?>