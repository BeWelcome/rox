<?php
//------------------------------------------------------------------------------
// InsertInCrypted allow to insert a string in Crypted table
// It returns the ID of the created record 
function InsertInCrypted($ss, $_IdMember = "", $IsCrypted = "crypted") {
	if ($ss == "")
		return (0); // Dont create a crypted data for a void value
	if ($_IdMember == "") { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	$str = "insert into cryptedfields(AdminCryptedValue,MemberCryptedValue,IdMember,IsCrypted) values(\"" . $ss . "\",\"" . $ss . "\"," . $IdMember . ",\"" . $IsCrypted . "\")";
	sql_query($str);
	return (mysql_insert_id());
} // end of InsertInCrypted

//------------------------------------------------------------------------------
// MemberCrypt allow a member to Crypt his crypted data
function MemberCrypt($IdCrypt) {
	$IdMember = $_SESSION['IdMember'];
	$str = "update  cryptedfields set IsCrypted='crypted' where IsCrypted='not crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	sql_query($str);
} // end of MemberCrypt

//------------------------------------------------------------------------------
// MemberDecrypt allow a member to Crypt his crypted data
function MemberDecrypt($IdCrypt = 0) {
	if (($IdCrypt == 0) or ($IdCrypt == ""))
		return (""); // return blank string if no entry
	$IdMember = $_SESSION['IdMember'];
	$str = "update  cryptedfields set IsCrypted='not crypted' where IsCrypted='crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	sql_query($str);
} // end of MemberDecrypt

//------------------------------------------------------------------------------
// IsCrypted return true if data is crypted
function IsCrypted($IdCrypt) {
	if ($IdCrypt == 0)
		return (false); // if no value, it is not crypted
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from cryptedfields where id=" . $IdCrypt);
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
	if (($IdCrypt == 0) or ($IdCrypt == ""))
		return (""); // return blank string if no entry
	// todo limit to right decrypt or similar
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from cryptedfields where id=" . $IdCrypt);
	return ($rr->AdminCryptedValue);
} // end of AdminReadCrypted

//------------------------------------------------------------------------------
// PublicReadCrypted read the crypt field
// return the plain text if contend is not crypted
// If not return standard "is crypted text"
// todo : complete this function
// if memberdata is crypted, return standard word cryptedhidden or content of optional parameter $returnval 
function PublicReadCrypted($IdCrypt, $returnval = "") {
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from cryptedfields where id=" . $IdCrypt);
	if ($rr->IsCrypted == "not crypted") {
		return ($rr->MemberCryptedValue);
	}
	if ($rr->MemberCryptedValue == "")
		return (""); // if empty no need to send crypted	
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
	if ($IdCrypt == 0)
		return (""); // if 0 it mean that the field is empty 
	$rr = LoadRow("select SQL_CACHE * from cryptedfields where id=" . $IdCrypt);
	if ($_SESSION["IdMember"] == $rr->IdMember) {
		//	  echo $rr->MemberCryptedValue,"<br>" ;
		return ($rr->MemberCryptedValue);
	} else {
		if ($rr->MemberCryptedValue == "")
			return (""); // if empty no need to send crypted	
		return (ww("cryptedhidden"));
	}
} // end of MemberReadCrypted

//------------------------------------------------------------------------------
// ReverseCrypt  return "decrypt" if $IdCrypt correspond to a crypt field
//               return "crypt" if $IdCrypt correspond to a not crypted field
function ReverseCrypt($IdCrypt) {
	if (IsCrypted($IdCrypt))
		return "decrypt";
	else
		return "crypt";
}

?>