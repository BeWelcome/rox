<?php
function _getOptionsDropdown($locationRow, $locationOptions, $words) {
    $select = '
        <select class="select2" name="location-options[]" id="trip-location-options-' . $locationRow .'" data-placeholder="' .
        $words->getBuffered('TripLocationOptionsPlaceholder') . '" multiple>
            <option label="empty"></option>
            <option value="1">' . $words->getBuffered('TripLikeToMeetup') .'</option>
            <option value="2">' . $words->getBuffered('TripLookingForAHost') .'</option>
        </select>';
    return $select;
}

