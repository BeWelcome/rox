<?php

  /*
   * mysmilies.php - Show the user's moods
   *
   */

include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$fb = get_fb();
$user = $fb->require_login();

// The list of possible moods
$moods = get_moods();

// Your past moods are stored in a preference for simplicity
try {
  $mood_list = $fb->api_client->data_getUserPreference(0);
} catch (Exception $e) {
  // prevent fatal
  }

$is_tab = isset($_POST['fb_sig_in_profile_tab']);

// This can be viewed as an app page or a tabs
if (!$is_tab) {
  echo render_header('Mine');
 } else {
  echo render_inline_style();
 }


echo '<div style="text-align: center">';
$pos_name = '<fb:name firstnameonly="true" useyou="false" possessive="true" linked="false" uid="'.$user.'"/>';
echo '<h2>'
   .   $pos_name.' Smilies'
   . '</h2>';

$user_name = '<fb:name useyou="false" uid="'.$fb->user.'"/>';
echo '<h3 style="padding: 7px 0px">'
   . 'We are pleased to announce that ' . $user_name
   . ' has been feeling:'
   . '</h3>';
echo '<div style="overflow:hidden"><div class="past">';

$n = max(3,count($mood_list));
for ($i =0; $i< $n; $i++) {
  $v = intval($mood_list[$i]);
  $mood = $moods[$v];
  echo '<a class="box" href="smile.php?smile='.$v.'"><div class="smiley">'
     .    $mood[1]
     .  '</div><div>'
     .    $mood[0]
     .  '</div></a>';
}
echo '</div></div>';
if ($is_tab) {
  echo '<br><a href="http://apps.facebook.com/'.APP_SUFFIX.'" >Check out Smiley</a>';
  echo '<fb:visible-to-friends uid="'.$fb->user.'"><br><a href="http://apps.facebook.com/'.APP_SUFFIX.'sendSmiley.php?to_user='.$fb->user.'" >Send <fb:name linked="false" useyou="0" firstnameonly="1" uid="'.$fb->user.'"/> a smiley!</a></fb:visible-to-friends>';
}
echo '</div>';


