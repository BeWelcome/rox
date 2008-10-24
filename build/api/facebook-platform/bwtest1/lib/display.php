<?php


// increment these when you change css or js files
define('CSS_VERSION', '20');
define('JS_VERSION',  '28');

function render_bool($res) {
  if ($res) {
    return 'true';
  } else {
    return 'false';
  }
}


/**
 * Render the dashboard and header for a page
 *
 * @param  string $selected The tab that is currently selected
 * @return void        Or a type, with a description here
 * @author
 */

function render_header($selected ='Home') {
  $header = '<link rel="stylesheet" type="text/css" href="'.ROOT_LOCATION.'/css/page.css?id='.CSS_VERSION.'" />';
  $header .= '<script src="'.ROOT_LOCATION.'/js/base.js?id='.JS_VERSION.'" ></script>';

  $header .= '<fb:dashboard/>';

  $header .=
    '<fb:tabs>'
    .'<fb:tab-item title="Home"  href="index.php" '
      .'selected="' . ($selected == 'Home') .'" />'
    .'<fb:tab-item title="My Smilies"  href="mysmilies.php" selected="' . ($selected == 'Mine') . '" />'
    .'<fb:tab-item title="New Smiley"  href="newsmiley.php" selected="' . ($selected == 'New') . '" />'
    .'<fb:tab-item title="Send Smiley"  href="sendSmiley.php" selected="' . ($selected == 'Send') . '" />'
    .'<fb:tab-item title="Smiley IFrame"  href="iframe/index.php?fb_force_mode=iframe" selected="false" />'
    .'<fb:tab-item title="Smiley Flash"  href="flash/smiley_flash.php" selected="false" />'
    .'</fb:tabs>';
  $header .= '<div id="main_body">';
  return $header;
}

function render_footer() {
  $footer = '</div>';
  return $footer;

}

function render_inline_style() {
 return  '<style>
  h2 {
   font-size: 20pt;
   text-align: center;
  }

  .box {
  padding: 10px;
  width : 100px;
  height : 90px;
  display : block;
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
  h3 {
  text-align: center;
  font-size: 11px;
  color:#3B5998;

  }

  .big_box {
  padding: 10px;
  width : 300px;
  height : 300px;
  margin: auto;
  text-align: center;
  border: black 1px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  color: black;
  text-decoration: none;
  }
  a.box {
   color: black;
  }


  a:hover.box {
   text-decoration: none;
  }

  .smiley {
  font-size: 25pt;
  font-weight: bold;
  padding: 10px;
  color: black;
  text-decoration: none;
  }


  .big_smiley {
  font-size: 100pt;
  font-weight: bold;
  padding: 40px;
  }

.past {
 margin:auto;
 width: 500px;
}
</style>
';
}


function render_emoticon_grid($moods, $js="select(") {
  $ret = '';
  $i = 0;
  $ret.='<div class="table"><div class="row">';
  foreach($moods as $mood) {
    list($title,$smiley) = $mood;
    if ($i%3==0 && $i!=0) {
      $ret.='</div><div class="row">';
    }
    $ret .= '<div onclick="'.$js.'\''.$title.'\',\''.$smiley.'\','.$i.')" onmouseover="over('.$i.')" onmouseout="out('.$i.')" class="box" id="sm_'.$i.'"><div class="smiley">'.$smiley.'</div><div id="smt_'.$i.'" class="title">'.$title.'</div></div>';
    $i++;
  }
  $ret .= '</div></div>';
  return $ret;

}
function render_handler_css() {
  $css  = '<style>.box {

  height :70px;
  width : 70px;
  float : left;
  text-align: center;
  border: black 1px;
  margin-right: 10px;
  margin-left: 10px;
  cursor: pointer;
  border: black solid 2px;
  background: orange;
  margin-left: 32px;
  margin-top: 20px;
}
.smiley {
  font-size: 20pt;
  font-weight: bold;
  padding: 0px;
  padding-top: 20px;
}

.box_selected {
  border: 2px dashed black;
  background: #E1E1E1;
}

.title {
 padding-top: 10px;
 font-size: 10px;
 visibility: hidden;
}

.box_selected .title {
  visibility: visible;
}

.box_over .title {
 visibility: visible;
}

</style>';
  return $css;
}

function render_handler_js() {
  $code .= '
<script>
var cur_picked = -1;
function over(id) {
  document.getElementById("sm_"+id).addClassName("box_over");
}
function out(id) {
  document.getElementById("sm_"+id).removeClassName("box_over");
}

function select(title, mood, id, feed) {
  document.getElementById("sm_"+id).addClassName("box_selected");
  document.getElementById("picked").setValue(id);
  if (feed) {
    Facebook.showFeedDialog("http://www.srush3.devrs001.facebook.com/intern/howareyoufeeling/feedHandler.php", {"picked":id});
  } else {
    Facebook.setPublishStatus(true);
  }
}

function unselect(id) {
  document.getElementById("sm_"+id).removeClassName("box_selected");
}

function picked(i) {
  if (cur_picked!=-1) {
    unselect(cur_picked);
  }
  cur_picked = i;
  select(i);
}
</script>
';
  return $code;

}
