<?php

function memberSelect($members, $currentMember) {
    $select = '<select id="member" name="member">';
    $select .= '<option value="0"></option>';
    foreach($members as $username => $member) {
        $select .= '<option value="' . $username . '"';
        if ($currentMember && $currentMember->id == $member->id ) {
            $select .= ' selected="selected"';
        }
        $select .= '>' . htmlentities($username, ENT_COMPAT, 'utf-8') . '</option>';
    }
    $select .= '</select>';
    return $select;
}