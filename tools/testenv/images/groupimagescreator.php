<?php
class GroupImagesCreator extends ImagesCreator
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
SELECT id picid,Picture name
FROM groups
WHERE NOT Picture=""
ORDER BY id ' . $this->getLimitText($limit);
        return $this->db->selectQuery($sSql);
    }

    /**
     * Wrapper to get all images for groups
     *
     * @access protected
     * @param array $pic Queryresults from database
     **/
    protected function getDummyImage($pic)
    {
        $dummy = new GroupDummyImage($pic);
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
        return 'group';
    }

}
