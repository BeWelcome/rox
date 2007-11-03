<?php

# This plugin rescales uploaded images using ImageMagick(1), if an uploaded
# image file is larger than allowed in EWIKI_IMAGE_MAXSIZE.
#
# NOTE: ImageMagick can usually be found on UNIX sytems only, but you could
# of course utilize another commandline instead if your system provides a
# similar one.



$ewiki_plugins["image_resize"][] = "ewiki_binary_resize_image_magick";


function ewiki_binary_resize_image_magick(&$filename, &$type, $return=0) {

   if (!filesize($filename)) {
      return(false);
   }

	if(filesize($filename) < EWIKI_IMAGE_MAXSIZE){
		return(true);
	}

   #-- temporary image file
   $tmp_rescale = tempnam(EWIKI_TMP, "ewiki.img_resize_magick.tmp.");
   $tmp_size = filesize($filename);

   #-- initial rescale factor
   $scale = sqrt(EWIKI_IMAGE_MAXSIZE / ($tmp_size + 1));

   #-- try to rescale image
   $loop=7;
   while ($loop && ($tmp_size > EWIKI_IMAGE_MAXSIZE)) {

      @unlink($tmp_rescale);
      copy($filename, $tmp_rescale);

      $n = round($scale * 100);
      exec("mogrify -scale $n%x$n% $tmp_rescale");

      clearstatcache();
      $scale = $scale * 0.95;
      $tmp_size = filesize($tmp_rescale);
   }


   #-- return result
   if ((filesize($tmp_rescale)) &&
       (filesize($tmp_rescale) < filesize($filename)) &&
       (filesize($tmp_rescale) < EWIKI_IMAGE_MAXSIZE))
   {
      @unlink($filename);
      $filename = $tmp_rescale;
      return($true);
   }
   else {
      @unlink($tmp_rescale);
      return($false);
   }

}



?>