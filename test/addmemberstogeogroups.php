<?php
/*
Copyright (c) 2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @package    Tests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */


/*
 * this script is one giant hack. It will import member data from a .csv file, if present
 * it utilizes the signup code in /build/signup/ - which is incredibly slow
 * most likely due to the very shitty 'encryption' stuff used
 *
 * there is little error checking, just some rudimentary stuff
 * if we ever need to import more members, a better script should be done
 *
 * @author Fake51
 */


require 'core_includes.php';


$model = new SignupModel;
$dao = $model->dao;
$ef = new RoxEntityFactory;

// kill all local group memberships first
$dao->query("DELETE FROM membersgroups WHERE islocal = 1");

$member_ids = $model->bulkLookup(
<<<SQL
SELECT m.id, a.idcity AS geo_id FROM members AS m, addresses AS a WHERE m.Status NOT IN ('Rejected', 'TakenOut', 'Banned', 'Sleeper', 'ChoiceInactive', 'OutOfRemind', 'Renamed', 'ActiveHidden', 'SuspendedBeta', 'AskToLeave', 'StopBoringMe', 'PassedAway', 'Buggy', 'DuplicateSigned') AND m.id = a.idmember;
SQL
);
$i = 0;
echo memory_get_usage() . PHP_EOL;
foreach ($member_ids as $m)
{
    $member = $ef->create('Member')->findById($m->id);
    if (!($location = $ef->create('Geo')->findById($m->geo_id)))
    {
        echo "Count not find location for member ID: {$m->id}." . PHP_EOL;
        continue;
    }

    if (!($group = $ef->create('GeoGroup')->getGroupForGeo($location)))
    {
        $group = $ef->create('GeoGroup')->lazyCreateGeoGroup($location);
    }

    $group->memberJoin($member, 'In', true);
    foreach ($location->getAncestorline() as $ancestor)
    {
        if ($ancestor->isCountry() || $ancestor->isRegion())
        {
            if (!($group = $ef->create('GeoGroup')->getGroupForGeo($ancestor)))
            {
                $group = $ef->create('GeoGroup')->lazyCreateGeoGroup($ancestor);
            }
            $group->memberJoin($member, 'In', true);
        }
    }
    if (($i % 100) == 0) echo memory_get_usage() . PHP_EOL;
    unset($member, $group, $location, $ancestor, $m);
    $i++;
}
echo "Set local groups for {$i} members." . PHP_EOL;
die('done');
