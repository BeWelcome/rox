<?php
//------------------------------------------------------------------------------
// InsertInCrypted allow to insert a string in Crypted table
// It returns the ID of the created record 
function InsertInCrypted($ss, $_IdMember = "", $IsCrypted = "crypted") {
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
	$str = "insert into ".$_SYSHCVOL['Crypted']."cryptedfields(AdminCryptedValue,MemberCryptedValue,IdMember,IsCrypted) values(\"" . $ssA . "\",\"" . $ssM . "\"," . $IdMember . ",\"" . $IsCrypted . "\")";
	sql_query($str);
	return (mysql_insert_id());
} // end of InsertInCrypted

//------------------------------------------------------------------------------
// MemberCrypt allow a member to Crypt his crypted data
function MemberCrypt($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	$IdMember = $_SESSION['IdMember'];
	$rr=LoadRow("select MemberCryptedValue from ".$_SYSHCVOL['Crypted']."cryptedfields where IdMember=" . $IdMember . " and id=" . $IdCrypt);
   $ssM=GetCryptM($rr->MemberCryptedValue);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set IsCrypted='crypted',MemberCryptedValue='".$ssM."' where IsCrypted='not crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	sql_query($str);
} // end of MemberCrypt

//------------------------------------------------------------------------------
// MemberDecrypt allow a member to Crypt his crypted data
function MemberDecrypt($IdCrypt = 0) {
	global $_SYSHCVOL; // use global vars
	if (($IdCrypt == 0) or ($IdCrypt == ""))
		return (""); // return blank string if no entry
	$IdMember = $_SESSION['IdMember'];
	$rr=LoadRow("select MemberCryptedValue from ".$_SYSHCVOL['Crypted']."cryptedfields where IdMember=" . $IdMember . " and id=" . $IdCrypt);
   $ssM=GetDeCryptM($rr->MemberCryptedValue);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set IsCrypted='not crypted',MemberCryptedValue='".$ssM."' where IsCrypted='crypted' and IdMember=" . $IdMember . " and id=" . $IdCrypt;
	sql_query($str);
} // end of MemberDecrypt

//------------------------------------------------------------------------------
// IsCrypted return true if data is crypted
function IsCrypted($IdCrypt) {
	global $_SYSHCVOL; // use global vars
	if ($IdCrypt == 0)
		return (false); // if no value, it is not crypted
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
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
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
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
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);

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
	$rr = LoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
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
function ReplaceInCrypted($ss, $IdCrypt, $_IdMember = 0, $IsCrypted = "crypted") {
	global $_SYSHCVOL; // use global vars
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}
	if ($IdCrypt == 0) {
		return (InsertInCrypted($ss, $IdMember, $IsCrypted)); // Create a full new crypt record
	} else {
		$rr = LoadRow("select * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
		if (!isset ($rr->id)) { // if no record exist
			return (InsertInCrypted($ss, $IdMember, $IsCrypted)); // Create a full new crypt record
		}
	}

	// todo : manage cryptation, manage IdMember when it is not the owner of the record (in this case he must have the proper right)

   $ssA=GetCryptA($ss);
   $ssM=GetCryptM($ss,$IsCrypted);
	$str = "update ".$_SYSHCVOL['Crypted']."cryptedfields set IsCrypted=\"" . $IsCrypted . "\",AdminCryptedValue=\"" . $ssA . "\",MemberCryptedValue=\"" . $ssM . "\" where id=" . $rr->id . " and IdMember=" . $rr->IdMember;
	sql_query($str);
	return ($IdCrypt);
} // end of ReplaceInCrypted



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
		return (false); // if no value, it is not crypted
	$IdMember = $_SESSION['IdMember'];
	$rr = LoadRow("select SQL_CACHE * from ".$_SYSHCVOL['Crypted']."cryptedfields where id=" . $IdCrypt);
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
				 			$strlog="function GetCryptM() Problem to crypt ".$ss." IsCrypted=[".$IsCrypted."]" ;
				 			LogStr($strlog,"Bug") ;
							bw_error($strlog) ;
							die ("Major problem with crypting issue") ;
				
			}
} // end of GetCryptM


// -----------------------------------------------------------------------------
// Return the decrypted value of $ss according to member cryptation algorithm
function GetDeCryptM($ss) { 
		  if (strstr($ss,"<membercrypted>")===false) return($ss); 
		  $res=strip_tags($ss);
		  // todo add right test
		  return(DecryptM($res));
} // end of GetDeCryptM

// -----------------------------------------------------------------------------
// Return the secret key for cryping
function GetCryptingKey() { 
		  // todo secure this key
		  return("YEU76EY6");
} // end of GetCryptingKey

function CryptA($ss) {
		  $res=$ss;
		  $key=GetCryptingKey();
		  $lenkey=strlen($key);
		  $len=strlen($res);
		  for ($ii=0;$ii<$len;$ii++) {
		  	  $res{$ii}=chr( (ord($key{($ii%$lenkey)}) + ord($res{$ii}) )%256 );
		  }
		  
		  return($res);
} 
function CryptM($ss) {
		  return(CryptA($ss)); // CryptM is buggy todo fix it
		  $res=$ss;
		  $key=$_SESSION['MemberCryptKey'].GetCryptingKey();
		  $lenkey=strlen($key);
		  $len=strlen($res);
		  for ($ii=0;$ii<$len;$ii++) {
		  	  $res{$ii}=chr( ord($res{$ii}) + (ord($key{($ii%$lenkey)}) )%256 );
		  }
		  
		  return($res);
} 
function DeCryptA($ss) {
		  $res=$ss;
		  $key=GetCryptingKey();
		  $lenkey=strlen($key);
		  $len=strlen($res);
		  for ($ii=0;$ii<$len;$ii++) {
		  	  $res{$ii}=chr( ord($res{$ii}) - (ord($key{($ii%$lenkey)}) )%256 );
		  }
		  
		  return($res);
} 
function DeCryptM($ss) {
		  return(DeCryptA($ss)); // DeCryptM is buggy todo fix it
		  $res=$ss;
		  $key=$_SESSION['MemberCryptKey'].GetCryptingKey();
		  $lenkey=strlen($key);
		  $len=strlen($res);
		  for ($ii=0;$ii<$len;$ii++) {
		  	  $res{$ii}=chr( ord($res{$ii}) - (ord($key{($ii%$lenkey)}) )%256 );
		  }
		  
		  return($res);
} 

?>