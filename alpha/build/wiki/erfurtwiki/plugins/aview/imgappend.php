<?php

/*
   This plugin provides a below-every-page image uploading function
   similar to the one of the edit page. The advantage is faster access
   and that it is working without JavaScript.
*/


$ewiki_plugins["view_append"][] = "ewiki_aview_image_append";
$ewiki_plugins["action"]["imageappend"] = "ewiki_action_image_append";


$ewiki_t["en"]["IMAGEAPPEND_FORM0"] = '<img src="imageappend.jpeg" width="80" height="60" alt="imageappend" align="left" /> <b>Append an Image</b><br />';
$ewiki_t["en"]["NO_IMAGEAPPEND"] = "The image uploading succeeded, but the page couldn't get updated. Please try the image upload function on EditThisPage.";




function ewiki_aview_image_append($id, $data, $action) {

   $URL = ewiki_script("imageappend", $id);
   $TXT = ewiki_t("IMAGEAPPEND_FORM0");
   $BTN1 = ewiki_t("UPLOAD_PICTURE_BUTTON");

   $accept = (defined("EWIKI_IMAGE_ACCEPT") ? ' accept="'.EWIKI_IMAGE_ACCEPT.'">' : "");
   return(<<<___
<div class="imageappend">
<form action="$URL" method="POST" enctype="multipart/form-data">
  $TXT
  <input type="file" name="imagefile" $accept><br />
  <input type="submit" value="$BTN1">
</form>
</div>
___
   );
}



function ewiki_action_image_append($id, $data, $action) {

   #-- invalid $id value
   if (empty($data) ||
       !$data["version"] ||
       (EWIKI_DB_F_TEXT != ($data["flags"] & EWIKI_DB_F_TYPE)))
   {
      $o = ewiki_t("CANNOTCHANGEPAGE");
   }

   #-- temporary upload-file found
   elseif ($fa = $_FILES["imagefile"]) {

      #-- guess HTTP meta data
      $meta = array(
         "X-Content-Type" => $fa["type"],
        #"X-Content-Length" => $fa["size"],
      );
      if ($s = $fa["name"]) {
         $meta["Content-Location"] = $s;
         ($p = 0) or
         ($p = strrpos($s, "/")) and ($p++) or
         ($p = strrpos($s, '\\')) and ($p++);
         $meta["Content-Disposition"] = 'inline; filename="'.urlencode(substr($s, $p)).'"';
      }

      #-- proceed an image (reject binary, resize if too large)
      $result = ewiki_binary_save_image(
          $fa["tmp_name"],	// uploaded file location
          "",			// no predefined $id
          "RETURN",		// do not die() on error
          $meta,		// =Content-Location
          0,			// =do not accept plain binary
          1			// =care for images
      );

      #-- database rejected file
      if (!$result) {
         $o = ewiki_t("BIN_NOIMG");
      }

      #-- if picture stored in db
      else {

         $loop = 3;
         while($loop--) {

            $data = ewiki_db::GET($id);

            $data["version"]++;
            $data["content"] = rtrim($data["content"], "\n") . "\n\n" .
                               "[\"AppendedPicture\"$result]\n\n\n";

            $result = ewiki_db::WRITE($data);

            if ($result) {
               break;
            }

         }

         if ($result) {
            $o = ewiki_page("view/$id");
            ewiki_log("image appended to '$id'");
         }
         else {
            $o .= ewiki_t("NO_IMAGEAPPEND");
         }

      }

   }

   #-- no upload-file
   else {
      $o .= ewiki_t("BIN_NOIMG");
#"You did not select an image, or something went really wrong during tansmission. Plase go back to the previous page.";
   }

   return($o);
}


?>