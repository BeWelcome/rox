<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 19.01.14
 * Time: 18:34
 */
function getAgeDropDown($vars, $name) {
    $select = '<select name="' . $name . '">';
    $select .= '<option value="0"></option>';
    foreach (range(18, 120, 2) as $age) {
        $select .= '<option value="' . $age . '"';
        if ($age == $vars[$name]) {
            $select .= ' selected="selected" ';
        }
        $select .= '>' . $age . '</option>';
    }
    $select .= '</select>';
    return $select;
}

function getGenderDropDown($vars) {
    $words = new MOD_words();
    $select = '<select name="search-gender">';
    $select .= '<option value="0"></option>
                <option value="male"';
    if ($vars['search-gender'] === 'male') {
        $select .= ' selected="selected"';
    }
    $select .= '>' . $words->getBuffered('Male') . '</option>';
    $select .= '<option value="female"';
    if ($vars['search-gender'] === 'female') {
        $select .= ' selected="selected"';
    }
    $select .= '>' . $words->getBuffered('Female') . '</option>';
    $select .= '<option value="genderOther"';
    if ($vars['search-gender'] === 'genderOther') {
        $select .= ' selected="selected"';
    }
    $select .= '>' . $words->getBuffered('genderOther') . '</option>
        </select>' . $words->flushBuffer();
    return $select;
}

function getGroupOptionsDropDown($vars) {
    $words = new MOD_words();
    $select = '<select name="search-groups" class="sval">';
    $select .= '<option value="0" ';
    if ($vars['search-groups'] == 0) {
        $select .= 'selected="selected"';
    }
    $select .= '>' . $words->getBuffered('SearchAllGroups');
    $select .= '</option>
        <option value="1" ';
    if ($vars['search-groups'] == 1) {
        $select .= 'selected="selected"';
    }
    $select .= '>' . $words->getBuffered('SearchMyGroups') . '</option>
        </select>' . $words->flushBuffer();
    return $select;
}

function getLanguagesOptionsDropDown($vars) {
    $words = new MOD_words();
    $select = '<select name="search-languages" class="sval">';
    $select .= '<option value="0" ';
    if ($vars['search-languages'] == 0) {
        $select .= 'selected="selected"';
    }
    $select .= '>' . $words->getBuffered('SearchAllLanguages');
    $select .= '</option>
        <option value="1" ';
    if ($vars['search-languages'] == 1) {
        $select .= 'selected="selected"';
    }
    $select .= '>' . $words->getBuffered('SearchMyLanguages') . '</option>
        </select>' . $words->flushBuffer();
    return $select;
}
?>