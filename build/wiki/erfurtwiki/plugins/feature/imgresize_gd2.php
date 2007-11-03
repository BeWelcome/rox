<?php

#  if someone uploads an image, which is larger than the allowed
#  image size (EWIKI_IMAGE_MAXSIZE), then this plugin tries to
#  rescale that image until it fits; it utilizes the PHP libgd
#  functions to accomplish this

#  NOTE: It is currently disabled for Win32, because nobody knows, if
#  this will crash the PHP interpreter on those systems.

#  Changed on 6/8/04 by Alfred Sterphone, III
#  Types supported: gif, png, jpeg
#  All that are resized are converted to jpeg in the end
#  WBMP stands for wireless bitmap and not windows bitmap
#  Bitmap support for only 25KB files is folly anyway

#  Revamped again, later the same day (6/8/04) by me again
#  Implemented a binary search for the correct pixel width
#  Next step is to put PHPUnit around and test all cases

#  Refactoring and bullet-proofing completed (6/15/04) by me again

/*
Design Requirements
- Handled formats: jpeg, gif, png
- Images wider than 90% of our page width will be resized proportionally to 90% of page width at upload
- Images uploaded as images will be restricted to 25 kilobytes
- Images larger than this will:
-# Be scaled according to our page width
-# Be converted to JPEG
-# Be re-compressed as JPEG with compression settings that suits us
-# Rescaled via binary search for suitable size down to 20% of page width
-# Rejected as too large
- Image format may be changed in scaling
- Images uploaded as attachments will not be scaled in this manner


*/

define("EWIKI_IMGRESIZE_WIN", 0);
define("EWIKI_IMAGE_MAX_PIXELS", 11000000); //11 megapixels
define("EWIKI_IMAGE_MAXSIZE", 65536);
define("EWIKI_IMAGE_TOLERANCE", 60000); //don't settle for smaller than this during resize
define("EWIKI_WORK_AREA", 640); //width of whitespace or work area within ewiki
define("EWIKI_IMAGE_MAX_X", (int)(.9*EWIKI_WORK_AREA)); //90% max
define("EWIKI_IMAGE_MIN_X", (int)(.2*EWIKI_WORK_AREA)); //20% min
define("EWIKI_IMAGE_MAX_Y",1000); //max y value if picture is "candy cane"
define("EWIKI_IMAGE_RATIO", 10); //maximum acceptable y:x ratio

if (!strstr(PHP_VERSION, "-dev") && !(extension_loaded("php_gd2.dll") or extension_loaded("gd.so")) && !function_exists("imagecreate") && function_exists("dl"))
{   #-- try to load gd lib
    @dl("php_gd2.dll") or @dl("gd.so");
}

if (function_exists("imagecreate"))
{
    $ewiki_plugins["image_resize"][] = "ewiki_binary_resize_image_gd";
}

function ewiki_binary_resize_image_gd(&$filename, &$mime, $return=0)
{
    return resizeImage($filename, $mime, $return);
}

function getTypeFromMIME($mime)
{
    #-- read orig image
    strtok($mime, "/");
    $type = strtok("/");
    return($type);
}

function getXY($orig_image,&$orig_x,&$orig_y)
{
    $orig_x = imagesx($orig_image);
    $orig_y = imagesy($orig_image);
}

function isResizeNeeded($a_width, $a_fileName)
{
    clearstatcache();
    
    if ($a_width > EWIKI_IMAGE_MAX_X)
    {
        return true;
    }
    
    if (filesize($a_fileName) > EWIKI_IMAGE_MAXSIZE)
    {
        return true;
    }
    
    return false;
}

function isImageTolerable($a_fileName)
{
    clearstatcache();
    
    if (filesize($a_fileName) > EWIKI_IMAGE_MAXSIZE)
    {
        return false;
    }
    
    if (filesize($a_fileName) < EWIKI_IMAGE_TOLERANCE)
    {
        return false;
    }
    
    return true;
}

function getImageStream($filename,$type)
{
    $retval=NULL;

    if ((($type != "gif") && ($type !="jpeg") && ($type !="png") && ($type != "vnd.wap.wbmp")))
    {
        return($retval);
    }
    
    if (!function_exists($pf = "imagecreatefrom$type"))
    {
        return($retval);
    }
    
    $retval = $pf($filename);
    
    return($retval);    
}

function getInitialResize($orig_image,&$new_x,&$new_y)
{
    getXY($orig_image,$orig_x,$orig_y);
    
    if ($orig_x <= EWIKI_IMAGE_MAX_X)
    //keep original dimesions
    {
        $new_x=$orig_x;
        $new_y=$orig_y;
    }
    else
    //wider than max width, so resize
    {
        $new_x=EWIKI_IMAGE_MAX_X;
        $new_y=(int)((EWIKI_IMAGE_MAX_X*$orig_y)/$orig_x);
    }
}

function doResize($orig_image,$new_x,$new_y,&$type)
{
    $tc = function_exists("imageistruecolor") && imageistruecolor($orig_image);
    if (!$tc || ($type == "gif"))
    {
        $new_image = imagecreate($new_x, $new_y);
        $white = imagecolorallocate($new_image,255,255,255);
        imagefill($new_image, 0, 0, $white);
        imagepalettecopy($new_image, $orig_image);
    }
    else
    {
        $new_image = imagecreatetruecolor($new_x, $new_y);
        $white = imagecolorallocate($new_image,255,255,255);
        imagefill($new_image, 0, 0, $white);
    }

    getXY($orig_image,$orig_x,$orig_y);
    
    #-- resize action
    imagecopyresampled($new_image, $orig_image, 0,0, 0,0, $new_x,$new_y, $orig_x,$orig_y);
    
    $type = "jpeg";
    
    return($new_image);
}

