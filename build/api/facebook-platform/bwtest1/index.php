<?php
/*
 * index.php - (and For setting app boxes)
 *
 */
include_once 'constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';


$fb = get_fb();
$user = $fb->require_login();

// You need to set info or profile box in order for the button's below to show up.
// Don't set them every time.
$is_set = $fb->api_client->data_getUserPreference(1);

if ($is_set != 'set') {
  // Setting info section for example
  // (Don't do this! Wait for user to add content)
  $info_fields = get_sample_info();
  $fb->api_client->profile_setInfo('My Smilies', 5, $info_fields, $user);

  // Setting info main profile box for example
  // (Don't do this! Wait for user to add content)
  $main_box =  get_user_profile_box(array('Happy', ':)'), $user);
  $fb->api_client->profile_setFBML(null, $user, null, null, null, $main_box);

 // Don't do this again
  $fb->api_client->data_setUserPreference(1, 'set');
}

echo render_header();

echo '<h2>Welcome to Smiley!</h2>';
echo '<p>Smiley is a sample app created to demonstrate the many '
    .'platform integration points of the Facebook profile.</p>';

// Profile box
echo 'Here is an button for adding a  box to your profile. This will go away if you add the box:';

echo '<div class="section_button"><fb:add-section-button section="profile"/></div>';

// Info section
echo 'Here is an button for adding an info section to your profile. This will go away if you add the section:';

echo '<div class="section_button"><fb:add-section-button section="info" /></div>';

// Permissions
echo 'These are FBML tags that can prompt users for extended permissions from the canvas page.<br />These will go away if you grant these permissions:<br />';
echo '<fb:prompt-permission perms="email">Enable Email</fb:prompt-permission>';
echo '<br />';
echo '<fb:prompt-permission perms="infinite_session">Enable Permanent Login</fb:prompt-permission>';

echo '<p>Upon submitting the form below, you will be prompted to grant email permissions (unless you\'ve already done so for this app):';
echo '<form promptpermission="email"><br />How often would you like to be notified of new smilies?<br /><input type="text" name="frequency"><input type="submit" value="Notify Me"></form></p>';
