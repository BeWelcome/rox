<?php
include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$smile = $_GET['smile'];
$moods = get_moods();

echo render_inline_style();
echo '<div class="big_box"><div class="big_smiley">'
     .    $moods[$smile][1]
     .  '</div><div>'
     .    $moods[$smile][0]
     .  '</div></div>';

