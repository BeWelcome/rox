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
    $select = '<strong class="small">' . $words->getFormatted('Gender') . '</strong><br/>';
    $select .= '<select name="search-gender">';
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
    $groups = array();
    $words = new MOD_words();
    $model = new RoxModelBase();
    $member = $model->getLoggedInMember();
    if ($member) {
        $groups = $member->getGroups();
    }
    $select = '<strong class="small">' . $words->getFormatted('Groups') . '</strong><br/>';
    $select .= '<select name="search-groups[]" class="multiselect sval" ';
    if (!$member) {
        $select .= 'disabled="disabled" ';
    }
    $select .= 'multiple="multiple">';
    foreach($groups as $group) {
        $select .= '<option value="' . $group->id . '"';
        if (in_array($group->id, $vars['search-groups']) === true) {
            $select .= ' selected="selected"';
        }
        $select .= '>' . $group->Name . '</option>';
    }
    $select .= '</select>' . $words->flushBuffer();
    return $select;
}

function getLanguagesOptionsDropDown($vars) {
    $words = new MOD_words();
    $languages = array();
    $model = new RoxModelBase();
    $member = $model->getLoggedInMember();
    if ($member) {
        $languages = $member->get_languages_spoken();
    }
    $select = '<strong class="small">' . $words->getFormatted('SearchLanguages') . '</strong><br/>';
    $select .= '<select name="search-languages[]" multiple="multiple" ';
    if (!$member) {
        $select .= 'disabled="disabled" ';
    }
    $select .= 'class="multiselect sval">';
    foreach($languages as $language) {
        $select .= '<option value="' . $language->IdLanguage . '"';
        if (in_array($language->IdLanguage, $vars['search-languages']) === true) {
            $select .= ' selected="selected"';
        }
        $select .= '>' . $language->Name . '</option>';
    }
    $select .= '</select>' . $words->flushBuffer();
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
    $options .= 'class="sval"/>&nbsp;
        <label for="search-accommodation-anytime">' . $words->get('Accomodation_anytime') . '</label><br/>
        <input type="checkbox" name="search-accommodation[]" id="search-accommodation-dependonrequest" value="dependonrequest"';
    if (in_array("dependonrequest", $accommodation) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>&nbsp;
        <label for="search-accommodation-dependonrequest">' . $words->get('Accomodation_dependonrequest') . '</label><br/>
        <input type="checkbox" name="search-accommodation[]" id="search-accommodation-neverask" value="neverask"';
    if (in_array("neverask", $accommodation) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>&nbsp;
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
    $options .= 'class="sval"/>&nbsp;
        <label for="search-typical-guidedtour">' . $words->get('TypicOffer_guidedtour') . '</label><br/>
        <input type="checkbox" name="search-typical-offer[]" id="search-typical-dinner" value="dinner"';
    if (in_array("dinner", $typicalOffers) === true) {
        $options .= ' checked="checked" ';
    }
    $options .= 'class="sval"/>&nbsp;
        <label for="search-typical-dinner">' . $words->get('TypicOffer_dinner') . '</label><br/>';
    return $options;
}

function getMembershipCheckbox($vars) {
    $words = new MOD_words();
    $memberCheckbox = '<strong class="small">' . $words->getFormatted('FindPeopleMemberStatus') . '</strong><br/>';
    $memberCheckbox .= '<input type="checkbox" id="search-membership" name="search-membership" value="1"';
    if (isset($vars['search-membership']) && ($vars['search-membership'] == 1)) {
        $memberCheckbox .= ' checked="checked"';
    }
    $memberCheckbox .= ' />&nbsp;<label for="search-membership">' . $words->get('SearchIncludeInactive') . '</label>';

    return $memberCheckbox;
}
?>