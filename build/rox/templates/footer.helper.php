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

function translator_block() {

    if (MOD_right::get()->hasRight("Words", PVars::get()->lang)) {
        ?><div id="translator"><?php
        $pagetotranslate = $_SERVER['PHP_SELF'];
        if ($pagetotranslate { 0 } == "/") {
            // funky array stuff
            $pagetotranslate { 0 } = "_";
        }
        echo "<a href='bw/admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate . "' target='_blank'><img height='11px' width='16px' src='bw/images/switchtrans.gif' alt='go to current translation list for " . $_SERVER['PHP_SELF'] . "' title='go to current translation list for " . $_SERVER['PHP_SELF'] . "' /></a>\n";
        
        $request_string = implode('/',PVars::__get('request'));
        $rox_tr = PVars::getObj("env")->baseuri . "rox/tr_mode";
        $words = new MOD_words();
    
        switch ($words->getTrMode()) {
        case 'translate':
            ?>
            <a href="<?=$rox_tr?>/browse/<?php echo $request_string ?>">browse</a>
            <strong>translate</strong>
            <a href="<?=$rox_tr?>/edit/<?php echo $request_string ?>">edit</a>
            <?php
            break;
        case 'edit':
            ?>
            <a href="<?=$rox_tr?>/browse/<?php echo $request_string ?>">browse</a>
            <a href="<?=$rox_tr?>/translate/<?php echo $request_string ?>">translate</a>
            <strong>edit</strong>
            <?php
            break;
        default:
        case 'browse':
            ?>
            <strong>browse</strong>
            <a href="<?=$rox_tr?>/translate/<?php echo $request_string ?>">translate</a>
            <a href="<?=$rox_tr?>/edit/<?php echo $request_tring ?>">edit</a>
            <?php
            break;
        }
        ?></div><?php
    }
}

?>
