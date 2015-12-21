<?php
class AvatarDummyImage extends DummyImage
{
    const IMAGE_DIR = '../../../data/user/avatars';
    const BASEIMAGE = 'avatarbase.png';
    
    /**
     * Construction of an imageresource to serve as a blueprint
     *
     * @access public
     * @param array $data Metadata for this image from db
     **/
    public function __construct($data)
    {
        srand();
        echo "Generate images for user " . $data['name'] . PHP_EOL;
        $this->id = $data['picid'];
        $this->name = $data['name'];
        $this->setImageDir();
        $count = 1;
        $blue = false;
        while (($count < 10) && (!$blue)) {
            $image = rand(0, 99);
            $this->size = getimagesize('lfw/image'.$image.'.png');
            $this->blueprint = imagecreatefrompng('lfw/image'.$image.'.png');
            $blue = $this->blueprint;
            $count++;
        }
        if($count == 10) {
            throw new Exception();
        }

         list($backclr,$puppet) = $this->getColor(14,12,11,1,2);
         imagefill($this->blueprint,75,75,$puppet);
        imagefill($this->blueprint,1,1,$backclr);
    }

    /**
     * Define and create the actual files from the blueprint
     *
     * @access public
     * @return integer Number of created files
     **/
    public function filesMake()
    {
        $max_x = min($this->size[0],150);
        $max_y = $this->size[1];
        $original_x = $this->getDimension(3);
        $original_y = $this->getDimension(5);
        
        $thumbData = array();
        $thumbData['_original']=array(0,0,0,0,$original_x, $original_y, $this->size[0],$this->size[1]);
        $thumbData['_200']    = $this->getThumbSize(200, 266, 'ratio' , $this->size);
        $thumbData['_xs']     = $this->getThumbSize( 50,  50, 'square', $this->size);
        $thumbData['_75_75']  = $this->getThumbSize( 75,  75, 'square', $this->size);
        $thumbData['_150']    = $this->getThumbSize(150, 150, 'square', $this->size);
        $thumbData['_30_30']  = $this->getThumbSize( 30,  30, 'square', $this->size);
        $thumbData['_500']    = $this->getThumbSize(500, 500, 'ratio' , $this->size);
        $thumbData['']        = $this->getThumbSize($max_x,$max_y, '' , $this->size);

        return $this->createFiles($thumbData);
    }

    /**
     * Define dimensionsize based on id
     *
     * @access protected
     * @param integer $factor An arbitrary multiplication factor
     * @return integer Dimensionsize in pixels
     **/
    protected function getDimension($factor)
    {
        $dim = intval(75*log(1/((($this->id*$this->id*$factor)%1024+1)/1024)-1)+450);
        return min(max($dim,100),1024);
    }

    /**
     * Construct filename in accordance to existing codebase for this type of images
     *
     * @access protected
     * @param string $addition String to be added to base name for specific thumbnail
     * @return string Filename (without path)
     **/
    protected function getFileName($addition)
    {
        return $this->name . $addition;
    }
}
