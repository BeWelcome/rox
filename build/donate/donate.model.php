<?php

use App\Utilities\PaypalIPN as PaypalIPN;

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
                COALESCE(SUM(amount),0) AS YearDonation,
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
     * @param bool recent Get only the results since the start of the current campaign
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
                        geonames
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

    public function processIpnNotificationFromPayPal()
    {
        $logStr = implode(' , ', $_POST);
        MOD_log::get()->write($logStr, 'donation');

        try {
            $ipn = new PaypalIPN();// Use the sandbox endpoint during testing.
            $ipn->useSandbox();
            $verified = $ipn->verifyIPN();
            if ($verified) {
                MOD_log::get()->write("Verified", 'donation');
                /*
                 * Process IPN
                 * A list of variables is available here:
                 * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
                 */
            }// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
            header("HTTP/1.1 200 OK");
            PPHP::PExit();
        } catch (Exception $e) {
        }
    }

    public function returnFromPayPal()
    {
        return true;
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
