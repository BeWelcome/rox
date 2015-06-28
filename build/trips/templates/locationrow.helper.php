<?php
function _getOptionsDropdown($locationRow, $current, $words) {
    $options = TripsModel::getLocationOptions();
    $select = '
        <select class="select2" name="location-options[]" id="trip-location-options-' . $locationRow .'" data-placeholder="' .
        $words->getBuffered('TripLocationOptionsPlaceholder') . '" multiple>
            <option label="empty"></option>';
    foreach($options as $value => $optionText) {
        $select .= '<option value="' . $value . '"';
        if (($current & $value) == $value) {
            $select .= ' selected';
        }
        $select .= '>' . $optionText . '</option>';
    }
    $select .= '</select>';
    return $select;
}

