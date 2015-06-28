<?php
echo '<p><strong>' . $words->get('SearchSelectLocation') . '</strong></p>';
echo '<div class="floatbox">';
    if (isset($biggest)) :
    // biggest
    $i = 0;
    foreach ($biggest as $big) :
    if ($big->cnt == 0) continue;
    $class = 'c33l';
    if ($i % 3 == 0) {
    echo '
    <div class="subcolumns row">';
        };
        if ($i % 3 == 2) {
        $class = 'c33r';
        }
        echo '<div class="' . $class . '"><span id="geoname' . $big->geonameid . '"><input type="submit" id="geonameid-' . $big->geonameid . '" name="geonameid-' . $big->geonameid . '" value="' . htmlentities($big->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                . htmlentities($big->admin1, ENT_COMPAT, 'utf-8') . ', ' . htmlentities($big->country, ENT_COMPAT, 'utf-8') . ', ' . $words->get('SearchSuggestionsMembersFound', $big->cnt);
            echo '</span></div>';
        if ($i %3 == 2) {
        echo '</div>';
    }
    $i++;
    endforeach;
    if ($i % 3 != 0) :
    echo '</div>';
endif;
if ($i != 0) :
echo '</div>';
endif;
endif;
$i = 0;
switch($type) {
case 'admin1s':
$type = 'admin1';
break;
case 'countries':
$type = 'country';
break;
default:
$type = '';
}
foreach ($locations as $location) :
$class = 'c33l';
if ($i % 3 == 0) {
echo '<div class="subcolumns row">';
    }
    if ($i % 3 == 2) {
    $class = 'c33r';
    }
    echo '<div class="' . $class . '">';
        if (empty($type)) :
        echo '<span id="geoname' . $location->geonameid . '"><input type="submit" id="geonameid-' . $location->geonameid . '" name="geonameid-' . $location->geonameid . '" value="' . htmlentities($location->name, ENT_COMPAT, 'utf-8') . '" /><br />'
                . ((isset($location->admin1)) ? htmlentities($location->admin1, ENT_COMPAT, 'utf-8') . ', ' : '') . htmlentities($location->country, ENT_COMPAT, 'utf-8') . ', ';
            if ($location->cnt == 0) :
                echo $words->get('SearchSuggestionsNoMembersFound');
            else :
                echo $words->get('SearchSuggestionsMembersFound', $location->cnt);
            endif;
            echo '</span>';
        else :
        if ($type == 'admin1') :
        $text = $location->admin1;

        else :
        $text = $location->country;
        endif;
        echo '<input type="submit" name="' . $type . '-' . htmlentities($text, ENT_COMPAT, 'utf-8') . '" value="' . htmlentities($text, ENT_COMPAT, 'utf-8') . '" />';
        endif;
        echo '</div>';
    if ($i % 3 == 2) :
    echo '</div>';
endif;
$i++;
endforeach;
if ($i % 3 != 0) :
echo '</div>' . "\n";
endif;
