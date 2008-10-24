<?php
include_once 'constants.php';
function create_feed_story($user, $picked, $method='feedStory') {
  $moods = get_moods();
  $content = '<style>.box2 {
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

.box2 .smiley {
  font-size: 35pt;
  font-weight: bold;
  padding: 20px;
}
</style>
<div class="box2"><div class="smiley">'.$moods[$picked][1].'</div><div >'.$moods[$picked][0].'</div></div>';


  $image = IMAGE_LOCATION . '/smile'.$picked.'.jpg';
  $images = array(array('src' => $image, 'href' => $image));
  $feed = array('template_id' => 14040772983,
                'template_data'      => array('mood' => $moods[$picked][1],
                                              'images' => $images)
                );


  $data = array('method'=> $method,
                'content' => array( 'feed'    => $feed));

  return json_encode($data);
}
