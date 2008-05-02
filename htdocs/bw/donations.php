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

require_once "lib/init.php";
require_once "layout/menus.php";
require_once "layout/donations.php";

$TDonations=array() ;


// fDonation returns an array with the mist of X last donations (all donation in case the current usezr has Treasurer rights) 
function fDonation() {
		$TDonations=array() ;
	  	if (HasRight("Treasurer")) {
		   $str = "select * from donations order by created desc" ;
		}
		else {
		   $str = "select * from donations order by created desc limit 10" ;
		}

		$qry = mysql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			  array_push($TDonations, $rWhile);
		}
		return($TDonations) ;
} // end of fDonation
		

switch (GetParam("action")) {
	case "done" :

		 	 // save the first immediate return values 		
			 $tx=$tx_token = $_GET['tx'];
			 $payment_amount=$_GET['amt'] ;
			 $payment_currency=$_GET['cc'] ;

			 // read the post from PayPal system and add 'cmd'
			 $req = 'cmd=_notify-synch';

			 $auth_token ="token is not set" ;
			 if (isset($_SYSHCVOL['paypal_authtoken'])) {
			 	$auth_token =$_SYSHCVOL['paypal_authtoken'] ;
			 }
			 $req .= "&tx=$tx_token&at=$auth_token";

/*			 
			 foreach ($_POST as $key => $value) {
			 		 $value = trim(urlencode(stripslashes($value)));
					 echo "_POST[", $key,"]=",$value,"<br />";
			}

			 foreach ($_GET as $key => $value) {
			 		 $value = trim(urlencode(stripslashes($value)));
					 echo "_GET[", $key,"]=",$value,"<br />";
			}
*/
			 // post back to PayPal system to validate
			 $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
			 $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			 $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			 $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
			 // If possible, securely post back to paypal using HTTPS
			 // Your PHP server will need to be SSL enabled
			 // $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

			 if (!$fp) {
	 		 		LogStr("Failed to connect to paypal for return value while checking confirmation on paypal","donation") ;
					DisplayDonate(array(),"while checking confirmation with paypal") ;
					exit(0) ;
	 		 } 
			 else {
			 	 fputs ($fp, $header . $req); // sending the query to paypal
			 	 // read the body data 
			 	 $res = '';
				 $headerdone = false;
				 while (!feof($fp)) { // while result not received
				 	$line = fgets ($fp, 1024); // reading the result
					if (strcmp($line, "\r\n") == 0) {
						// read the header
						$headerdone = true;
					}
					else if ($headerdone) {
	 		 			 LogStr("Requesting paypal for confirmation (\$tx_token=".$tx_token.") [".$line."]","donation") ;
						 // header has been read. now read the contents
						 $res .= $line;
					}
				 }

				 // parse the data to read the return variables by paypal
				 $lines = explode("\n", $res);
				 $keyarray = array();
				 if (strcmp ($lines[0], "SUCCESS") == 0) {
				 	for ($i=1; $i<count($lines);$i++){ // Retrieve the parameters
						if (strpos($lines[$i],"=")) {
						   list($key,$val) = explode("=", $lines[$i]);
						}
						$keyarray[urldecode($key)] = urldecode($val);
					}
					
					$ItsOK=true ;
					
					$txn_id = $keyarray['txn_id'];

					if ($payment_amount!=$keyarray['mc_gross']) { // If amount differs we will not continue
					   $ItsOK=false ;
	 		 		   LogStr("Problem for \$payment_amount expected=".$payment_amount." return par paypal confirmation=".$keyarray['mc_gross'],"donation") ;
					}
					if ($payment_currency!=$keyarray['mc_currency']) { // If currency differs we will not continue
					   $ItsOK=false ;
	 		 		   LogStr("Problem for \$payment_currency expected=".$payment_currency." return par paypal confirmation=".$keyarray['mc_currency'],"donation") ;
					}
					
  				 	if ($keyarray['txn_id']!=$tx) { // If control code differs we will not continue
					   $ItsOK=false ;
	 		 		   LogStr("Problem for txn_id expected=".$tx." return par paypal confirmation=".$keyarray['txn_id'],"donation") ;
  				 	}
					
					if (!$ItsOK) { 
						DisplayDonate(array(),"We detected a problem while checking the success of your donation on paypal") ;
						exit(0) ;
					}
					
  				 	$IdMember=0 ; $IdCountry=0 ; // This values will remain if the user was not logged
  				 	if (isset($_SESSION["IdMember"])) {
  	 		 					$IdMember=$_SESSION["IdMember"] ;
  	 		 					$m=LoadRow("select IdCountry from members,cities where members.id=".$IdMember." and cities.id=members.IdCity") ;
  	 		 					$IdCountry=$m->IdCountry ; 
  				 	}

  				 	$referencepaypal=  "ID #".$keyarray['txn_id']." payment_status=".$keyarray['payment_status'] ;
					if ($keyarray['mc_currency']=="USD") {
					   $payment_currency="$" ;
					}
					else if ($keyarray['mc_currency']=="EUR") {
					   $payment_currency="€" ;
					}
					else {
					   $payment_currency=$keyarray['mc_currency'] ;
					}
					
					$receiver_email=$keyarray['payer_email'] ;
					
					// now test if this donation was allready registrated
					$rr=LoadRow("select * from  donations where IdMember=".$IdMember." and referencepaypal like '%".$referencepaypal."%'") ;
					if (isset($rr->id)) { // If a previous version was already existing, it means a double signup
		 				LogStr("Same Donation Submited several time for ".$keyarray['mc_gross'].$payment_currency." by ".$keyarray['first_name']." ".$keyarray['last_name']."/".$receiver_email." status=".$payment_status." [expected".$_SESSION["PaypalBW_key"]." received=".$tx."]","Donation") ;
						DisplayDonate(array(),"Your donation is registrated only once , not need to submit twice ;-)") ;
						exit(0) ;
					}
			
			
					$memo="" ;
					if (isset($keyarray['memo'])) {
					   $memo=$keyarray['memo'] ;
					}
					$str="INSERT INTO `donations` ( `IdMember`,`Email`,`StatusPrivate`,`created`,`Amount`,`Money`,`IdCountry`,`namegiven`,`referencepaypal`,`membercomment`,`SystemComment` ) VALUES (".$IdMember.",'".$receiver_email."','showamountonly',now(),".$payment_amount.",'".$payment_currency."',".$IdCountry.",'".$keyarray['first_name']." ".$keyarray['last_name']."','".$referencepaypal."','','Via paypal"." ".$keyarray['payment_status']." ".$memo."')" ;

					$qry = mysql_query($str);
				 	LogStr("donation ID #".$referencepaypal." recorded","donation") ;

		header("Location: https://www.bewelcome.org/donate/list") ; exit(0) ;
				 	DisplayDonate(fDonation(),ww("donatethanks")) ;
				 	fclose($fp) ;
				 	exit(0) ;
			} // end if verified
			DisplayDonate(array(),"not verified") ;
			LogStr("can't find verified in paypal return information for ID #".$tx." recorded","donation") ;
			exit(0) ;
	} // enf if fp
	break ;

	case "cancel" :
		LogStr("Donation cancelled ","Donation") ;

		DisplayDonate(fDonation(),ww("DonationCancelled")) ;
		break ;

	Default:
		header("Location: https://www.bewelcome.org/donate") ; exit(0) ;
		DisplayDonate(fDonation()) ;
		break ;
	}

	require_once "layout/footer.php";
	exit(0) ;
?>