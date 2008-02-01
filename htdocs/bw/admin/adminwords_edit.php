<?php

require_once "../lib/init.php";

$code = $_GET['code'];

if(isset($_GET['lang'])) {
    $lang = $_GET['lang'];
} else if(isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'en';
}

if ($row = mysql_fetch_object(sql_query(
    "SELECT * FROM words WHERE code='$code' AND ShortCode='$lang'"
))) {
    $idword = $row->id;
    header("Location: adminwords.php?idword=$idword");
    exit;
} else {
    header("Location: adminwords.php?code=$code&lang=$lang");
    exit;
}

?>