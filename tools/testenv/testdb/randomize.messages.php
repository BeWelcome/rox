/**
*
* Use this script to adapt messages in the test db
*
* Keeps structure of messages alive.
*/
<?php
function anonymizeMessage($msg) {
    $msg = mb_convert_encoding($msg, 'utf-8', 'utf-8');
    $replaced = preg_replace('/[^>\s,;]/ui', 'a', $msg);
    return $replaced;
}

function updateMessages($connection)
{
    $query = "
    SELECT * FROM messages m";
    $result = $connection->query($query);

    $rows = array();
    while ($row = $result->fetch_array(MYSQL_ASSOC)) {
        $msganon = anonymizeMessage($row['Message']);
        $msgesc = $connection->real_escape_string($msganon);
        if (empty($msganon) | empty($msgesc)) {
            echo "\$msganon or \$msgesc empty: id = " . $row['id'] . "\n";
            echo "Original message was: \n";
            echo "+" . $row['Message'] . "+\n";
            echo "Anonymized message was: \n";
            echo "*" . $msganon . "*\n";
            echo "Escaped message was: \n";
            echo "-" . $msgesc . "-\n";
        } else {
            $row['Message'] = $msgesc;
        }
        $rows[] = $row;
    }

    $i = 0;
    $replace = "";
    foreach ($rows as $row) {
        $replace .= "REPLACE messages VALUES (";
            foreach ($row as $value) {
                $replace .= "'" . $value . "',";
            }
            $replace = substr($replace, 0, -1) . ");\n";
        $i++;
        if ($i % 100 == 0) {
            $result1 = $connection->multi_query($replace);
            while ($next = $connection->next_result()) {
                if (!$next) {
                    echo "failed.";
                }
            }
            $replace = '';
            echo "Still updating messages. Reached " . $i . ".\n";
        }
    }
    $result1 = $connection->multi_query($replace);
    while ($next = $connection->next_result()) {
        if (!$next) {
            echo "failed.";
        }
    }
}

// $params = parse_ini_file('../../rox_local.ini');
// print_r($params);
//$mysqli = new mysqli("localhost", $params['user'],$params['password'], $params['dsn']);
$mysqli = new mysqli("localhost", 'root', '', 'bewelcome');

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
echo $mysqli->host_info . "\n";

updateMessages($mysqli);
