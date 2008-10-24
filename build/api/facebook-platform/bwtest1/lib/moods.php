<?php
error_log(CLIENT_PATH);
include_once CLIENT_PATH.'facebook.php';

function get_fb() {
  return new Facebook(API_KEY,
                      SECRET_KEY);
}

function get_moods() {
  return array(array('Happy',        ':)'),
               array('Indifferent' , ':|'),
               array('Sad'         , ':('),
               array('Cool'        , 'B-|'),
               array('Emo'         , '//.-'),
               array('Surprised'   , '=O'),
               array('Laughing'    , 'XD'),
               array('Vampire'     , ': ['),
               array('Evil'        , '>:|')
               );
}

function get_other_moods() {
  return array(array('Kiss',        ':-*'),
               array('Wink',        ';]'),
               array('Confused',    ':@'),
               array('Tear',        ':\'-)'),
               array('Tongue',      ':P'),
               array('Bite',        ':-K'),
               );
}

/**
 * some info data to play with.
 * Schema is
 struct info_item {
  string label,
  string sublabel,
  string description,
  string link,
  string image
 }

struct info_field {
  string field,
  list<info_item> items
}
 */


function get_sample_info() {
  return array(
                      array('field' => 'Good Smilies',
                            'items' =>
                            array(array('label'=> 'Happy',
                                        'image' => IMAGE_LOCATION . 'smile0.jpg',
                                        'sublabel'=>'',
                                        'description'=>'The original and still undefeated.',
                                        'link'=>'http://www.facebook.com'),
                                  array('label'=>'Indifferent',
                                        'image'=> IMAGE_LOCATION . 'smile1.jpg', 'description'=>'meh...',
                                        'link'=>'http://www.facebook.com'),
                                  array('label'=>'Sad',
                                        'image'=> IMAGE_LOCATION . 'smile2.jpg',
                                        'description'=>'Oh my god! you killed my dog!',
                                        'link'=>'http://www.facebook.com'),
                                  array('label'=>'Cool',
                                        'image'=> IMAGE_LOCATION . 'smile3.jpg',
                                        'link'=>'http://www.facebook.com',
                                        'description'=>'Yeah. whatever'))),
                      array('field'=> 'Bad',
                            'items'=>
                            array(array('label'=> 'Evil',
                                        'link'=>'http://www.evil.com'))));
}

function get_user_profile_box($mood) {
  return  '<style>

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
  .smile {
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
  margin-bottom: 20px;
  }

  </style>
  <h2>We are pleased to announce that <fb:name useyou="false" uid="profileowner" /> is feeling:</h2>
  <div class="smile"><div class="smiley">'.$mood[1].'</div><div >'.$mood[0].'</div></div>
  <br /><p><a href="http://apps.facebook.com/mysmiley/" requirelogin=1>Visit Smiley</a></p>';

}