function doSave(&$image,$filename,$type)
{
    if (function_exists($pf = "image$type"))
    {
        $pf($image,$filename,70);
    }
    else
    {
        return(false);   # cannot save in orig format
    }
}

function isCandyCane($filename)
{
    clearstatcache();
    
    list($width, $height, $type, $attr) = getimagesize($filename);
    $ratio = ($height/$width);
    $fs = filesize($filename);
    
    if (($ratio > EWIKI_IMAGE_RATIO) && ($fs > EWIKI_IMAGE_MAXSIZE) && ($height > EWIKI_IMAGE_MAX_Y))
    {
        ewiki_log("$filename is a candy cane",3);
        return true;
    }
    return false;
}

function isMemoryFriendly($filename)
{
    list($width, $height, $type, $attr) = getimagesize($filename);
    $pixels = (int)($height*$width);
    
    if ($pixels > EWIKI_IMAGE_MAX_PIXELS)
    {
        ewiki_log("$filename at $pixels pixels is too big!",3);
        return false;
    }
    
    return true;
}

function resizeImage(&$filename, &$mime, $return=0)
{
    //start timing
    $time_start = getmicrotime();
    
    /*** this disallows Win32 ***/
    if ((DIRECTORY_SEPARATOR!="/") && !EWIKI_IMAGERESIZE_WIN || (strpos($mime, "image/")!==0))
    {
        return(false);
    }
    
    if (!isMemoryFriendly($filename))
    {
        return false;
    }

    if (isCandyCane($filename))
    {
        return false;
    }
        
    $rescaled_filename = $filename;

    $type = getTypeFromMIME($mime);

    $orig_image = getImageStream($rescaled_filename,$type);
    if (!isset($orig_image))
    {
        return(false);
    }
    
    getXY($orig_image,$orig_x,$orig_y);
    
    if (!isResizeNeeded($orig_x, $filename))
        return true;
    
    getInitialResize($orig_image,$new_x,$new_y);
    
    $orig_image = doResize($orig_image,$new_x,$new_y,$type);
    
    $rescaled_filename = tempnam(EWIKI_TMP, "ewiki.img_resize_gd.tmp.");
    doSave($orig_image,$rescaled_filename,$type);
    
    if (isResizeNeeded($new_x, $rescaled_filename))
    //will only take cases that need to be resized
    {
        ewiki_log("Resize beyond initial resize is needed.  Carrying through.",3);
        
        //set starting points for binary search
        $x_max=EWIKI_IMAGE_MAX_X-1;
        $x_min=EWIKI_IMAGE_MIN_X;
       
        //set failsafe break to max number of iterations through the loop
        $failsafe = (int)(log($orig_x)+1);
        
        while (($x_min <= $x_max) && !isImageTolerable($rescaled_filename))
        //the resize while loop
        {
            ewiki_log("While loop initiated",3);
            
            //somehow made it to an infinite loop, so get out
            if($failsafe < 0) return(false);
            
            //take a guess at the correct width
            $x_guess=(int)(($x_max+$x_min)/2);
            
            if ($filename == $rescaled_filename)
            {
                $rescaled_filename = tempnam(EWIKI_TMP, "ewiki.img_resize_gd.tmp.");
            }
            
            #-- sizes
            $new_x = (int)($x_guess);
            $new_y = (int)(($x_guess*$orig_y)/$orig_x);
            
            $new_image = doResize($orig_image,$new_x,$new_y,$type);
            
            doSave($new_image,$rescaled_filename,$type);
            
            #-- prepare next run
            imagedestroy($new_image);
            clearstatcache();
            
            $failsafe--;
            
            $ftmp = filesize($rescaled_filename);
            ewiki_log("xguess: $x_guess, xmin: $x_min, xmax: $x_max, filesize: $ftmp",3);
            
            if (filesize($rescaled_filename) < EWIKI_IMAGE_TOLERANCE)
            {
                $x_min=$x_guess+1;
            }
            else if (filesize($rescaled_filename) > EWIKI_IMAGE_MAXSIZE)
            {
                $x_max=$x_guess-1;
            }
        }
        
        ewiki_log("While loop ended",3);
        
    }
    
    #-- stop
    imagedestroy($orig_image);
    clearstatcache();
    
    #-- security check filesizes, abort
    if (!filesize($filename) || !filesize($rescaled_filename) || (filesize($rescaled_filename) > EWIKI_IMAGE_MAXSIZE))
    {
        unlink($rescaled_filename);
        return($false);
    }
    
    #-- set $mime, as it may have changed (.gif)
    $mime = strtok($mime, "/") . "/" . $type;
    if (!strstr($filename, ".$type"))
    {
        unlink($filename);
        $filename .= ".$type";
    }
    
    #-- move tmp file to old name
    copy($rescaled_filename, $filename);
    unlink($rescaled_filename);
    
    //end timing
    $time_end = getmicrotime();
    $time = $time_end - $time_start;
    ewiki_log("$time seconds to perform resizing", 3);
    
    return(true);
}
?>