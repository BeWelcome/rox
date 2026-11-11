<?php

namespace App\Model;

use App\Entity\Params;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;

readonly class DonateModel
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getStatForDonations(): mixed
    {
        $campaignValue = $this->getCampaignValues();
        if ($campaignValue) {
            $requiredPerMonth = floor($campaignValue['neededperyear'] / 12);
            $connection = $this->entityManager->getConnection();
            $yearDonation = $connection->fetchOne(
                'SELECT COALESCE(SUM(amount), 0) FROM donation WHERE created > ?',
                [$campaignValue['campaignstartdate']->format('Y-m-d H:i:s')],
            );

            // the current quarter, from its first day to the first day of the next one
            $now = Carbon::now();
            $start = $now->copy()->firstOfQuarter()->startOfDay();
            $end = $start->copy()->addMonths(3);
            $row = [
                'Total' => $connection->fetchOne(
                    'SELECT SUM(ROUND(amount)) FROM donation WHERE created >= ? AND created < ?',
                    [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')],
                ),
                'year' => $now->year,
            ];
            $row['QuarterDonation'] = \sprintf('%d', $row['Total']);
            $row['MonthNeededAmount'] = $requiredPerMonth;
            $row['YearNeededAmount'] = $campaignValue['neededperyear'];
            $row['QuarterNeededAmount'] = $requiredPerMonth * 3;
            $row['YearDonation'] = $yearDonation;
            $row['year'] = Carbon::instance($campaignValue['campaignstartdate'])->year;

            return $row;
        }
        $campaignDetails = new stdClass();
        $campaignDetails->year = 0;
        $campaignDetails->YearNeededAmount = 0;
        $campaignDetails->YearDonation = 0;

        return $campaignDetails;
    }

    public function getCampaignValues()
    {
        $query = $this
            ->entityManager
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
