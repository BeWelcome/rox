<?php

function memberSelect($members, $currentMember) {
    $select = '<select id="member" name="member" class="select2 form-control-sm mr-2">';
    $select .= '<option value="0"></option>';
    foreach($members as $username => $member) {
        $select .= '<option value="' . $member->id . '"';
        if ($currentMember && $currentMember == $member->id ) {
            $select .= ' selected="selected"';
        }
        $select .= '>' . htmlentities($username, ENT_COMPAT, 'utf-8') . '</option>';
    }
    $select .= '</select>';
    return $select;
}
