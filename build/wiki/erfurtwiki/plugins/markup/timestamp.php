<?php

/*
    Description:
    This function replaces &now in text with a timestamp
    as long as it is surrounded by any form of whitespace
    Written by Alfred Sterphone, III
    Started - 5/24
    Last Updated - 5/27
*/

$ewiki_plugins["edit_save"][] = "ewiki_edit_save_timestamp";

function ewiki_edit_save_timestamp(&$save)
{
    $save['content'] = replaceAmpNow($save['content'], time());
}

function replaceAmpNow($a_input, $a_timestamp)
{
    $pattern = "/(^|[\s])(&now)($|[\s])/i";
    $dateFormat = "l, F dS, Y h:i A";

    $retval = preg_replace($pattern,
            "\$1''".date($dateFormat, $a_timestamp)."''\$3",
            $a_input);
            
    return $retval;
}

?>