<?php

function rightSelect($rights, $currentRightId = false) {
    $select = '<select id="right" name="right">';
    $select .= '<option value="0"></option>';
    foreach($rights as $rightId => $rightDetails) {
        $select .= '<option value="' . $rightId . '"';
        if ($currentRightId && $currentRightId == $rightId) {
            $select .= ' selected="selected"';
        }
        $select .= '>' . htmlentities($rights[$rightId]->Name, ENT_COMPAT, 'utf-8') . '</option>';
    }
    $select .= '</select>';
    return $select;
}