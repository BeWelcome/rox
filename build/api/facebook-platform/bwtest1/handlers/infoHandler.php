<?php
include_once '../constants.php';
include_once LIB_PATH.'moods.php';
include_once LIB_PATH.'display.php';

// need stripslashes on posted json
$fields = json_decode(stripslashes($_POST['fields']), true);

$new_fields = array();
foreach ($fields as $field ) {
  $items = array();
  $label = $field['field'];
  $elements = trim($field['entered']);
  $split_elements = $elements?explode(',', $elements):array();
  foreach ($split_elements as $elem) {
    $elem = trim($elem);
    switch ($elem) {
    case 'Happy':
      $link = 'http://www.smile.com';
      break;
    case 'Sad':
      $link = 'http://www.sad.com';
      break;
    default:
      $link = 'http://www.blah.com/?'.$elem;
    }
    $items[] = array('label' => $elem,
                     'link'  => $link);
  }
  $new_fields[] = array('field' => $label,
                    'items' => $items);
}

$data = array('method'=> 'infoChanged',
              'content' => $new_fields);

echo json_encode($data);
