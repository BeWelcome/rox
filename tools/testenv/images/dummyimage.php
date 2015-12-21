<?php
class DummyImage
{
    protected $blueprint;// Imageresource where physical images will be based on
    protected $size;     // Array containing metadata about original image
    protected $id;       // pictureid for this image
    protected $name;     // filename for this image
    protected $imgDir;   // directorypath for this image

    /**
     * Deterministic algorithm to get a main color and a related color based on id's
     *
     * Color is defined through modulus of a big number, which is the summation of
     * two power calculations
     *
     * @access protected
     * @param integer $divmod
     * @param integer $pow1 First powerfactor
     * @param integer $pow2 Second powerfactor
     * @param integer $add Value add to the groundnumber of the second powercalculation
     * @param integer $factor Relative difference between main and related colors
     * @return array Collection of both colors
     **/
    protected function getColor($divmod,$pow1,$pow2,$add,$factor = 1)
    {
        // define main color
        $div = bcadd(bcpow(256,3),bcmod($this->id,$divmod));
        $val = bcadd(bcpow($this->id,$pow1),bcpow($this->id + $add,$pow2));
        $mod = bcmod($val,$div);
        $c1 = floor($mod / pow(256,2));
        $c2 = floor($mod / 256 % 256);
        $c3 = $mod % 256;
        // get related color, based on the other color
        if (($c1+$c2+$c3)/3 < 128){
            $c4 = min(256,round($c1 * $factor));
            $c5 = min(256,round($c2 * $factor));
            $c6 = min(256,round($c3 * $factor));
        } else {
            $c4 = floor($c1 / $factor);
            $c5 = floor($c2 / $factor);
            $c6 = floor($c3 / $factor);
        }    
        return array(imagecolorallocate($this->blueprint,$c1,$c2,$c3),
                     imagecolorallocate($this->blueprint,$c4,$c5,$c6));
    }

    /**
     * Calculate dimensions of the image based on requirements
     *
     * @access protected
     * @param integer $max_x Maximum horizontal dimension of image
     * @param integer $max_y Maximum vertical dimension of image
     * @param string $mode Transformation mode
     * @param array $size Array with basic metadata of baseimage
     * @return array Collection of parameters for resizing
     **/
    protected function getThumbSize($max_x = false, $max_y = false, $mode = 'square',$size)
    {
        $size_x = $size[0];
        $size_y = $size[1];
        
        // old school
        if (!$max_x || !$max_y) {
            if ($max_x && intval($max_x) > 0 && $size_x) {
                $th_size_x = intval($max_x);
                $th_size_y = intval($size_y*$th_size_x/$size_x);
                $size_x = $th_size_x;
                $size_y = $th_size_y;
            }
            if ($max_y && intval($max_y) > 0 && $size_y > $max_y) {
                $th_size_y = intval($max_y);
                $th_size_x = intval($size_x*$th_size_y/$size_y);
            }
            $startx = 0;
            $starty = 0;
            $size_x = $size[0];
            $size_y = $size[1];
        } else {
            switch($mode){
                case "ratio":
                    if (($max_x / $size_x) >= ($max_y / $size_y)){
                        $ratio = $max_y / $size_y;
                    } else {
                        $ratio = $max_x / $size_x;
                    }
                    $startx = 0;
                    $starty = 0;
                    break;
                default:
                    if ($size_x >= $size_y){
                        $startx = ($size_x - $size_y) / 2;
                        $starty = 0;
                        $size_x = $size_y;
                    } else {
                        $starty = ($size_y - $size_x) / 2;
                        $startx = 0;
                        $size_y = $size_x;
                    }
    
                    if ($max_x >= $max_y){
                        $ratio = $max_y / $size_y;
                    } else {
                        $ratio = $max_x / $size_x;
                    }
                    break;
            }
            $th_size_x = $size_x * $ratio;
            $th_size_y = $size_y * $ratio;
        }
        return array(0, 0, $startx, $starty, $th_size_x, $th_size_y, $size_x, $size_y);
    }

    /**
     * Create the actual files for the images
     *
     * @access protected
     * @param string $imgDir
     * @param array $thumbData
     * @return integer Number of created files
     * 
     **/
    protected function createFiles($thumbData)
    {
        $imgCount = 0;
        echo "Processing image";
        foreach ($thumbData as $thname => $th){
            echo "... " . $thname;
            $newImage = ImageCreateTrueColor($th[4], $th[5]);
            $newFile = $this->imgDir . '/' . $this->getFileName($thname);
            imagecopyresized($newImage, $this->blueprint,$th[0], $th[1],
                             $th[2], $th[3], $th[4], $th[5], $th[6], $th[7]);
            switch ($this->size[2]) {
                case IMAGETYPE_GIF:
                case 'image/gif':
                    imagetruecolortopalette($newImage, true, 256);
                    imagegif($newImage, $newFile);
                    break;
                case IMAGETYPE_JPEG:
                case 'image/jpeg':
                    imagejpeg($newImage, $newFile);
                    break;
                case IMAGETYPE_PNG:
                case 'image/png':
                    imagepng($newImage, $newFile);
                    break;
            }
            if (is_readable($newFile)) {$imgCount++;}
        }
        if ($this->blueprint) {
            imagedestroy($this->blueprint);
        }
        echo " ...done" . PHP_EOL;
        return $imgCount;
    }
    
    /**
     * Get working directory for created images
     *
     * @access protected
     * @return string Directory path
     **/
    protected function getImageDir()
    {
        return STATIC::IMAGE_DIR;
    }

    /**
     * Set and create if needed, the working directory for created images
     *
     * @access protected
     **/
    protected function setImageDir()
    {
        $imgDir = STATIC::IMAGE_DIR;
        if (!is_dir($imgDir)) {
            mkdir($imgDir,'0777',true);
        }
        $this->imgDir = $imgDir;
    }
}
