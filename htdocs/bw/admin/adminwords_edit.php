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
    // translation in your lang exists - edit the translation
    $idword = $row->id;
    header("Location: adminwords.php?idword=$idword");
    exit;
} else if ($row = mysql_fetch_object(sql_query(
    "SELECT * FROM words WHERE code='$code' AND ShortCode='en'"
))) {
    // english definition exists - create one in your language
    header("Location: adminwords.php?code=$code&lang=$lang");
    exit;
} else {
    // no english definition - do that first!
    header("Location: adminwords.php?code=$code&lang=en");
    exit;
}

?>