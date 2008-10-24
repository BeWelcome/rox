<?php
$settings = parse_ini_file('settings.ini');
chdir('..');
$settings['MAIN_PATH'] = getcwd() . '/';
$constants = "<?php\n";
foreach($settings as $key => $value) {
  $constants .= "define('$key', '$value');\n";
}
$constants .= "define('LIB_PATH', MAIN_PATH . 'lib/');\n";
$constants .= "define('CLIENT_PATH', MAIN_PATH . 'client/');\n";
$constants .= "define('IMAGE_LOCATION', ROOT_LOCATION . 'images/');\n";


include_once $settings['MAIN_PATH'].'client/facebook.php';
include_once $settings['MAIN_PATH'].'lib/display.php';
$fb = new Facebook($settings['API_KEY'], $settings['SECRET_KEY']);
define('ROOT_LOCATION', $settings['ROOT_LOCATION']);
define('APP_SUFFIX', $settings['APP_SUFFIX']);

$data = array('application_name'   => 'Smiley',
              'callback_url'       => ROOT_LOCATION,

              // The url for the app tab relative to callback url
              'tab_default_name'   => 'Smile',
              'profile_tab_url'    => 'mysmilies.php',

              // Publisher for other users profiles
              'publish_action'     => 'Smile at!',
              'publish_url'        => ROOT_LOCATION . '/handlers/otherPublishHandler.php',
              // Publisher for you own profile
              'publish_self_action'=> 'Smile!',
              'publish_self_url'   => ROOT_LOCATION . '/handlers/publishHandler.php',

              // Info changed callback url
              'info_changed_url'   => ROOT_LOCATION . '/handlers/infoHandler.php',

              // Wide canvas
              'wide_mode'          => true
              );

$fb->api_client->admin_setAppProperties($data);

// Set feed template 1 (for me)
$one_line_story = array('{*actor*} is feeling {*mood*} today');
$short_story = array(array('template_title'   => '{*actor*} is feeling so {*mood*} today',
                      'template_body'    => '{*actor*} just wanted to let you know that he is so {*mood*} today',
                           'preferred_layout' => 1));

$full_story = array('template_title' => '{*actor*} is feeling very {*mood*} today',
                     'template_body'  => '<div style="padding: 10px;width : 200px;height : 200px;margin: auto;text-align: center;border: black 1px;cursor: pointer;border: black solid 2px;background: orange;color: black;text-decoration: none;"><div style="font-size: 60pt;font-weight: bold;padding: 40px;">{*emote*}</div><div style="font-size: 20px; font-weight:bold;">{*mood*}</div></div>');


$res = $fb->api_client->feed_registerTemplateBundle($one_line_story, $short_story, $full_story);
$constants .= "define('FEED_STORY_1', '$res');\n";

// Set feed template 2 (for them)
$one_line_story = array('{*actor*} just wanted to {*emote*} at {*target*} today');
$short_story = array(array('template_title'   => '{*actor*} just wanted to {*emote*} at {*target*} today',
                      'template_body'    => 'Always a great day to {*emoteaction*}',
                           'preferred_layout' => 1));

$full_story = array('template_title'   => '{*actor*} just wanted to {*emote*} at {*target*} today',
                      'template_body'    => 'Always a great day to {*emoteaction*}',
                      'preferred_layout' => 1);


$res = $fb->api_client->feed_registerTemplateBundle($one_line_story, $short_story, $full_story);
$constants .= "define('FEED_STORY_2', '$res');\n";


// Set info options
$options  = array(array('label'=> 'Happy', 'image' => 'http://fbplatform.mancrushonmcslee.com/images/smile0.jpg', 'sublabel'=>'','description'=>'The original and still undefeated.', 'link'=>'http://apps.facebook.com/'.APP_SUFFIX.'/smile.php?smile=1'), array('label'=>'Indifferent', 'image'=> 'http://fbplatform.mancrushonmcslee.com/images/smile1.jpg', 'description'=>'meh...', 'link'=>'http://www.facebook.com'), array('label'=>'Sad', 'image'=> 'http://fbplatform.mancrushonmcslee.com/images/smile2.jpg', 'description'=>'Oh my god! you killed my dog!', 'link'=>'http://www.facebook.com'), array('label'=>'Cool', 'image'=> 'http://fbplatform.mancrushonmcslee.com/images/smile3.jpg', 'link'=>'http://www.facebook.com','description'=>'Yeah. whatever'));

$res = $fb->api_client->profile_setInfoOptions($options, 'Good Smilies');

$file = fopen('constants.php', 'w');
fwrite($file, $constants);
