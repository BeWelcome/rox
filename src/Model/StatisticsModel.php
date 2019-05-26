<?php

namespace App\Model;

use App\Doctrine\MemberStatusType;

class StatisticsModel extends BaseModel
{
    public function getStatistics()
    {
        $members = $this->execQuery('
            SELECT
                COUNT(*) AS cnt
            FROM
                members m
            WHERE
                m.status IN ('.MemberStatusType::ACTIVE_ALL.')
        ')->fetch();

        $countries = $this->execQuery("
            SELECT
                DISTINCT gc.country
            FROM
                geonamescountries gc
                join geonames g on gc.country = g.country
                join members m on g.geonameId = m.IdCity and m.Status IN ('Active', 'OutOfRemind')        
        ")->fetchAll();

        $languages = $this->execQuery('
            SELECT
                COUNT(DISTINCT l.id) AS cnt
            FROM
                languages l,
                memberslanguageslevel mll,
                members m
            WHERE
                l.id = mll.idLanguage
                AND mll.IdMember = m.Id
                AND m.Status IN ('.MemberStatusType::ACTIVE_ALL.')
        ')->fetch();

        $positiveComments = $this->execQuery("
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                members m
            WHERE
                c.Quality = 'Good'
                AND IdFromMember = m.Id
                AND m.Status IN (".MemberStatusType::ACTIVE_ALL.')
        ')->fetch();

        $activities = $this->execQuery('
            SELECT
                COUNT(a.id) AS cnt
            FROM
                activities a
            WHERE
                a.status = 0
        ')->fetch();

        $stats = [
            'members' => $members['cnt'],
            'countries' => \count($countries),
            'languages' => $languages['cnt'],
            'comments' => $positiveComments['cnt'],
            'activities' => $activities['cnt'],
        ];

        return $stats;
    }
}
