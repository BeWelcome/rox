<?php
// Use autoloading of helper file

include 'locationrow.helper.php';

function _getCountDropdown($value, $placeholder) {
    $select = '
        <select class="select2" name="trip-count" data-placeholder="' . $placeholder . '">
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

function _getAdditionalInfoDropdown($current, $words) {
    $options = TripsModel::getAdditonalInfoOptions();

    $select = '<select class="select2" name="trip-additional-info" data-placeholder="'
        . $words->getBuffered('TripCountPlaceholder') . '">';
    $select .= '<option label="empty"></option>';
   foreach($options as $value => $optionText) {
        $select .= '<option value="' . $value . '"';
        if ($current && $current == $value) {
            $select .= ' selected';
        }
        $select .= '>' . $optionText . '</option>';
    }
    $select .= '</select>';
    return $select;
}