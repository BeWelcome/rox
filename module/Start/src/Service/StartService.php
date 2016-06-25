<?php

namespace Rox\Start\Service;

use Illuminate\Database\ConnectionInterface;

/**
 * Class StartService.
 */
class StartService
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getStatistics()
    {
        $members = $this->connection->select('
            SELECT
                COUNT(*) AS cnt
            FROM
                members m
            WHERE
                m.status IN (' . \Member::ACTIVE_ALL . ')
        ');

        $countries = $this->connection->select('
            SELECT
                COUNT(DISTINCT gc.country) AS cnt
            FROM
                geonamescountries gc,
                geonames g,
                members m
            WHERE
                gc.country = g.country
                AND g.geonameId = m.IdCity
                AND m.Status IN (' . \Member::ACTIVE_ALL . ')
        ');

        $languages = $this->connection->select('
            SELECT
                COUNT(DISTINCT l.id) AS cnt
            FROM
                languages l,
                memberslanguageslevel mll,
                members m
            WHERE
                l.id = mll.idLanguage
                AND mll.IdMember = m.Id
                AND m.Status IN (' . \Member::ACTIVE_ALL . ')
        ');

        $positiveComments = $this->connection->select('
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                members m
            WHERE
                c.Quality = \'Good\'
                AND IdFromMember = m.Id
                AND m.Status IN (' . \Member::ACTIVE_ALL . ')
        ');

        $activities = $this->connection->select('
            SELECT
                COUNT(a.id) AS cnt
            FROM
                activities a
            WHERE
                a.status = 0
        ');

        $stats = [
            'members' => $members[0]->cnt,
            'countries' => $countries[0]->cnt,
            'languages' => $languages[0]->cnt,
            'comments' => $positiveComments[0]->cnt,
            'activities' => $activities[0]->cnt,
        ];

        return $stats;
    }
}
