<?php

/**
 *  start page model
 *
 * @package Start page
 * @author shevek
 */

namespace Rox\Main\Start;

use Illuminate\Database\Capsule\Manager as Capsule;

class StartModel extends \RoxModelBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
    }

    public function getStatistics() {
        $members = Capsule::select("
            SELECT
                COUNT(*) AS cnt
            FROM
                members m
            WHERE
                m.status IN (" . \Member::ACTIVE_ALL . ")
        ");
        $countries = Capsule::select("
            SELECT
                COUNT(DISTINCT gc.country) AS cnt
            FROM
                geonamescountries gc,
                geonames g,
                members m
            WHERE
                gc.country = g.country
                AND g.geonameId = m.IdCity
                AND m.Status IN (" . \Member::ACTIVE_ALL . ")
        ");
        $languages = Capsule::select("
            SELECT
                COUNT(DISTINCT l.id) AS cnt
            FROM
                languages l,
                memberslanguageslevel mll,
                members m
            WHERE
                l.id = mll.idLanguage
                AND mll.IdMember = m.Id
                AND m.Status IN (" . \Member::ACTIVE_ALL . ")
        ");
        $positiveComments = Capsule::select("
            SELECT
                COUNT(c.id) AS cnt
            FROM
                comments c,
                members m
            WHERE
                c.Quality = 'Good'
                AND IdFromMember = m.Id
                AND m.Status IN (" . \Member::ACTIVE_ALL . ")
        ");
        $activities = Capsule::select("
            SELECT
                COUNT(a.id) AS cnt
            FROM
                activities a
            WHERE
                a.status = 0
        ");

        return array(
            'members' => $members[0]->cnt,
            'countries' => $countries[0]->cnt,
            'languages' => $languages[0]->cnt,
            'comments' => $positiveComments[0]->cnt,
            'activities' => $activities[0]->cnt
        );
    }
}
