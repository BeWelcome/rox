<?php

namespace AppBundle\Model;

use AppBundle\Entity\Member;

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
                m.status IN ('.Member::ACTIVE_ALL.')
        ')->fetch();

        /*        $countries = $this->execQuery('
                    SELECT
                        COUNT(DISTINCT gc.country) AS cnt
                    FROM
                        geonamescountries gc,
                        geonames g,
                        members m
                    WHERE
                        gc.country = g.country
                        AND g.geonameId = m.IdCity
                        AND m.Status IN (' . Member::ACTIVE_ALL . ')
                ')->fetch();*/

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
                AND m.Status IN ('.Member::ACTIVE_ALL.')
        ')->fetch();

        $positiveComments = $this->execQuery('
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                members m
            WHERE
                c.Quality = \'Good\'
                AND IdFromMember = m.Id
                AND m.Status IN ('.Member::ACTIVE_ALL.')
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
            'countries' => 215,
            'languages' => $languages['cnt'],
            'comments' => $positiveComments['cnt'],
            'activities' => $activities['cnt'],
        ];

        return $stats;
    }
}
