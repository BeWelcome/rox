<?php


class DonateModel extends PAppModel
{
    
    
    
    
    
    
    /**
     * Returns an array with the mist of X latest donations (all donation in case the current user has Treasurer rights)
     *
     */    
    public function getDonations() {
        $TDonations = array() ;
        $R = MOD_right::get();
        $hasRight = $R->hasRight('Treasurer');
        if ($hasRight) {
           $query = "select * from donations order by created desc" ;
        }
        else {
           $query = "select * from donations order by created desc limit 10" ;
        }
        $result = $this->dao->query($query);
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
              array_push($TDonations, $row);
        }
        return($TDonations) ;
    }
    
    
    
    
    
    
    public function returnFromPayPal()
    {    
/*    
//The donation returns an url as the following
http://www.bewelcome.org/donate/?action=done&tx=0ME24142PE152304A&st=Completed&amt=5.00&cc=EUR&cm=&item_number=&sig=hYUTlSOjBeJvNqfFqc%252fZbrBA4p6c%252fe6EErVp1w18eOBR96p6hzzenPysL%252bFVPZi8YEcONFovQmYn%252b6QF%252fBYoVhGMoaQJCxBQh%252bLAlC0TdgeScs1skk0%252bpY6SyoC%252fNCV1ou69zWRrhDrtsa4SUHibLD%252f1RwGg43iaZjPhB24I6lg%253d
*/
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
            MOD_log::get()->write("Failed to connect to paypal for return value while checking confirmation on paypal","donation") ;
            $error = "A problem occured while checking confirmation with paypal";
            return $error;
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
                     MOD_log::get()->write("Requesting paypal for confirmation (\$tx_token=".$tx_token.") [".$line."]","donation") ;
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
                   MOD_log::get()->write("Problem for \$payment_amount expected=".$payment_amount." return par paypal confirmation=".$keyarray['mc_gross'],"donation") ;
                }
                if ($payment_currency!=$keyarray['mc_currency']) { // If currency differs we will not continue
                   $ItsOK=false ;
                   MOD_log::get()->write("Problem for \$payment_currency expected=".$payment_currency." return par paypal confirmation=".$keyarray['mc_currency'],"donation") ;
                }
                
                if ($keyarray['txn_id']!=$tx) { // If control code differs we will not continue
                   $ItsOK=false ;
                   MOD_log::get()->write("Problem for txn_id expected=".$tx." return par paypal confirmation=".$keyarray['txn_id'],"donation") ;
                }
                
                if (!$ItsOK) { 
                    $error = "We detected a problem while checking the success of your donation on paypal";
                    return $error;
                }
                
                $IdMember=0 ; $IdCountry=0 ; // This values will remain if the user was not logged
                if (isset($_SESSION["IdMember"])) {
                    $IdMember=$_SESSION["IdMember"] ;
                    $query = '
SELECT IdCountry
FROM  members,cities
WHERE members.id='.$IdMember.'
AND cities.id=members.IdCity';
                    $result = $this->dao->query($query);
                    $m = $result->fetch(PDB::FETCH_OBJ);
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
                $query = '
SELECT *
FROM  donations
WHERE IdMember='.$IdMember.'
AND referencepaypal LIKE "%'.$referencepaypal.'%"';
                $result = $this->dao->query($query);
                $rr = $result->fetch(PDB::FETCH_OBJ);
        
                if (isset($rr->id)) { // If a previous version was already existing, it means a double signup
                    MOD_log::get()->write("Same Donation Submited several times for ".$keyarray['mc_gross'].$payment_currency." by ".$keyarray['first_name']." ".$keyarray['last_name']."/".$receiver_email." status=".$payment_status." [expected".$_SESSION["PaypalBW_key"]." received=".$tx."]","Donation") ;
                    $error = "Your donation is registrated only once , not need to submit twice ;-)";
                    return $error;
                }
        
                $memo="" ;
                if (isset($keyarray['memo'])) {
                   $memo=$keyarray['memo'] ;
                }
                $query = '
INSERT INTO `donations`
( `IdMember`,`Email`,`StatusPrivate`,`created`,`Amount`,`Money`,`IdCountry`,`namegiven`,`referencepaypal`,`membercomment`,`SystemComment` )
VALUES
('.$IdMember.',"'.$receiver_email.'","showamountonly",now(),'.$payment_amount.',"'.$payment_currency.'",'.$IdCountry.',"'.$keyarray["first_name"].' '.$keyarray["last_name"].'","'.$referencepaypal.'","","Via paypal'.' '.$keyarray["payment_status"].' '.$memo.'")
';
                $this->dao->exec($query);
                MOD_log::get()->write("donation ID #".$referencepaypal." recorded","donation") ;
                fclose($fp) ;
                return;
            } // end if verified
            MOD_log::get()->write("can't find verified in paypal return information for ID #".$tx." recorded","donation");
            $error = "not verified";
            return $error;
        } // enf if fp
    }
}


?>