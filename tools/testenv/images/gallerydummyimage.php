<?php
class GalleryDummyImage extends DummyImage
{
    const IMAGE_DIR = '../../../data/gallery';

    protected $user; // userid for this galleryimage

    /**
     * Construction of an imageresource to serve as a blueprint
     *
     * @access public
     * @param array $data Metadata for this image from db
     **/
    public function __construct($data)
    {
        $this->user = $data['id'];
        $this->id = $data['picid'];
        $this->name = $data['name'];
        $this->setImageDir();
        $this->size = array($data['width'],$data['height'],$data['mimetype']);
        $this->blueprint = imagecreatetruecolor($data['width'],$data['height']);

        list($backclr,$unused) = $this->getColor(14,12,11,1,2);
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
        $thumbData = array();
        $thumbData[''] = array(0,0,0,0,$this->size[0], $this->size[1], $this->size[0], $this->size[1]);
        $thumbData['thumb']  = $this->getThumbSize(100, 100, '',     $this->size);
        $thumbData['thumb1'] = $this->getThumbSize(240, 240, 'ratio',$this->size);
        $thumbData['thumb2'] = $this->getThumbSize(500, 500, 'ratio',$this->size);

        return $this->createFiles($thumbData);
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
        return $addition . $this->name;
    }

    /**
     * Set and create if needed, the working directory for created images
     *
     * @access protected
     **/
    protected function setImageDir()
    {
        $imgDir = STATIC::IMAGE_DIR . '/member' . $this->user;
        if (!is_dir($imgDir)) {
            mkdir($imgDir,'0777',true);
        }
        $this->imgDir = $imgDir;
    }
}
