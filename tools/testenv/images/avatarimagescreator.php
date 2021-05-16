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
    protected function getImageRecords($limit, $startId = null)
    {
        $sSql = "
SELECT id picid,id name
FROM members
WHERE status in ('Active', 'OutOfRemind') AND id IN (
    SELECT DISTINCT idmember
    FROM membersphotos " .
    $this->getStartingPointText($startId) .")
ORDER BY picid " . $this->getLimitText($limit);
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
        $this->imgCount += 1;
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
            return 'WHERE idmember > ' . (int)$startId;
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
        return 'avatar';
    }



}
