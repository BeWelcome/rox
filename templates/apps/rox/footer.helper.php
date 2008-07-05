<?

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
    $url .= "&summary=bug%20report";
    return $url;
}


$bugreportLink = _getBugreportLink();

// TODO: move to a better place  -- and rename to languageSelector
function _buildFlagList()
{
    $model = new FlaglistModel();
    $languages = $model->getLanguages();
    $flaglist = '';
    $request_string = implode('/',PVars::__get('request'));
    
    foreach($languages as $language) {
        $abbr = $language->ShortCode;
        $title = $language->Name;
        $png = $abbr.'.png';
        if (!isset($_SESSION['lang'])) {
            // hmm
        } else { // if ($_SESSION['lang'] == $abbr) {               
            $flaglist .=
                "<a href=\"rox/in/".$abbr.'/'.$request_string.
                "\">"
                . $title . "</a>\n"
                ;
        }
    }
    
    return $flaglist;
}

$flagList = _buildFlagList();
?>