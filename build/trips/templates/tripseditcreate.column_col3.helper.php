<?php
// Use autoloading of helper file

include 'locationrow.helper.php';

function _getCountDropdown($value, $placeholder) {
    $select = '
        <select class="select2" id="trip-count" data-placeholder="' . $placeholder . '">
            <option label="empty"></option>
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
            <option>5</option>
            <option>&gt;5</option>
        </select>';
    if ($value != "") {
        str_replace('>' . $value, ' selected>' . $value, $select);
    }
    return $select;
}

function _getAdditionalInfoDropdown($value, $words) {
    $select = '
        <select class="select2" id="trip-additional-info" data-placeholder="' . $words->getBuffered('TripCountPlaceholder') . '">
            <option label="empty"></option>
            <option value="1">' . $words->getBuffered('TripSingleTraveller') . '</option>
            <option value="2">' . $words->getBuffered('TripCouple') . '</option>
            <option value="4">' . $words->getBuffered('TripFriendsMixedGender') . '</option>
            <option value="8">' . $words->getBuffered('TripFriendsSameGender') . '</option>
            <option value="16">' . $words->getBuffered('TripFamily') . '</option>
        </select>';
    if ($value != "") {
        str_replace('value="' . $value . '"', 'value="' . $value . '" selected', $select);
    }
    return $select;
}