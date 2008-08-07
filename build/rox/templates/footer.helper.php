<?php

require_once "_languageselector.helper.php";



$words = new MOD_words();
$languageSelector = _languageSelector();


/**
 * move back to better spot once htdocs/bw/layout/footer.php is gone
 */
function _getVersionInfo()
{
    // TODO: add alpha/test/live
    if (file_exists("revision.txt")) {   // htdocs is default dir
        $version = 'r' . file_get_contents("revision.txt");
    } else {
        $version = "local";
    }
    return $version;
}

$versionInfo = _getVersionInfo();

/**
 * used in footer
 */
function _getBugreportLink()
{
    global $versionInfo;

    $url = "http://www.bevolunteer.org/trac/newticket?";
    $url .= "description=";
    $info =
        'BW Rox version: ' . $versionInfo . "\n" .
        'user agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\n" .
        'request uri: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']  . "\n";
    $url .= urlencode($info);
    $url .= "&amp;summary=bug%20report";
    return $url;
}


$bugreportLink = _getBugreportLink();

$query_list = PVars::get()->query_history;

?>
