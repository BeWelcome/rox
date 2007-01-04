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
        $this->hash = sha1_file($this->file);
        return true;
    }
    
    public function createThumb($dir, $prefix, $width = false, $height = false, $prefixIsRealName = false) {
        if (!isset ($this->hash))
            return FALSE;
        if (!$dir)
            $dir = dirname($this->file);
        if (!$prefix) {
            $prefix = 't';
        }

        if ((!$width && !$height) || (intval ($width) <= 0 && intval ($height) <= 0) )
            throw new PException('Neither thumbnail width nor height provided!');
        $oldWidth = $this->imageSize[0];
        $oldHeight = $this->imageSize[1];
        if ($width && intval($width) > 0 && $oldWidth) {
            $newWidth = intval($width);
            $newHeight = intval($oldHeight*$newWidth/$oldWidth);
            $oldWidth = $newWidth;
            $oldHeight = $newHeight;
        }
        if ($height && intval($height) > 0 && $oldHeight > $height) {
            $newHeight = intval($height);
            $newWidth = intval($oldWidth*$newHeight/$oldHeight);
        }

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
                $e = new PPException ('Image type not supported!');
                $e->addInfo(print_r($this->imageSize, TRUE));
                break;
        }
        $newImage = ImageCreateTrueColor($newWidth, $newHeight);
        imageCopyResampled($newImage, $oldImage, 0, 0, 0, 0, $newWidth, $newHeight, $this->imageSize[0], $this->imageSize[1]);
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