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

if (!($file = fopen('profiledata.csv', 'r')))
{
    die('cannot open file');

}
$headers = array_flip(fgetcsv($file));

$model = new SignupModel;
$dao = $model->dao;

$cities = $model->bulkLookup(
<<<SQL
SELECT geonameid FROM geonames_cache WHERE fcode IN ('PPL', 'PPLA', 'PPLC') LIMIT 100
SQL
);
$city_count = count($cities);
$i = 1;
while ($line = fgetcsv($file))
{
    $random_city = $cities[mt_rand(0, $city_count - 1)]->geonameid;
    $array = array(
        'username'      => 'testuser' . $line[$headers['Username']],
        'gender'        => $line[$headers['Gender']],
        'genderhidden'  => $line[$headers['HideGender']],
        'password'      => 'password',
        'iso_date'      => $line[$headers['BirthDate']],
        'agehidden'     => 'No',
        'email'         => $line[$headers['Email']],
        'firstname'     => $line[$headers['FirstName']],
        'secondname'    => '',
        'lastname'      => $line[$headers['LastName']],
        'housenumber'   => $line[$headers['HouseNumber']],
        'street'        => $line[$headers['StreetName']],
        'zip'           => $line[$headers['Zip']],
        'geonameid'     => $random_city,
        'status'        => $line[$headers['Status']],
        'accomodation'  => $line[$headers['Accomodation']],
        'restrictions'  => $line[$headers['Restrictions']],
        'typicoffer'    => $line[$headers['TypicOffer']],
    );
    try
    {
        $member_id = $model->registerBWMember($array);
        if ($member_id)
        {
            $id = $dao->nextId('user');
            $query = "INSERT INTO user (id, auth_id, handle, email, pw, active) VALUES ({$id}, 1, '{$array['username']}', '{$array['email']}', password('password'), 1)";
            $query2 = "UPDATE members set Status = '{$array['status']}', Accomodation = '{$array['accomodation']}', TypicOffer = '{$array['typicoffer']}' where id = {$member_id}";
            if ($dao->query($query) && $dao->query($query2))
            {
                echo "Inserted {$array['username']} into database" . PHP_EOL;
            }
            else
            {
                $dao->query("DELETE FROM members WHERE id = {$member_id}");
                echo "Failed to insert member {$array['username']} into database" . PHP_EOL;
            }
        }
        else
        {
            echo "Could not enter testuser {$array['username']}" . PHP_EOL;
            echo $array['errors'] . PHP_EOL;
        }
    }
    catch (Exception $e)
    {
        echo "Could not enter {$array['username']} into database" . PHP_EOL;
        echo $e->getMessage() . PHP_EOL;
        echo mysql_error();
    }
}
