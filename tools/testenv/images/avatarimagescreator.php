<?php
class AvatarImagesCreator extends ImagesCreator
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
SELECT id,id name
FROM members
WHERE id IN (SELECT DISTINCT idmember FROM membersphotos )
ORDER BY id ' . $this->getLimitText($limit);
        return $this->db->selectQuery($sSql);
    }

    /**
     * Wrapper to get all images for avatars
     *
     * @access protected
     * @param array $pic Queryresults from database
     **/
    protected function getDummyImage($pic)
    {
        $dummy = new AvatarDummyImage($pic);
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
        return 'avatar';
    }

}
