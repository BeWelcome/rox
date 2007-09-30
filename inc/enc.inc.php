<?php
/**
 * 
 * This file is TOP SECRET and not part of the GPL distribution.
 * 
 * @copyright Copyright (c) 2007 BeVolunteer
 * @author Jean-Yves
 * 
 */

function GetCryptingKey() { 
	require SCRIPT_BASE.'inc/key.inc.php';
	return $key;
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