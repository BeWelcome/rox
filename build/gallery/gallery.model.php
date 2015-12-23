<?php
/**
 * Gallery model
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GalleryModel extends RoxModelBase
{
    const FLAG_VIEW_PRIVATE   = 1;
    const FLAG_VIEW_PROTECTED = 2;

    public function __construct() {
        parent::__construct();
        $this->bootstrap();
    }

    public function createGallery($title, $desc = false)
    {
        if (!$member = $this->getLoggedInMember())
            return false;
    	$query = '
INSERT INTO `gallery`
(`id`, `user_id_foreign`, `flags`, `title`, `text`)
VALUES
('.$this->dao->nextId('gallery').',
'.(int)$member->get_userid().',
0,
\''.$this->dao->escape($title).'\',
\''.($desc ? $this->dao->escape($desc) : '').'\')
        ';
        $s = $this->dao->query($query);
        return $s->insertId();
    }

    public function bootstrap()
    {
    	$dataDir = new PDataDir('gallery');
    }

    public function deleteAll()
    {
    	$query = 'TRUNCATE TABLE `gallery_items`';
        $this->dao->exec($query);
        $this->dao->dropSequence('gallery_items');
        $dataDir = new PDataDir('gallery');
        $dataDir->remove(false, false, false);
        return true;
    }

    /**
     * Delete a single selfuploaded picture as loggedin owner or with gallery rights
     *
     * @access public
     * @param Object $image Image to be deleted
     * @return boolean
     */
    public function deleteOneProcess($image)
    {
        if (!$member = $this->getLoggedInMember())
            return false;
        $R = MOD_right::get();
        $GalleryRight = $R->hasRight('Gallery');
        if (($member->get_userid() == $image->user_id_foreign) || ($GalleryRight > 1)) {
            // Log the deletion to prevent admin abuse
            MOD_log::get()->write("Deleting a gallery item #".$image->id." filename: ".$image->file." belonging to user: ".$image->user_id_foreign, "Gallery");
            $this->deleteThisImage($image);
            return true;
        } else return false;
    }

    public function editProcess($vars)
    {
        $this->dao->exec("UPDATE `gallery_items` SET `title` = '".$vars['t']."' , `description` = '".$vars['txt']."' WHERE `id`= ".$vars['id']);
        if (isset($vars['gallery'])) {
            if ($this->getImageGallery($vars['id'])) {
                $this->dao->exec("UPDATE `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['gallery']."' WHERE `item_id_foreign`= ".$vars['id']);
            } else {
                $this->dao->exec("INSERT INTO `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['gallery']."', `item_id_foreign`= ".$vars['id']);
            }
            return true;
        }
        else
        {
            PPostHandler::clearVars($callbackId);
            return false;
        }
    }
    public function ajaxModImageGallery($type, $id, $title = false, $text = false)
    {
        $tableName = ($type == 'set' ? 'gallery' : 'gallery_items');
        $descField = ($type == 'set' ? 'text'    : 'description'  );

	if ($title || $text !== false){
            $this->dao->query("START TRANSACTION");
            $query = "UPDATE $tableName ";
            if ($title) {
                $query .= "SET `title` = '".$title."'";
            }
            elseif ($text !== false) {
                $query .= "SET $descField = '".$text."'";
            }
            $query .= "WHERE `id`= ".$id;
            $this->dao->exec($query);
            $this->dao->query("COMMIT");
        }
    }

    public function reorderTripItems($items)
    {
        if (!$this->checkTripItemOwnerShip($items)) {
            return;
        }

        $this->dao->query("START TRANSACTION");
        foreach ($items as $position => $item) {
            $query = sprintf("UPDATE `blog_data` SET `blog_display_order` = '%d' WHERE `blog_id` = '%d'", ($position + 1), $item);
            $this->dao->query($query);
        }
        $this->dao->query("COMMIT");
    }

    public function editGalleryProcess($vars)
    {
        $this->dao->exec("UPDATE `gallery` SET `title` = '".$vars['t']."' , `description` = '".$vars['txt']."' WHERE `id`= ".$vars['id']);
        PPostHandler::clearVars($callbackId);
        return false;
    }

    /**
     * Delete several selfuploaded pictures as loggedin owner or with gallery rights
     *
     * @access public
     * @param Object $image Image to be deleted
     * @return boolean
     */
    public function deleteMultiple($images)
    {
        if (!$member = $this->getLoggedInMember()){
            return false;
        }
        $R = MOD_right::get();
        $GalleryRight = $R->hasRight('Gallery');
        foreach ($images as $image) {
            if (!$image) {
                return false;
            }
            if ($member->get_userid() == $this->imageGalleryOwner('image',$image) || ($GalleryRight > 1)) {
                $image = $this->imageData($image);
                // Log the deletion to prevent admin abuse
                MOD_log::get()->write("Deleting multiple gallery items #".$image->id." filename: ".$image->file." belonging to user: ".$image->user_id_foreign, "Gallery");
                $this->deleteThisImage($image);
            } else {
                return false;
            }
        }
    }

    /**
     * Actual deletion of files and database entries for removed item
     *
     * @access protected
     * @param MOD_images_Image $image The image to be deleted
     **/ 
    protected function deleteThisImage($image)
    {
        $filename = $image->file;
        $userDir = new PDataDir('gallery/user'.$image->user_id_foreign);
        $userDir->delFile($filename);
        $userDir->delFile('thumb'.$filename);
        $userDir->delFile('thumb1'.$filename);
        $userDir->delFile('thumb2'.$filename);

        $this->dao->exec('
DELETE FROM `gallery_items_to_gallery`
WHERE `item_id_foreign`= ' . (int)$image->id);
        $this->dao->exec('
DELETE FROM `gallery_items`
WHERE `id` = ' . (int)$image->id);

        $this->deleteComments($image->id);
    }

    public function deleteComments($table_id,$table = 'gallery_items') {
        $shouts = new Shouts();
        return $shouts->deleteShouts($table,$table_id);
    }

    public function updateGalleryProcess($vars = null)
    {
        if (isset($vars)) {
            if (isset ($vars['new']) && $vars['new'] == 1 && !$vars['deleteOnly']) {
                if (isset($vars['g-title'])) {
                        $vars['gallery'] = $this->createGallery($vars['g-title'], $desc = false);
                } else {
                    $vars['errors'] = array('gallery');
                    return false;
                }
            }
            if (array_key_exists('imageId', $vars)) {
                $images = ($vars['imageId']);
                if (!isset($images[0]) || !$images[0]) {
                    $vars['errors'] = array('images');
                    return false;
                }
                if (!$member = $this->getLoggedInMember())
                    return false;
                if (isset($vars['deleteOnly']) && $vars['deleteOnly'])
                    return $this->deleteMultiple($images);
                foreach ($images as $d) {
                    $this->dao->exec("DELETE FROM `gallery_items_to_gallery` WHERE `item_id_foreign`= ".$d);
                    if (!isset($vars['removeOnly']) || !$vars['removeOnly']) {
                        $this->dao->exec("INSERT INTO `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$this->dao->escape($vars['gallery'])."',`item_id_foreign`= ".$d);
                    }
                }
            }
            return 'gallery/show/sets/'.$vars['gallery'];
        }
    }

    public function deleteGalleryProcess($galleryId) {
        if ($this->getLatestGalleryItem($galleryId)) {
    	$query = '
DELETE FROM `gallery_items_to_gallery`
WHERE `gallery_id_foreign`= '.(int)$galleryId.'
        ';
        $return = $this->dao->exec($query);
        }
    	$query = '
DELETE FROM `gallery`
WHERE `id` = '.(int)$galleryId.'
        ';
        if (isset($return) && $return == false)
            return false;
        return $this->dao->exec($query);

    }

    /**
     * returns a single gallery entity
     *
     * @param int $galleryId - id of gallery to fetch
     *
     * @access public
     * @return Gallery|false
     */
    public function getGallery($galleryId)
    {
        return $this->createEntity('Gallery')->findById($galleryId);
    }

    public function getGalleryItems($galleryId,$count=false)
    {
    	$query = '
SELECT
    i.`item_id_foreign`
FROM `gallery_items_to_gallery` AS i
WHERE i.`gallery_id_foreign` = '.(int)$galleryId.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        if ($count == true)
            return $s->numRows();
        return $s;
    }

    public function getLatestGalleryItem($galleryId)
    {
    	$query = '
SELECT
    i.`item_id_foreign` as item_id
FROM `gallery_items_to_gallery` AS i
WHERE i.`gallery_id_foreign` = '.(int)$galleryId.'
ORDER BY `item_id_foreign` DESC LIMIT 1
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        //$img = $this->imageData((int)$s);
        return $s->fetch(PDB::FETCH_OBJ)->item_id;
    }

    public function getItemGallery($imageId)
    {
    	$query = '
SELECT
    i.`item_id_foreign`,
    i.`gallery_id_foreign`
FROM `gallery_items_to_gallery` AS i
WHERE i.`item_id_foreign` = '.(int)$imageId.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getUserGalleries($UserId = false)
    {
    	$query = '
SELECT
`id`, `user_id_foreign`, `flags`, `title`, `text`
FROM `gallery`';
if ($UserId) {
    	$query .= '
WHERE gallery.`user_id_foreign` = '.(int)$UserId;
}
    	$query .= '
ORDER BY `id` DESC';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    /**
     * Gets number of gallery items for a user
     *
     * @param integer $userId Travelbook user ID
     *
     * @return integer
     */
    public function getUserItemCount($userId)
    {
        $query = '
SELECT
COUNT(*) cnt
FROM `gallery_items`
WHERE `gallery_items`.`user_id_foreign` = ' . (int) $userId;
        $s = $this->dao->query($query);
        $result = $s->fetchColumn();
        $count = (int) $result->cnt;
        return $count;
    }

    public function getGalleriesNotEmpty($UserId = false, $publicOnly = false)
    {
        $query = '
SELECT DISTINCT
    g.`id` AS `id`,
    `user_id_foreign`,
    `flags`,
    `title`,
    `text`
FROM `gallery` AS g
LEFT JOIN `gallery_items_to_gallery` AS gi ON
    g.`id` = gi.`gallery_id_foreign`';
        if ($publicOnly) {
            $query .= '
LEFT JOIN `user` AS `u` ON
    u.`id` = g.`user_id_foreign`
LEFT JOIN `members` AS `m` ON
    u.`handle` = m.`Username`
LEFT JOIN `memberspublicprofiles` AS `mp` ON
    m.`id` = mp.`IdMember`';
        }
        $query .= '
WHERE
    g.`id` = gi.`gallery_id_foreign`
    AND ';
        if ($UserId) {
            $query .= '
    g.`user_id_foreign` = ' . (int)$UserId . '
    AND ';
        }
        if ($publicOnly) {
            $query .= '
    m.`id` = mp.`idMember`
    AND
    m.`Status` = \'Active\'
    AND ';
        }
        $query .= '
    1';
        $query .= '
ORDER BY `id` DESC';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getGalleriesNotEmptyEntities()
    {
        $sql = <<<SQL
            SELECT DISTINCT
            `id`, `user_id_foreign`, `flags`, `title`, `text`
            FROM `gallery`
            LEFT JOIN `gallery_items_to_gallery` AS `g` ON
                g.`gallery_id_foreign` = g.`gallery_id_foreign`
            WHERE g.`gallery_id_foreign` = gallery.`id`
            ORDER BY `id` DESC
SQL;
        return $this->createEntity('Gallery')->findBySQLMany($sql);
    }

    public function getMemberWithUserId($userId)
    {
        if (!($userId = intval($userId)))
        {
            return false;
        }
        $s = $this->singleLookup('SELECT handle FROM user WHERE id = '.(int)$userId);
        return $this->createEntity('Member')->findByUsername($s->handle);
    }

    public function getLatestItems($userId = false, $galleryId = false, $numRows = false, $publicOnly = false)
    {
    	$query = '
SELECT
    i.`id`,
    i.`user_id_foreign`,
    u.`handle` AS `user_handle`,
    i.`file`,
    i.`original`,
    i.`flags`,
    i.`mimetype`,
    i.`width`,
    i.`height`,
    i.`title`,
    i.`created`
FROM `gallery_items` AS i
LEFT JOIN `user` AS `u` ON
    u.`id` = i.`user_id_foreign`
LEFT JOIN `gallery_items_to_gallery` AS `g` ON
    g.`item_id_foreign` = i.`id` ';
        if ($publicOnly) {
            $query .= '
LEFT JOIN `members` AS `m` ON
    u.`handle` = m.`Username`
LEFT JOIN `memberspublicprofiles` AS `mp` ON
    m.`id` = mp.`IdMember`';
        }
        $query .= '
WHERE ';
        if ($userId) {
        	$query .= '
    `user_id_foreign` = '.(int)$userId.'
AND ';
        }
        if ($galleryId) {
        	$query .= '
    `gallery_id_foreign` = '.(int)$galleryId.'
AND ';
        }
        if ($publicOnly) {
            $query .= '
    m.`id` = mp.`idMember`
AND
    m.`Status` IN ( \'Active\', \'Pending\', \'OutOfRemind\')
AND ';
        }
        $query .= '1 = 1';
        $query .= '
ORDER BY `created` DESC
        ';

        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getNextItems($imageId,$limit = 1,$userId = false,$galleryId = false)
    {
    	$query = '
SELECT
    i.`id`,
    i.`user_id_foreign`,
    u.`handle` AS `user_handle`,
    i.`file`,
    i.`original`,
    i.`flags`,
    i.`mimetype`,
    i.`width`,
    i.`height`,
    i.`title`,
    i.`created`
FROM `gallery_items` AS i
LEFT JOIN `user` AS `u` ON
    u.`id` = i.`user_id_foreign`
WHERE i.`id` > '.(int)$imageId.'
        ';
        if ($userId) {
        	$query .= '
AND `user_id_foreign` = '.(int)$userId.'
            ';
        }
        $query .= '
ORDER BY `id` ASC LIMIT '.(int)$limit.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getPreviousItems($imageId,$limit = 1,$userId = false,$galleryId = false)
    {
    	$query = '
SELECT
    i.`id`,
    i.`user_id_foreign`,
    u.`handle` AS `user_handle`,
    i.`file`,
    i.`original`,
    i.`flags`,
    i.`mimetype`,
    i.`width`,
    i.`height`,
    i.`title`,
    i.`created`
FROM `gallery_items` AS i
LEFT JOIN `user` AS `u` ON
    u.`id` = i.`user_id_foreign`
WHERE i.`id` < '.(int)$imageId.'
        ';
        if ($userId) {
        	$query .= '
AND `user_id_foreign` = '.(int)$userId.'
            ';
        }
        $query .= '
ORDER BY `id` DESC LIMIT '.(int)$limit.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getNextGalleryItems($imageId,$limit = 1,$galleryId = false)
    {
    	$query = '
SELECT
    i.`item_id_foreign`,
    i.`gallery_id_foreign`
FROM `gallery_items_to_gallery` AS i
WHERE i.`gallery_id_foreign` = '.(int)$galleryId.'
AND i.`item_id_foreign` > '.(int)$imageId.'
ORDER BY `id` ASC LIMIT '.(int)$limit.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function getPreviousGalleryItems($imageId,$limit = 1,$galleryId = false)
    {
    	$query = '
SELECT
    i.`item_id_foreign`,
    i.`gallery_id_foreign`
FROM `gallery_items_to_gallery` AS i
WHERE i.`gallery_id_foreign` = '.(int)$galleryId.'
AND i.`item_id_foreign` < '.(int)$imageId.'
ORDER BY `id` DESC LIMIT '.(int)$limit.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function imageData($itemId)
    {
        $query = "
SELECT
    i.`id`,
    i.`user_id_foreign`,
    u.`handle` AS `user_handle`,
    i.`file`,
    i.`original`,
    i.`flags`,
    i.`mimetype`,
    i.`width`,
    i.`height`,
    i.`title`,
    i.`description`,
    i.`created`
FROM `gallery_items` AS i,
user as u,
members as m
WHERE i.`id` = ". (int)$itemId . "
AND u.id = i.user_id_foreign
AND m.Username = u.handle
AND m.Status IN ('Active', 'Pending', 'OutOfRemind')
        ";

        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        $d = $s->fetch(PDB::FETCH_OBJ);
        return $d;
    }

    public function imageGalleryOwner($type,$id)
    {
        $query = '
SELECT
    `user_id_foreign`
FROM ' . ($type === 'set'?'`gallery`':'`gallery_items`') . '
WHERE
    `id` = '.(int)$id.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() !== 1)
            return false;
        return $s->fetch(PDB::FETCH_OBJ)->user_id_foreign;
    }

    /**
     * processing image uploads
     *
     * @todo sizes should be customizable
     */
    public function uploadProcess(&$vars)
    {
        // NEW CHECKS
        if (!$member = $this->getLoggedInMember()) {
             $vars['error'] = 'Gallery_NotLoggedIn';
             return false;
        }
        if (!isset($_FILES['gallery-file']) || !is_array($_FILES['gallery-file']) || count($_FILES['gallery-file']) == 0) {
            $vars['error'] = 'Gallery_UploadError';
            return false;
        }
        $noError = true; // flag for error on one file
        $userDir = new PDataDir('gallery/user'.$member->get_userid());
        $insert = $this->dao->prepare('
INSERT INTO `gallery_items`
(`id`, `user_id_foreign`, `file`, `original`, `flags`, `mimetype`, `width`, `height`, `title`, `created`)
VALUES
(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
        $itemId = false;
        $insert->bindParam(0, $itemId);
        $userId = $member->get_userid();
        $insert->bindParam(1, $userId);
        $hash = false;
        $insert->bindParam(2, $hash);
        $orig = false;
        $insert->bindParam(3, $orig);
        $flags = 0;
        $insert->bindParam(4, $flags);
        $mimetype = false;
        $insert->bindParam(5, $mimetype);
        $width = false;
        $insert->bindParam(6, $width);
        $height = false;
        $insert->bindParam(7, $height);
        $title = false;
        $insert->bindParam(8, $title);
        foreach ($_FILES['gallery-file']['error'] as $key=>$error) {
        	$fileName = $_FILES['gallery-file']['name'][$key];
        	// if upload failed, set error message
        	if (!empty($fileName) && $error != UPLOAD_ERR_OK) {
        		$noError = false;
        		switch ($error) {
        		    case UPLOAD_ERR_INI_SIZE:
        			case UPLOAD_ERR_FORM_SIZE:
        				$vars['fileErrors'][$fileName] = 'Gallery_UploadFileTooLarge';
                		break;
        			default:
        				$vars['fileErrors'][$fileName] = 'Gallery_UploadError';
            			break;
            	}
        	} elseif (!empty($fileName)) { // upload succeeded -> check if image
            	$img = new MOD_images_Image($_FILES['gallery-file']['tmp_name'][$key]);
            	if (!$img->isImage()) {
            		$noError = false;
            		$vars['fileErrors'][$fileName] = 'Gallery_UploadNotImage';
            	} else { // upload is image
                    // resize
		            $size = $img->getImageSize();
		            $original_x = min($size[0],PVars::getObj('images')->max_width);
		            $original_y = min($size[1],PVars::getObj('images')->max_height);
		            $tempDir = dirname($_FILES['gallery-file']['tmp_name'][$key]);
		            $resizedName = md5($_FILES['gallery-file']['tmp_name'][$key]) . '_resized';
		            $img->createThumb($tempDir,$resizedName, $original_x, $original_y, true, 'ratio');
		            $tempFile = $tempDir . '/' . $resizedName;

		            // create new image object from resized image
		            $img = new MOD_images_Image($tempFile);
		            $size = $img->getImageSize();
		            $type = $size[2];
		            $hash = $img->getHash();
		            if ($userDir->fileExists($hash))
		                continue;
		            if (!$userDir->copyTo($tempFile, $hash))
		                continue;
		            if (!$result = $this->createThumbnails($userDir,$img)) return false;
		            $itemId = $this->dao->nextId('gallery_items');
		            $orig = $_FILES['gallery-file']['name'][$key];
		            $mimetype = image_type_to_mime_type($type);
		            $width = $size[0];
		            $height = $size[1];
		            $title = $orig;
		            try {
		                $insert->execute();
		            } catch (PException $e) {
		                error_log($e->__toString());
		            }
		            if ($vars['galleryId']) {
		                $this->dao->exec("INSERT INTO `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['galleryId']."', `item_id_foreign`= ".$itemId);
		            }
		            $vars['fileErrors'][$_FILES['gallery-file']['name'][$key]] = 'Gallery_UploadFileSuccessfule';
		            unlink($tempFile);
            	}
            }
        }
        return $noError;
    }

    public function createThumbnails ($dataDir, $img)
    {
        $size = $img->getImageSize();
        if (!$img->createThumb($dataDir->dirName(), 'thumb', 100, 100))
            return false;
        if ($size[0] > 240)
            $img->createThumb($dataDir->dirName(), 'thumb1', 240, 240, false ,'ratio');
        if ($size[0] > 500 || $size[1] > 500)
            $img->createThumb($dataDir->dirName(), 'thumb2', 500, 500, false, 'ratio');
        return true;
    }

}
