<?php

namespace App\Model;

use App\Entity\Params;

class DonateModel extends BaseModel
{
    public function getStatForDonations()
    {
        $campaignValue = $this->getCampaignValues();
        if ($campaignValue) {
            $requiredPerMonth = floor($campaignValue['neededperyear'] / 12);
            $sql = "
                SELECT
                    COALESCE(SUM(amount),0) AS YearDonation,
                    year(NOW()) AS yearnow,
                    month(NOW()) AS month,
                    quarter(NOW()) AS quarter
                FROM
                    donations
                WHERE
                    created > '".$campaignValue['campaignstartdate']->format('Y-m-d H:i:s')."'
                ";
            $rowYear = $this->execQuery($sql)->fetch();
            switch ($rowYear['quarter']) {
                case 1:
                    $start = $rowYear['yearnow'].'-01-01';
                    $end = $rowYear['yearnow'].'-04-01';
                    break;
                case 2:
                    $start = $rowYear['yearnow'].'-04-01';
                    $end = $rowYear['yearnow'].'-07-01';
                    break;
                case 3:
                    $start = $rowYear['yearnow'].'-07-01';
                    $end = $rowYear['yearnow'].'-10-01';
                    break;
                case 4:
                    $start = $rowYear['yearnow'].'-10-01';
                    $end = $rowYear['yearnow'].'-12-31';
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
            $result = $this->execQuery($query);
            $row = $result->fetch(\PDO::FETCH_OBJ);
            $row->QuarterDonation = sprintf('%d', $row->Total);
            $row->MonthNeededAmount = $requiredPerMonth;
            $row->YearNeededAmount = $campaignValue['neededperyear'];
            $row->QuarterNeededAmount = $requiredPerMonth * 3;
            $row->YearDonation = $rowYear['YearDonation'];

            return $row;
        }
        $campaignDetails = new \stdClass();
        $campaignDetails->year = 0;
        $campaignDetails->YearNeededAmount = 0;
        $campaignDetails->YearDonation = 0;

        return $campaignDetails;
    }

    public function getCampaignValues()
    {
        $query = $this
            ->em
            ->getRepository(Params::class)
            ->createQueryBuilder('d')
            ->select([
                'd.neededperyear',
                'd.campaignstartdate',
            ])
            ->getQuery();
        $results = $query->getResult();

        return (empty($results)) ? null : $results[0];
    }
}
