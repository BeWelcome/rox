<?php

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb = get_fb();
if (!$user = $fb->get_loggedin_user()) {
  $user = $fb->get_canvas_user();
 }

$prefill_user = null;
if (isset($_GET['to_user']) && ($_GET['to_user'] != $user)) {
  // you can't send a smiley to yourself
  $prefill_user = $_GET['to_user'];
 }

echo render_header('Send');

if (!$prefill_user) {
  $ret = '<h2>Send a friend a smiley</h2>';
  $ret .='<form fbtype="multiFeedStory" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
  $ret .= '<div class="input_row"> <fb:multi-friend-input /></div>';
  $ret .= render_emoticon_grid(get_other_moods());
  $ret .= '<input type="hidden" id="picked" name="picked" value="-1">'
    .'<div id="centerbutton" class="buttons"><input type="submit" id="mood" label="Send Smiley"></div>'
    .'<div id="emoticon"></div>'
    .'</form></div>';
 } else {
  $ret = '<h2>Send <fb:name uid="'.$prefill_user.'" firstnameonly=1 /> a smiley</h2>';
  $ret .='<form fbtype="multiFeedStory" action="'.ROOT_LOCATION.'/handlers/multiFeedHandler.php">';
  $ret .= render_emoticon_grid(get_other_moods());
  $ret .= '<input type="hidden" id="picked" name="picked" value="-1">'
    .'<div id="centerbutton" class="buttons"><input type="submit" fbuid="'.$prefill_user.'" id="mood" label="Send Smiley to %n"></div>'
    .'<div id="emoticon"></div>'
    .'</form></div>';
 }
echo $ret;
