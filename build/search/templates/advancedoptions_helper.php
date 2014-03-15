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

function getAccommodationOptions($vars) {
    $words = new MOD_words();
    $accommodation = array();
    if (isset($vars['search-accommodation']) && is_array($vars['search-accommodation'])) {
        $accommodation = $vars['search-accommodation'];
    }
    $options = '<input type="checkbox" name="search-accommodation[]" id="search-accommodation-anytime" value="anytime"';
    if (in_array("anytime", $accommodation) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>
        <label for="search-accommodation-anytime">' . $words->get('Accomodation_anytime') . '</label><br/>
        <input type="checkbox" name="search-accommodation[]" id="search-accommodation-dependonrequest" value="dependonrequest"';
    if (in_array("dependonrequest", $accommodation) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>
        <label for="search-accommodation-dependonrequest">' . $words->get('Accomodation_dependonrequest') . '</label><br/>
        <input type="checkbox" name="search-accommodation[]" id="search-accommodation-neverask" value="neverask"';
    if (in_array("neverask", $accommodation) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>
        <label for="search-accommodation-neverask">' . $words->get('Accomodation_neverask') . '</label><br/>';
    return $options;
}

function getTypicalOfferOptions($vars) {
    $words = new MOD_words();
    $typicalOffers = array();
    if (isset($vars['search-typical-offer']) && is_array($vars['search-typical-offer'])) {
        $typicalOffers = $vars['search-typical-offer'];
    }
    $options = '<input type="checkbox" name="search-typical-offer[]" id="search-typical-guidedtour" value="guidedtour"';
    if (in_array("guidedtour", $typicalOffers) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>
        <label for="search-typical-guidedtour">' . $words->get('TypicOffer_guidedtour') . '</label><br/>
        <input type="checkbox" name="search-typical-offer[]" id="search-typical-dinner" value="dinner"';
    if (in_array("dinner", $typicalOffers) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>
        <label for="search-typical-dinner">' . $words->get('TypicOffer_dinner') . '</label><br/>';
    return $options;
}

function getMemberOptions($vars) {
    $words = new MOD_words();
    $select = '<select name="search-membership">
                <option value="0"';
    if ($vars['search-membership'] == 0) {
        $select .= ' selected="selected"';
    }
    $select .= '>' . $words->getBuffered('Active') . '</option>
        <option value="1"';
    if ($vars['search-membership'] == 1) {
        $select .= ' selected="selected"';
    }
    $select .= '>' . $words->getBuffered('All') . '</option>
        </select>' . $words->flushBuffer();
    return $select;
}
?>