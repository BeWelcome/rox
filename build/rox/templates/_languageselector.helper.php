<?php

function _languageSelector()
{
    $model = new FlaglistModel();
    $languages = $model->getLanguages();
    $langsel = '';
    $request_string = implode('/',PVars::__get('request'));
    
    foreach($languages as $language) {
        $abbr = $language->ShortCode;
        $title = $language->Name;
        $png = $abbr.'.png';
        if (!isset($_SESSION['lang'])) {
            // hmm
        } else { // if ($_SESSION['lang'] == $abbr) {               
            $langsel .=
                "<a href=\"".PVars::getObj("env")->baseuri."rox/in/".$abbr.'/'.$request_string.
                "\">"
                . $title . "</a>\n"
                ;
        }
    }
    
    return $langsel;
}

?>