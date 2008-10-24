<?php

include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

$picked = $_POST['picked'];
$moods  = get_moods();
$fb = get_fb();

$str = $fb->api_client->data_getUserPreference(0);
$mood = intval($str[0]);

$content = '<style>.box {
  padding: 10px;
  width : 100px;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 10px;
  margin-left: 10px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  margin-left: 32px;
  margin-top: 30px;
}
h2 {
text-align: center;
font-size: 11px;
color:#3B5998;

}

.smiley {
  font-size: 35pt;
  font-weight: bold;
  padding: 20px;
}
</style>
<h2>We are pleased to announce that <fb:name useyou="false" uid="'.$fb->user.'"/> is feeling:</h2>
<div class="box"><div class="smiley">'.$moods[$mood][1].'</div><div >'.$moods[$mood][0].'</div></div>';
$feed = array('fbml' =>  $content);

$data = array('method'=> 'profileBox',
                'content' => array( 'profilebox'    => $feed,
                                    'next'    => 'http://apps.srush2.devrs006.facebook.com/mysmiley/',
                                    ));

echo json_encode($data);
