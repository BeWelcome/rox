<?php
class GroupDummyImage extends DummyImage
{
    const IMAGE_DIR = '../../../data/groups';
    const BASEIMAGE = 'groupbase.png';

    /**
     * Construction of an imageresource to serve as a blueprint
     *
     * @access public
     * @param array $data Metadata for this image from db
     **/
    public function __construct($data)
    {
        $this->id = $data['picid'];
        $this->name = $data['name'];
        $this->setImageDir();
        $this->size = getimagesize(STATIC::BASEIMAGE);
        $this->blueprint = imagecreatefrompng(STATIC::BASEIMAGE);

        list($backclr,$dummy) = $this->getColor(13,12,11,1);
        imagefill($this->blueprint,1,1,$backclr);
        list($lefthead,$leftbody) = $this->getColor(12,13,10,3,1.3);
        imagefill($this->blueprint,90,90,$lefthead);
        imagefill($this->blueprint,75,180,$leftbody);
        list($righthead,$rightbody) = $this->getColor(11,11,11,5,1.3);
        imagefill($this->blueprint,180,120,$righthead);
        imagefill($this->blueprint,210,210,$rightbody);
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
        $original_x = min($this->size[0],1024);
        $original_y = min($this->size[1],1024);
 
        $thumbData = array();
        $thumbData[$this->getFileName('')] = $this->getThumbSize(300,300, 'ratio', $this->size);
        $thumbData['thumb'] = $this->getThumbSize(100,100, 'ratio', $this->size);
 
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
}
