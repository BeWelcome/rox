<?php
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
    if ($i++ < 3115) continue;
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
