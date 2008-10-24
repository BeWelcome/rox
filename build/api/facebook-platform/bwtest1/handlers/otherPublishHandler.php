<?php

/*
 * otherPublishHandler.php -  Handler for the publish interface on friends feed
 *
 */
include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$moods = get_other_moods();

if ($_POST['method']=='publisher_getFeedStory') {
  $picked = $_POST['app_params']['picked'];

  $image = IMAGE_LOCATION . '/smile'.$picked.'.jpg';

  $images = array(array('src'   => $image,
                        'href'  => 'http://apps.facebook.com/'.APP_SUFFIX));

  $feed = array('template_id'   => FEED_STORY_2,
                'template_data' => array('emote' => $moods[$picked][1],
                                         'emoteaction' => $moods[$picked][0]
                                         )
                );

  // The response to publisher_getFeedStory
  $data = array('method'=> 'publisher_getFeedStory',
                'content' => array( 'feed'    => $feed));

} else {
  array_pop($moods);

  $ret = render_handler_css();
  $ret .= render_handler_js();

  $ret .=
    '<form >'
     . render_emoticon_grid($moods)
     .'<input type="hidden" id="picked" name="picked" value="-1">'
    .'</form>';

  // The reponse to publisher_getInterface
  $data = array('content'=>array('fbml' => $ret,
                                 'publishEnabled' => false,
                                 'commentEnabled' => false),
                'method' => 'publisher_getInterface');

}

echo json_encode($data);
