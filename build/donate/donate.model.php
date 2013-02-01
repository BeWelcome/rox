<?php


class DonateModel extends RoxModelBase
{

    /**
     * Get donation statistics
     *   - QuarterDonation:     Received donations for current quarter
     *   - MonthNeededAmount:   Required donations per month
     *   - YearNeededAmount:    Required donations per year
     *   - QuarterNeededAmount: Required donations per quarter
     *   - YearDonation:        Received donations for current year
     *
     * @return object Database result row with string properties
     */
    public function getStatForDonations() {
        // check if donate.ini exists and get values
        list($requiredPerYear, $campaignStart) = $this->getCampaignValues();
        $requiredPerMonth = floor($requiredPerYear / 12);
        
        // Calculate donations received for current year
        $result = $this->dao->query("
            SELECT
                SUM(amount) AS YearDonation,
                year(NOW()) AS yearnow,
                month(NOW()) AS month,
                quarter(NOW()) AS quarter
            FROM
                donations
            WHERE
                created > '" . $campaignStart . "'
            ");
        $rowYear = $result->fetch(PDB::FETCH_OBJ);

        switch ($rowYear->quarter) {
            case 1:
                $start = $rowYear->yearnow . "-01-01";
                $end = $rowYear->yearnow . "-04-01";
                break;
            case 2:
                $start = $rowYear->yearnow . "-04-01";
                $end = $rowYear->yearnow . "-07-01";
                break;
            case 3:
                $start = $rowYear->yearnow . "-07-01";
                $end = $rowYear->yearnow . "-10-01";
                break;
            case 4:
                $start = $rowYear->yearnow . "-10-01";
                $end = $rowYear->yearnow . "-12-31";
                break;
        }

        $query = "
            SELECT
                SUM(ROUND(amount)) AS Total,
                year(now()) AS year
            FROM
                donations
            WHERE
                created >= '$start'
                AND
                created < '$end'
            ";
        $result = $this->dao->query($query);

        $row = $result->fetch(PDB::FETCH_OBJ);
        $row->QuarterDonation = sprintf("%d", $row->Total);
        $row->MonthNeededAmount = $requiredPerMonth;
        $row->YearNeededAmount = $requiredPerYear;
        $row->QuarterNeededAmount = $requiredPerMonth * 3;
        $row->YearDonation = $rowYear->YearDonation;

        return $row;
     }

    /**
     * Get donations (max. 25, all if user has Treasurer rights)
     *
     * @param recent Get only the results since the start of the current campaign
     * @return array List of donations as objects with string properties
     *
     * TODO: Add parameter for limit and do permission check elsewhere
     */
    public function getDonations($recent = false) {
        $rights = MOD_right::get();
        $where = "";
        list($dummy, $campaignStart) = $this->getCampaignValues();
        if ($rights->hasRight('Treasurer')) {
            $limitClause = "";
            if ($recent) {
                $where = "WHERE created >= '" . $campaignStart . "'";
            }
        } else {
            $limitClause = "LIMIT 25";
        }
        $query = "
            SELECT
                *
            FROM
                donations
            " . $where . "
            ORDER BY
                created DESC
            $limitClause
            ";
        $result = $this->dao->query($query);
        $donations = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if ($row->IdCountry == 0) {
                $countryName = "Unknown country";
            } else {
                $idCountry = intval($row->IdCountry);
                $resultcountry = $this->dao->query("
                    SELECT
                        name
                    FROM
                        geonames_cache
                    WHERE
                        geonameId = $idCountry
                    ");
                $country = $resultcountry->fetch(PDB::FETCH_OBJ);
                $countryName = $country->name;
            }
            $row->CountryName = $countryName;
            array_push($donations, $row);
        }
        return $donations;
    }

    public function returnFromPayPal()
    {    
          global $_SYSHCVOL ; // this is needed to be able to read the value of paypal_authtoken !
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
          else {
            MOD_log::get()->write("_SYSHCVOL[paypal_authtoken] is not set","donation") ;
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
                    $query = "
                        SELECT
                            geonames_cache.parentCountryId AS IdCountry
                        FROM
                            members,
                            geonames_cache
                        WHERE
                            members.id = $IdMember
                            AND
                            geonames_cache.geonameId = members.IdCity
                        ";
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
                    MOD_log::get()->write("Same Donation Submited several times for ".$keyarray['mc_gross'].$payment_currency." by ".$keyarray['first_name']." ".$keyarray['last_name']."/".$receiver_email." status=".$keyarray['payment_status']." received=".$tx."]","Donation") ;
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
            MOD_log::get()->write("can't find verified in paypal return information for ID #".$tx." recorded ".$res,"donation");
            $error = "not verified";
            return $error;
        } // enf if fp
    }

    public function getCampaignValues() {
        $query = "
            SELECT
                neededperyear,campaignstartdate
            FROM
                params";
        $r = $this->singleLookup($query);
        if (!$r) {
            // failed return defaults (might miss a DB update)
            return array(1260, '2012-10-11');
        } else {
            return array($r->neededperyear, $r->campaignstartdate);
        }
    }

    public function setCampaignValues($neededPerYear,$campaignStartDate) {
        $query = "
            UPDATE
                params
            SET
                neededperyear = " . $neededPerYear . ",
                campaignstartdate = '" . $campaignStartDate . "'";
        $r = $this->dao->query($query);
        if (!$r) {
            return false;
        }
        return true;
    }
}
?>
