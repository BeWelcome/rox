<?php
    function cmpPrefLang($a, $b)
    {
        if ($a->TranslatedName == $b->TranslatedName) {
            return 0;
        }
        return (strtolower((string) $a->TranslatedName) < strtolower((string) $b->TranslatedName)) ? -1 : 1;
    }

    $words = $this->getWords();
    $layoutkit = $this->layoutkit;
    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
    $flaglist = new FlaglistModel();
    $languages = $flaglist->getLanguages();
    foreach($languages as &$language) {
        $language->TranslatedName = $words->getSilent($language->wordcode);
    }
    usort($languages, "cmpPrefLang");
    $p = $this->member->preferences;

    //get baseuri
    $baseuri = PVars::getObj('env')->baseuri;
    if (PVars::getObj('env')->force_ssl_sensitive) {
        $baseuri = PVars::getObj('env')->baseuri_https;
    }


    // Check if preferred language is set
    if (!isset($p['PreferenceLanguage']->Value)) {
        $p['PreferenceLanguage']->Value = $this->session->get('IdLanguage');
    }
    // var_dump ($p);
    $ii = 1;

    if (!$memory = $formkit->getMemFromRedirect()) {
        // no memory
    } else {
        // from previous form
        if ($memory->post) {
            foreach ($memory->post as $key => $value) {
                $vars[$key] = $value;
            }
        }
        // problems from previous form
        if (is_array($memory->problems)) {
            echo '<div class="error">';
            foreach ($memory->problems as $key => $value) {
                ?>
                <p><?=$words->get($value) ?></p>
                <?php
            }
            echo '</div>';
        }
    }
    $timezones =  [
         [ "timeshift" => -39600, "city" => "Pago Pago", "utc" => "-11"], // $words->get('Pago Pago');
         [ "timeshift" => -36000, "city" => "Honolulu", "utc" => "-10"], // $words->get('Honolulu');
         [ "timeshift" => -32400, "city" => "Fairbanks", "utc" => "-9"], // $words->get('Fairbanks');
         [ "timeshift" => -28800, "city" => "Los Angeles", "utc" => "-8"], // $words->get('Los Angeles');
         [ "timeshift" => -25200, "city" => "Calgari", "utc" => "-7"], // $words->get('Calgari');
         [ "timeshift" => -21600, "city" => "Mexico City", "utc" => "-6"], // $words->get('Mexico City');
         [ "timeshift" => -18000, "city" => "New York", "utc" => "-5"], // $words->get('New York');
         [ "timeshift" => -16200, "city" => "Carracas", "utc" => "-4.5"], // $words->get('Carracas');
         [ "timeshift" => -14400, "city" => "Santiago", "utc" => "-4"], // $words->get('Santiago');
         [ "timeshift" => -10800, "city" => "Sao Paulo", "utc" => "-3"], // $words->get('Sao Paulo');
         [ "timeshift" => -7200, "city" => "Fernando de Noronha", "utc" => "-2"], // $words->get('Fernando de Noronha');
         [ "timeshift" => -3600, "city" => "Cape Verde", "utc" => "-1"], // $words->get('Cape Verde');
         [ "timeshift" => 0, "city" => "London", "utc" => "+0"], // $words->get('London');
         [ "timeshift" => 3600, "city" => "Paris, Berlin", "utc" => "+1"], // $words->get('Paris, Berlin');
         [ "timeshift" => 7200, "city" => "Cairo", "utc" => "+2"], // $words->get('Cairo');
         [ "timeshift" => 10800, "city" => "Moscow", "utc" => "+3"], // $words->get('Moscow');
         [ "timeshift" => 14400, "city" => "Dubai", "utc" => "+4"], // $words->get('Dubai');
         [ "timeshift" => 18000, "city" => "Karachi", "utc" => "+5"], // $words->get('Karachi');
         [ "timeshift" => 19800, "city" => "Mumbai", "utc" => "+5.5"], // $words->get('Mumbai');
         [ "timeshift" => 21600, "city" => "Dhaka", "utc" => "+6"], // $words->get('Dhaka');
         [ "timeshift" => 25200, "city" => "Jakarta", "utc" => "+7"], // $words->get('Jakarta');
         [ "timeshift" => 28800, "city" => "Hong Kong", "utc" => "+8"], // $words->get('Hong Kong');
         [ "timeshift" => 32400, "city" => "Tokyo", "utc" => "+9"], // $words->get('Tokyo');
         [ "timeshift" => 36000, "city" => "Sydney", "utc" => "+10"], // $words->get('Sydney');
         [ "timeshift" => 39600, "city" => "Noumea", "utc" => "+11"], // $words->get('Noumea');
         [ "timeshift" => 43200, "city" => "Auckland", "utc" => "+12"], // $words->get('Auckland');
    ];
