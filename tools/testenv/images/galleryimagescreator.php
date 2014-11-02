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
    protected function getImageRecords($limit, $startId = null)
    {
        $sSql = '
SELECT id picid, user_id_foreign id, `file` name, mimetype, width, height
FROM gallery_items '.
$this->getStartingPointText($startId) . '
ORDER BY picid ASC ' . $this->getLimitText($limit);
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
    * Check if a starting-id id given and create code for that
    *
    * @access protected
    * @param integer $startId Itemid that was previously the last one to be processed
    * @return string Sql code to limit results
    **/
    protected function getStartingPointText($startId)
    {
        if ($startId !== null){
            return 'WHERE id > ' . (int)$startId;
        } else {
            return '';
        }
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
