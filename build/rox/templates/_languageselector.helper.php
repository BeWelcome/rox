<?php

function _languageSelector()
{
    $model = new FlaglistModel();
    $languages = $model->getLanguages();
    $langsel = '';
    $request_string = htmlspecialchars(implode('/',PVars::get()->request), ENT_QUOTES);

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

function cmpLang($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
}

function _languageOptions($words) {
    $model = new FlaglistModel();
    $languages = $model->getLanguages();
    $langarr = array();
    foreach($languages as $language) {
        $lang = new StdClass;
        $lang->Name = $language->Name;
        $lang->TranslatedName = $words->getSilent($language->WordCode);
        $lang->ShortCode = $language->ShortCode;
        $langarr[] = $lang;
    }
    usort($langarr, "cmpLang");

    $langOptions = '';
    $request_string = htmlspecialchars(implode('/',PVars::get()->request), ENT_QUOTES);
    $langOptions = '';
    foreach($langarr as $language) {
        $abbr = $language->ShortCode;
        $png = $abbr.'.png';
        if (!isset($_SESSION['lang'])) {
            // hmm
        } else {
            $langOptions .=
                '<option value="rox/in/'.$abbr.'/'.$request_string.'" '.(($_SESSION['lang'] == $abbr) ? 'selected="selected"' : '');
            $langOptions .= '>' . $language->TranslatedName . ' (' . trim($language->Name) . ')</option>';
        }
    }
    return $langOptions;
}

function _languageSelectorDropDown()
{
    $words = new MOD_words();
    $langsel = '';
    $request_string = htmlspecialchars(implode('/',PVars::get()->request), ENT_QUOTES);
    $langsel = '
    <form id="language_select" action="a" method="post">
    '.$words->get('Languages').':
      <select id="language" name="language" class="combo" onchange="window.location.href=this.value; return false">'
    ;
    $langsel .= _languageOptions($words) . '</select></form>';
    return $langsel;
}

function _languageFooterSelectorDropDown()
{
    $words = new MOD_words();
    $langsel = '';
    $request_string = htmlspecialchars(implode('/',PVars::get()->request), ENT_QUOTES);
    $langsel = '
    <form style="display: inline;" action="a" method="post">
      <select id="language" name="language" class="combo" onchange="window.location.href=this.value; return false">';
    $langsel .= _languageOptions($words) . '</select></form>';
    return $langsel;
}

?>
