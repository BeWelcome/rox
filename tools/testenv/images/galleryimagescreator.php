<?php
class GalleryImagesCreator extends ImagesCreator
{
    /**
     * Retrieve metadata for images
     *
     * @access protected
     * @param integer $limit Maximum number of images to be created
     * @return array Queryresult
     **/
    protected function getImageRecords($limit)
    {
        $sSql = '
SELECT id picid, user_id_foreign id, `file` name, mimetype, width, height
FROM gallery_items
ORDER BY user_id_foreign ASC ' . $this->getLimitText($limit);
        return $this->db->selectQuery($sSql);
    }

    /**
     * Wrapper to get all images for galleries
     *
     * @access protected
     * @param array $pic Queryresults from database
     **/
    protected function getDummyImage($pic)
    {
        $dummy = new GalleryDummyImage($pic);
        $this->imgCount += $dummy->filesMake();
    }

    /**
     * Type of images that are created
     *
     * @access protected
     * @return string Type of images
     **/
    protected function getType()
    {
        return 'gallery';
    }

}
