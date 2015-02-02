<?php

require_once "_languageselector.helper.php";



$words = new MOD_words();

/**
 * remove and use $versionInfo = $this->getVersionInfo(); instead
 * once htdocs/bw/layout/footer.php is gone
 */
function _getVersionInfo()
{
    $revisionFile = "../revision.txt";
    if (file_exists($revisionFile)) {
        $version = substr(file_get_contents($revisionFile), 0, 7);
    } else {
        $version = "0000000";
    }
    return $version;
}

$versionInfo = _getVersionInfo();

// Deployment date and time
$mtime = filemtime(__FILE__);
$deploymentDate = date("j M Y", $mtime);
$deploymentDateTime = date(DATE_RSS, $mtime);

/**
 * used in footer
 */
function _getBugreportLink()
{
    $url = PVars::getObj("env")->baseuri . "feedback?";
    $url .= "IdCategory=1&amp;";
    $url .= "RequestURI=";
    $url .= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

    return $url;
}


$bugreportLink = _getBugreportLink();

$query_list = PVars::get()->query_history;
?>
