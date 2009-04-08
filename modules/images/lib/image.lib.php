<?php
/**
 * @author Philipp Hunstein & Seong-Min Kang <info@respice.de>
 * @version v2.0.0 Pre-Alpha
 */
class MOD_images_Image {
    protected $file;
    protected $imageSize;
    protected $hash;
    protected $mimetype;
    
    public function __construct($file) {
        if (file_exists($file) && is_file($file) && is_readable($file)) {
            $this->file = $file;
            $this->_loadImage();
        }
    }
    
    private function _loadImage() {
        if (!$this->file)
            return false;
        $is = @getimagesize($this->file);
        if (!is_array($is))
            return false;
        $this->imageSize = $is;
        $this->mimetype = $is['mime'];
        $this->hash = sha1_file($this->file);
        return true;
    }
    
    public function createThumb($dir, $prefix, $max_x = false, $max_y = false, $prefixIsRealName = false, $mode = 'square') {
        if (!isset ($this->hash))
            return FALSE;
        if (!$dir)
            $dir = dirname($this->file);
        if (!$prefix) {
            $prefix = 't';
        }

        if ((!$max_x && !$max_y) || (intval ($max_x) <= 0 && intval ($max_y) <= 0) )
            throw new PException('Neither thumbnail max-width nor max-height provided!');
        $size_x = $this->imageSize[0];
        $size_y = $this->imageSize[1];
        
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
        
        // if ($width && intval($width) > 0 && $oldWidth) {
            // $newWidth = intval($width);
            // $newHeight = intval($oldHeight*$newWidth/$oldWidth);
            // $oldWidth = $newWidth;
            // $oldHeight = $newHeight;
        // }
        // if ($height && intval($height) > 0 && $oldHeight > $height) {
            // $newHeight = intval($height);
            // $newWidth = intval($oldWidth*$newHeight/$oldHeight);
        // }

        switch (intval($this->imageSize[2])) {
            case IMG_GIF:
                $oldImage = ImageCreateFromGIF($this->file);
                break;
            case IMG_JPG:
                $oldImage = ImageCreateFromJPEG($this->file);
                break;
            case IMG_PNG:
                $oldImage = ImageCreateFromPNG($this->file);
                break;
            case IMG_WBMP:
                $oldImage = ImageCreateFromWBMP($this->file);
                break;
            default:
                $e = new PException('Image type not supported!');
                $e->addInfo(print_r($this->imageSize, TRUE));
                break;
        }
        $newImage = ImageCreateTrueColor($th_size_x, $th_size_y);
        imageCopyResampled($newImage, $oldImage, 0, 0, $startx, $starty, $th_size_x, $th_size_y, $size_x, $size_y);
        $newFile = tempnam('Lorem ipsum dolor sit amet', 'thumb');

        switch ($this->imageSize[2]) {
            case IMG_GIF:
                ImageTrueColorToPalette ($newImage, TRUE, 256);
                ImageGIF ($newImage, $newFile);
                $mimetype = 'image/gif';
                break;
            case IMG_JPG:
                ImageJPEG ($newImage, $newFile);
                $mimetype = 'image/jpeg';
                break;
            case IMG_PNG:
                ImagePNG ($newImage, $newFile);
                $mimetype = 'image/png';
                break;
            case IMG_WBMP:
                ImageWBMP ($newImage, $newFile);
                $mimetype = 'image/wbmp';
                break;
        }

        $dest = $dir.'/'.$prefix;
        if (!$prefixIsRealName)
            $dest .= $this->hash;
        if (!@copy($newFile, $dest))
            return false;
        unlink ($newFile);
        return true;
    }
    
    public function getHash() {
        return $this->hash;
    }
    
    public function getMimetype() {
        if (!isset($this->mimetype))
            return false;
        return $this->mimetype;
    }
    
    public function getImageSize() {
        if (!isset($this->imageSize))
            return false;
        return $this->imageSize;
    }
    
    public function isImage() {
        if (!isset($this->file) || !isset($this->imageSize))
            return false;
        return true;
    }    
}
?>
