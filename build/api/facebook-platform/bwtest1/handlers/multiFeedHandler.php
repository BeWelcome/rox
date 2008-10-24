<?php
  /*
   * multiFeedHandler.php - Posting to other's feed form handler
   *
   */

include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';
include_once LIB_PATH.'feed.php';

$picked = $_POST['picked'];
$fb     = get_fb();
$moods  = get_other_moods();
$canvas_url = $fb->get_facebook_url('apps') . '/' . APP_SUFFIX;
if ($picked != -1) {
  $feed = array('template_id' =>  FEED_STORY_2,
                'template_data' => array('emote'       => $moods[$picked][1],
                                         'emoteaction' => $moods[$picked][0]));

  $data = array('method'=> 'multiFeedStory',
                'content' => array( 'feed'    => $feed,
                                    'next'    => $canvas_url
                                    ));

} else {
  $data = array('errorCode'=> FACEBOOK_API_VALIDATION_ERROR,
              'errorTitle'=> 'No mood selected',
              'errorMessage'=>'Please select a smiley.');
}

echo json_encode($data);
