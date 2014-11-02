/*
Use this script to set random names for the test db
*/

<?php
function updateNamesAndEmail($connection) {
    $first = file('firstnames.csv', FILE_IGNORE_NEW_LINES);
    $last = file('lastnames.csv', FILE_IGNORE_NEW_LINES);

    $query = "SELECT id, email as emailaddress, firstname as first, secondname as second, lastname as last from members";

    $result = $connection->query($query);

    $rows = array();
    while ($row = $result->fetch_array(MYSQL_ASSOC)) {
        $data = $row;
        $data['firstname'] = $first[rand(0,count($first) - 1)];
        if ($row['second'] <> 0) {
            $data['secondname'] = $first[rand(0, count($first) - 1)];
        } else {
            $data['secondname'] = '';
        }
        $data['lastname'] = $last[rand(0, count($last) - 1)];
        $data['ids'] = $row['first'] . ',' . $row['second'] . ',' . $row['last'] . ',' . $row['emailaddress'];
        if (!empty($data['secondname'])) {
            $data['email'] = $data['firstname'] . '.' . substr($data['secondname'], 0, 1) . '.' . $data['lastname'];
        }
        else {
            $data['email'] = $data['firstname'] . '.' . $data['lastname'];
        }
        $data['email'] .= '@test.bewelcome.org';
        $data['email'] = strtolower($data['email']);
		$data['notset'] = 'notset';
        $rows[] = $data;
    }

    $i = 0;
    foreach($rows as $row) {
        $query = "SELECT * from cryptedfields where id IN (" . $row['ids'] . ")";
        $result = $connection->query($query);

        $replacerows = array();
        while ($replacerow = $result->fetch_array(MYSQL_ASSOC)) {
            $column = str_replace('members.', '', strtolower($replacerow['TableColumn']));
            if ($replacerow['IsCrypted'] == 'not crypted') {
                $replacerow['AdminCryptedValue'] = $row[$column];
                $replacerow['MemberCryptedValue'] = $row[$column];
            } else {
                $replacerow['AdminCryptedValue'] = '<admincrypted>' . $row[$column] . '</admincrypted>';
                $replacerow['MemberCryptedValue'] = '<membercrypted>' . $row[$column] . '</membercrypted>';
            }
            $replacerows[] = $replacerow;
        }

        $replace = "";
        foreach($replacerows as $replacerow) {
            $replace .= "REPLACE cryptedfields VALUES (";
            foreach($replacerow as $value) {
                $replace .= "'" . $value . "',";
            }
            $replace = substr($replace, 0, -1) . ");\n";
        }
        $i++;
        if (!empty($replace)) {
            $replace .= "UPDATE user SET email = '" . $row['email'] . "' WHERE handle = 'member-" . $row['id'] . "';\n";
                // echo $replace;
                $result1 = $connection->multi_query($replace);
            while ($connection->next_result()) {
                ;
            }
        }
		if ($i % 1000 == 0) {
			echo "Still updating names and emails. Reached " . $i . ".";
		}
    }
}

function updateStreetnames($connection) {
    $streets = file('streets_osm.txt', FILE_IGNORE_NEW_LINES);

    $query = "SELECT id, HouseNumber as hn, streetname as sn, Zip as zp from addresses";

    $result = $connection->query($query);

    $rows = array();
    while ($row = $result->fetch_array(MYSQL_ASSOC)) {
        $data = $row;
        if ($row['hn'] <> 0) {
            $data['housenumber'] = rand(1, 40000);
        } else {
            $data['housenumber'] = '';
        }
        if ($row['sn'] <> 0) {
            $data['streetname'] = $streets[rand(0, count($streets) - 1)];
        } else {
            $data['streetname'] = '';
        }
        if ($row['zp'] <> 0) {
            $data['zip'] = rand(10000, 99999);
        } else {
            $data['zip'] = '';
        }
		$data['notset'] = 'notset';
        $data['ids'] = $row['hn'] . ',' . $row['sn'] . ',' . $row['zp'];
        $rows[] = $data;
    }

    $i = 0;
    foreach($rows as $row) {
//	if ($i == 2) break;
//	print_r($row);
        $query = "SELECT * from cryptedfields where id IN (" . $row['ids'] . ")";
        $result = $connection->query($query);

        $replacerows = array();
        while ($replacerow = $result->fetch_array(MYSQL_ASSOC)) {
            $column = str_replace('addresses.', '', strtolower($replacerow['TableColumn']));
            if ($replacerow['IsCrypted'] == 'not crypted') {
                $replacerow['AdminCryptedValue'] = $row[$column];
                $replacerow['MemberCryptedValue'] = $row[$column];
            } else {
                $replacerow['AdminCryptedValue'] = '<admincrypted>' . $row[$column] . '</admincrypted>';
                $replacerow['MemberCryptedValue'] = '<membercrypted>' . $row[$column] . '</membercrypted>';
            }
            $replacerows[] = $replacerow;
        }

        $replace = "";
        foreach($replacerows as $replacerow) {
            $replace .= "REPLACE cryptedfields VALUES (";
            foreach($replacerow as $value) {
                $replace .= "'" . $value . "',";
            }
            $replace = substr($replace, 0, -1) . ");\n";
        }
        $i++;
        if (!empty($replace)) {
			// echo $replace;
			$result1 = $connection->multi_query($replace);
			if (!$result1) {
				echo " failed. Errno: " . print_r($connection->error_list);
			}
			while ($connection->next_result()) {
				;
				}
        }
		if ($i % 1000 == 0) {
			echo "Still updating adresses. Reached " . $i . ".";
		}
    }
}

// $params = parse_ini_file('../../rox_local.ini');
// print_r($params);
//$mysqli = new mysqli("localhost", $params['user'],$params['password'], $params['dsn']);
$mysqli = new mysqli("localhost", 'root', '', 'bw_test');

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
echo $mysqli->host_info . "\n";

updateNamesAndEmail($mysqli);
updateStreetnames($mysqli);