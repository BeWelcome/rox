<?php

/* 
    This plugin allows users to select a page image graphic.
    
*/
/* 
* @author andy fundinger <afundinger@burgiss.com> 
* @based on aview_piclogocntrl.php and page_imagegallery.php
*/

require_once('plugins/page/imagegallery.php');
define('EWIKI_PAGE_LOGOCNTRL_GALLERY','HeaderGallery');

$ewiki_plugins['edit_form_append'][] = 'ewiki_edit_form_append_pageimgcntrl';
$ewiki_plugins['edit_save'][]        = 'ewiki_edit_save_pageimgcntrl';
$ewiki_plugins['view_final'][]       = 'ewiki_add_title_image';

function ewiki_add_title_image(&$html, $id, &$data, $action) {
    if(!isset($data['meta']['pageimage']) || ($data['meta']['pageimage'] == '') ){
        return;
    }
    $imagename=$data['meta']['pageimage'];
    $image=ewiki_database('GET',$imagename);
    if((EWIKI_PROTECTED_MODE) &&!ewiki_auth($imagename, $image, "binary-get")){
        return;
    }
    
          #-- height, width
      $x = $image['meta']["width"];
      $y = $image['meta']["height"];
    
      #-- print a/img tag
      $o .= '<img src="' . ewiki_script_binary("", $imagename)
          . '" border="0" width="'.$x.'" height="'.$y.'"' 
          . ' />'. "\n";
          
    $html=preg_replace('/<h2 class="page title">/','<h2 class="page title">' . $o,$html,1);

}

/**
 * Save selected pageimage value by setting it in the meta field of save data array
 * passed by reference.
 * 
 * @param array save associative array of ewiki form data
 */
function ewiki_edit_save_pageimgcntrl(&$save){
    if (isset($_REQUEST['pageimagecntrl']) ) {
        if($_REQUEST['pageimagecntrl']==-1){
            unset($save['meta']['pageimage']);
        }else{
            $imageExist=ewiki_database('FIND',array($_REQUEST['pageimagecntrl']));
            //var_dump($imageExist);
            if($imageExist[$_REQUEST['pageimagecntrl']])
                $save['meta']['pageimage'] = $_REQUEST['pageimagecntrl'];
        }
    }
}

function ewiki_edit_form_append_pageimgcntrl ($id, $data, $action){
    global $ewiki_config;

   #-- fetch and asort images
   $sorted = array();
   $result = ewiki_db::GETALL(array("flags", "created", "meta"));
    while ($row = $result->get()) {    
        if ((($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_BINARY)
            && (strpos($row['meta']['Content-Type'], 'image/') === 0)) {
            if(isset($ewiki_config['image-galleries'][EWIKI_PAGE_LOGOCNTRL_GALLERY])){
                foreach($ewiki_config['image-galleries'][EWIKI_PAGE_LOGOCNTRL_GALLERY]as $field=>$value){
                    if($row['meta'][$field]!=$value){
                        continue (2);
                    }
                }
            } 
            if (!EWIKI_PROTECTED_MODE || (EWIKI_PROTECTED_MODE_HIDING <= .5)|| ewiki_auth($row["id"], $uu, "binary-get")) {
                $sorted[$row["id"]] = substr($row['meta']["Content-Location"].' ('.$row["id"].')',0,70);
                //echo("adding ".$row["id"].", ".$sorted[$row["id"]]."<br />");
            }           
        }
    }
   arsort($sorted);

   #-- start selector
    $o = '
        <br /><label for="pageimagecntrl">Choose Page Image (<A href="'.ewiki_script(EWIKI_PAGE_LOGOCNTRL_GALLERY).'">view thumbnails</A>):</label>
        <select id="pageimagecntrl" name="pageimagecntrl">'.
        '<option value="-1" '.(!isset($data['meta']['pageimage']) || $data['meta']['pageimage'] == '' ? ' selected="selected"' : '').'>None</option>'        ;
        
   foreach ($sorted as $image => $name) {   
        $o .= '<option value="'.htmlentities($image).'"'.
            (isset($data['meta']['pageimage']) && $image == $data['meta']['pageimage'] ? ' selected="selected"' : '').
            '>'.htmlentities($name).'</option>';
   }   
   $o .= "</select>\n";

   return($o);
}

?>