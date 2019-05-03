<?php

/**
 * Class AdminTreasurerModel
 */
class AdminTreasurerModel extends RoxModelBase {

    public function treasurerEditCreateDonationVarsOk(&$vars) {
        $errors = array();
        if (empty($vars['donate-username'])) {
            $errors[] = 'AdminTreasurerDonorEmpty';
        } else {
            if ($vars['donate-username'] == "-empty-") {
                $vars['IdMember'] = 0;
            } else {
                $donor = $this->createEntity('Member')->findByUsername($vars['donate-username']);
                if (!$donor) {
                    $errors[] = 'AdminTreasurerUnknownDonor';
                } else {
                    $vars['IdMember'] = $donor->id;
                }
            }
        }
        if (!is_numeric($vars['donate-amount'])) {
            $errors[] = 'AdminTreasurerDonatedAmountInvalid';
        }
        if (empty($vars['donate-date'])) {
            $errors[] = 'AdminTreasurerDonatedOnEmpty';
        } else {
            $date = $vars['donate-date'];
            if ((strlen($date) < 8) || (strlen($date) > 10)) {
                 $errors[] = 'AdminTreasurerDonatedOnInvalid';
            } else {
                list($day, $month, $year) = preg_split('/[\/.-]/', $date);
                if (substr($month,0,1) == '0') $month = substr($month,1,2);
                if (substr($day,0,1) == '0') $day = substr($day,1,2);
                $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
                $vars['DonatedOn'] = date('YmdHis', $start);
            }
        }
        return $errors;
    }

    public function getGeonameIdForCountryCode($countrycode) {
        $query = "
            SELECT
                geonameid
            FROM
                geonames AS g
            WHERE
                g.fcode LIKE 'PCL%'
                AND g.country = '" . $countrycode . "'";
        $cc = $this->singleLookup($query);
        if ($cc) {
            return $cc->geonameid;
        }
        return 0;
    }

    public function getCountryCodeForGeonameId($geonameid) {
        $query = "
            SELECT
                country
            FROM
                geonames AS g
            WHERE
                g.geonameid = " . $geonameid;
        $cc = $this->singleLookup($query);
        if ($cc) {
            return $cc->fk_countrycode;
        }
        return false;
    }

    public function createDonation($memberid, $donatedon, $amount, $comment, $countryid) {
        $query = "
            INSERT INTO
                donations
            SET
                IdMember = " . $memberid . ",
                Email = '',
                StatusPrivate = 'showamountonly',
                created = '" .  $donatedon . "',
                Amount = " . $amount . ",
                Money = '',
                IdCountry = " . $countryid . ",
                namegiven = '',
                referencepaypal = '',
                membercomment = '',
                SystemComment = '" . $this->dao->escape($comment) . "'";
        $affected = $this->dao->exec($query);
        if ($affected != 1) {
            return false;
        }
        return true;
    }

    public function updateDonation($id, $memberid, $donatedon, $amount, $comment, $countryid) {
        $query = "
            UPDATE
                donations
            SET
                IdMember = " . $memberid . ",
                Email = '',
                StatusPrivate = 'showamountonly',
                created = '" .  $donatedon . "',
                Amount = " . $amount . ",
                Money = '',
                IdCountry = " . $countryid . ",
                namegiven = '',
                referencepaypal = '',
                membercomment = '',
                SystemComment = '" . $this->dao->escape($comment) . "'
            WHERE
                id = " . $id;
        $sql = $this->dao->query($query);
        if (!$sql) {
            return false;
        }
        return true;
    }

    public function getRecentDonations() {
        $donateModel = new DonateModel();
        return $donateModel->getDonations(true);
    }

    public function getStatForDonations() {
        $donateModel = new DonateModel();
        return $donateModel->getStatForDonations();
    }

    public function getDonationCampaignValues() {
        $donateModel = new DonateModel();
        return $donateModel->getCampaignValues();
    }

    public function getDonation($id) {
        $query = "
            SELECT
                *
            FROM
                donations
            WHERE
                id = " . $id;
        return $this->singleLookup($query);
    }

    public function getDonationCampaignStatus() {
        $query = "
            SELECT
                ToggleDonateBar
            FROM
                params";
        $r = $this->singleLookup($query);
        if (isset($r)) {
            return $r->ToggleDonateBar;
        }
        return false;
    }

    public function treasurerStartDonationCampaignVarsOk(&$vars) {
        $errors = array();
        if (!is_numeric($vars['donate-needed-per-year'])) {
            $errors[] = 'AdminTreasurerNeededAmountInvalid';
        }
        if (empty($vars['donate-start-date'])) {
            $errors[] = 'AdminTreasurerStartDateEmpty';
        } else {
            $date = $vars['donate-start-date'];
            if ((strlen($date) < 8) || (strlen($date) > 10)) {
                 $errors[] = 'AdminTreasurerStartDateInvalid';
            } else {
                list($day, $month, $year) = preg_split('/[\/.-]/', $date);
                if (substr($month,0,1) == '0') $month = substr($month,1,2);
                if (substr($day,0,1) == '0') $day = substr($day,1,2);
                $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
                $vars['StartDate'] = date('Y-m-d', $start);
            }
        }
        return $errors;
    }

    public function startDonationCampaign($vars) {
        $donateModel = new DonateModel();
        $success = $donateModel->setCampaignValues($vars['donate-needed-per-year'], $vars['StartDate']);
        if (!$success) {
            return false;
        }
        $query = "
            UPDATE
                params
            SET
                ToggleDonateBar = 1";
        $r = $this->dao->query($query);
        if ($r->affectedRows() != 1) {
            return false;
        };
        return true;
    }

    public function stopDonationCampaign() {
        $query = "
            UPDATE
                params
            SET
                ToggleDonateBar = 0";
        $r = $this->dao->query($query);
        if ($r->affectedRows() != 1) {
            return false;
        };
        return true;
    }
}