<?php
// Use autoloading of helper file

include 'locationrow.helper.php';

function _getCountDropdown($current, $placeholder) {
    $options = array (
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        10 => '&gt; 5',
    );
    $select = '
        <select class="select2-allow-clear" name="trip-count" data-placeholder="' . $placeholder . '">
            <option label="empty"></option>';
    foreach($options as $value => $optionText) {
        $select .= '<option value="' . $value . '"';
        if (($current && $value) == $value) {
            $select .= ' selected';
        }
        $select .= '>' . $optionText . '</option>';
    };
    $select .= '</select>';
    return $select;
}

function _getAdditionalInfoDropdown($current, $words) {
    $options = TripsModel::getAdditonalInfoOptions();

    $select = '<select class="select2-allow-clear" name="trip-additional-info" data-placeholder="'
        . $words->getBuffered('TripCountPlaceholder') . '">';
    $select .= '<option label="empty"></option>';
    foreach($options as $value => $optionText) {
        $select .= '<option value="' . $value . '"';
        if (($current && $value) == $value) {
            $select .= ' selected';
        }
        $select .= '>' . $optionText . '</option>';
    }
    $select .= '</select>';
    return $select;
}