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
class Gallery extends PAppModel {
    const FLAG_VIEW_PRIVATE   = 1;
    const FLAG_VIEW_PROTECTED = 2;
    
    public function __construct() {
        parent::__construct();
        $this->bootstrap();
    }

    public function createGallery($title, $desc = false)
    {
        if (!$User = APP_User::login())
            return false;
    	$query = '
INSERT INTO `gallery`
(`id`, `user_id_foreign`, `flags`, `title`, `text`)
VALUES
('.$this->dao->nextId('gallery').', 
'.(int)$User->getId().', 
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
    
    // delete own uploaded pictures as logged in user or user with gallery rights
    public function deleteOneProcess($image)
    {
        if (!$User = APP_User::login())
            return false;
        $R = MOD_right::get();
        $GalleryRight = $R->hasRight('Gallery');
        if (($User->getId() == $this->imageOwner($image->id)) || ($GalleryRight > 1)) {
            // Log the deletion to prevent admin abuse
            MOD_log::get()->write("Deleting a gallery item #".$image->id." filename: ".$image->file." belonging to user: ".$image->user_id_foreign, "Gallery");
            // Start the deletion process
            $filename = $image->file;
            $userDir = new PDataDir('gallery/user'.$image->user_id_foreign);
            $userDir->delFile($filename);
            $userDir->delFile('thumb'.$filename);
            $userDir->delFile('thumb2'.$filename);
            $this->dao->exec('DELETE FROM `gallery_items` WHERE `id` = '.$image->id);
            $this->deleteComments($image->id);
            return true;
        } else return false;
    }
    
    public function editProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars = &PPostHandler::getVars($callbackId);
            $this->dao->exec("UPDATE `gallery_items` SET `title` = '".$vars['t']."' , `description` = '".$vars['txt']."' WHERE `id`= ".$vars['id']);
            if (isset($vars['gallery'])) {
                if ($this->getImageGallery($vars['id'])) {
                    $this->dao->exec("UPDATE `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['gallery']."' WHERE `item_id_foreign`= ".$vars['id']);
                } else {
                    $this->dao->exec("INSERT INTO `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['gallery']."', `item_id_foreign`= ".$vars['id']);
                }
            } else {
                PPostHandler::clearVars($callbackId);
        	return false;
            }
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    public function ajaxModImage($id, $title = false, $text = false)
    {
        $this->dao->exec("UPDATE `gallery_items` SET `title` = '".$title."' , `description` = '".$text."' WHERE `id`= ".$id);
    }
    
	public function reorderTripItems($items) {
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
    public function ajaxModGallery($id, $title = false, $text = false)
    {
		$this->dao->query("START TRANSACTION");
        $query = "UPDATE `gallery` ";
        if ($title) $query .= "SET `title` = '".$title."'";
        elseif ($text) $query .= "SET `text` = '".$text."'";
        $query .= "WHERE `id`= ".$id;
        $this->dao->exec($query);
		$this->dao->query("COMMIT");
    }
    
    public function editGalleryProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars = &PPostHandler::getVars($callbackId);
            $this->dao->exec("UPDATE `gallery` SET `title` = '".$vars['t']."' , `description` = '".$vars['txt']."' WHERE `id`= ".$vars['id']);
            PPostHandler::clearVars($callbackId);
        	return false;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function updateGalleryProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            $vars = PPostHandler::getVars($callbackId);
            if(isset($vars) && array_key_exists('imageId', $vars)) {
                $images = ($vars['imageId']);
                    if (!$User = APP_User::login())
                        return false;
                    foreach ($images as $d) {
                    $this->dao->exec("DELETE FROM `gallery_items_to_gallery` WHERE `item_id_foreign`= ".$d);
                    $this->dao->exec("INSERT INTO `gallery_items_to_gallery` SET `gallery_id_foreign` = '".$vars['gallery']."',`item_id_foreign`= ".$d);
                    }
                } else {
                    PPostHandler::clearVars($callbackId);
                return false;
                }
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    
    public function getGallery($galleryId = false)
    {
    	$query = '
SELECT
    i.`id`, 
    i.`user_id_foreign`, 
    i.`flags`,
    i.`title`, 
    i.`text` 
FROM `gallery` AS i
        ';
        if ($galleryId) {
        $query .= '
WHERE i.`id` = '.(int)$galleryId.'
        ';
        }
        $query .= '
ORDER BY `id` DESC';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s->fetch(PDB::FETCH_OBJ);
    }        

    public function getGalleryItems($galleryId)
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

    public function getUserGalleries($UserId)
    {
    	$query = '
SELECT
`id`, `user_id_foreign`, `flags`, `title`, `text`
FROM `gallery`
WHERE gallery.`user_id_foreign` = '.(int)$UserId.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }        
    
    
    /**
     * Processing creation of a comment
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * title        - invalid(empty) title.
     * textlen      - too short or long text.
     * inserror     - db error while inserting.
     */
    public function commentProcess($image = false) {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            $request = PRequest::get()->request;
            if (!$image)
                $image = (int)$request[3];

            // validate
            if (!isset($vars['ctxt']) || strlen($vars['ctxt']) == 0 || strlen($vars['ctxt']) > 5000) {
                $vars['errors'] = array('textlen');
                return false;
            }

            $commentId = $this->dao->nextId('gallery_comments');
            $query = '
INSERT INTO `gallery_comments`
SET
    `id`='.$commentId.',
    `gallery_items_id_foreign`='.$image.',
    `user_id_foreign`='.$User->getId().',
    `title`=\''.(isset($vars['ctit'])?$this->dao->escape($vars['ctit']):'').'\',
    `text`=\''.$this->dao->escape($vars['ctxt']).'\',
    `created`=NOW()';
            $s = $this->dao->query($query);
            if (!$s) {
                $vars['errors'] = array('inserror');
                return false;
            }
            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.implode('/', $request).'#c'.$commentId;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    
    /*
         Adding comments
        */
    public function getComments($image) {
    	$query = '
SELECT
    c.`id` AS `comment_id`,
    c.`user_id_foreign` AS `user_id`,
    u.`handle` AS `user_handle`,
    UNIX_TIMESTAMP(c.`created`) AS `unix_created`,
    c.`title`,
    c.`created`,
    c.`text`
FROM `gallery_comments` c
LEFT JOIN `user` u ON c.`user_id_foreign`=u.`id`
WHERE c.`gallery_items_id_foreign` = '.(int)$image.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function deleteComments($image) {
    	$query = '
DELETE FROM `gallery_comments`
WHERE `gallery_items_id_foreign` = '.(int)$image.'
        ';
        return $this->dao->exec($query);
    }

    public function getLatestItems($userId = false,$galleryId = false, $numRows = false)
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
    g.`item_id_foreign` = i.`id`
        ';
        if ($userId) {
        	$query .= '
WHERE `user_id_foreign` = '.(int)$userId.'
            ';
        }
        if ($galleryId) {
        	$query .= '
WHERE `gallery_id_foreign` = '.(int)$galleryId.'
            ';
        }
        $query .= '
ORDER BY `created` DESC
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        if ($numRows)
            return $s->numRows();
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
    	$s = $this->dao->query('
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
FROM `gallery_items` AS i
LEFT JOIN `user` AS `u` ON
    u.`id` = i.`user_id_foreign`
WHERE i.`id` = '.(int)$itemId.'
        ');
        if ($s->numRows() == 0)
            return false;
        $d = $s->fetch(PDB::FETCH_OBJ);
        return $d;
    }
    
    public function imageOwner($imageId)
    {
        $query = '
SELECT 
    `user_id_foreign`
FROM `gallery_items`
WHERE
    `id` = '.(int)$imageId.'
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() != 1)
            return false;
        return $s->fetch(PDB::FETCH_OBJ)->user_id_foreign;
    }
    

    /**
     * processing image uploads
     * 
     * @todo sizes should be customizable
     */
    public function uploadProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        $vars = &PPostHandler::getVars($callbackId);
        if (PPostHandler::isHandling()) {
            if (!isset($_FILES['gallery-file']) || !is_array($_FILES['gallery-file']) || count($_FILES['gallery-file']) == 0)
                return false;
            if (!$User = APP_User::login())
                return false;
            // NEW CHECKS
            if (!$User = APP_User::login()) {
                 $vars['error'] = 'Gallery_NotLoggedIn';
                 return false;
            }
            if (!isset($_FILES['gallery-file']) || !is_array($_FILES['gallery-file']) || count($_FILES['gallery-file']) == 0) {
                //$vars['error'] = 'Gallery_UploadError';
                return false;
            }
            $userDir = new PDataDir('gallery/user'.$User->getId());
            $insert = $this->dao->prepare('
INSERT INTO `gallery_items`
(`id`, `user_id_foreign`, `file`, `original`, `flags`, `mimetype`, `width`, `height`, `title`, `created`)
VALUES
(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $itemId = false;
            $insert->bindParam(0, $itemId);
            $userId = $User->getId();
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
            	if ($error != UPLOAD_ERR_OK)
                    continue;

            	if (
                    $_FILES['gallery-file']['error'] == UPLOAD_ERR_INI_SIZE ||
                    $_FILES['gallery-file']['error'] == UPLOAD_ERR_FORM_SIZE
                ) {
                    $vars['error'] = 'Gallery_UploadFileTooLarge';
                    return false;
                }
                if ($_FILES['gallery-file']['error'] == UPLOAD_ERR_OK) {
                    $vars['error'] = 'Gallery_UploadError';
                    return false;
                } 
                // END
                $img = new MOD_images_Image($_FILES['gallery-file']['tmp_name'][$key]);
                if (!$img->isImage()) {
                    $vars['error'] = 'Gallery_UploadNotImage';
                    return false;
                }
                $size = $img->getImageSize();
                $type = $size[2];
                // maybe this should be changed by configuration
                if ($type != IMAGETYPE_GIF && $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG) {
                     $vars['error'] = 'Gallery_UploadInvalidFileType';
                     return false;
                }
                $hash = $img->getHash();
                if ($userDir->fileExists($img->getHash()))
                    continue;
                if (!$userDir->copyTo($_FILES['gallery-file']['tmp_name'][$key], $hash))
                    continue;
                if (!$img->createThumb($userDir->dirName(), 'thumb', 100))
                    continue;
                if ($size[0] > 550)
                    $img->createThumb($userDir->dirName(), 'thumb2', 500);
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
            }
        	return false;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }


    /**
     * processing image uploads
     * 
     * @todo sizes should be customizable
     */
    public function uploadProcess1()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        $vars = &PPostHandler::getVars($callbackId);
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login()) {
                 $vars['error'] = 'Gallery_NotLoggedIn';
                 return false;
            }
            if (!isset($_FILES['gallery-file']) || !is_array($_FILES['gallery-file']) || count($_FILES['gallery-file']) == 0) {
                $vars['error'] = 'Gallery_UploadError';
                return false;
            }
        	if (
                $_FILES['gallery-file']['error'] == UPLOAD_ERR_INI_SIZE ||
                $_FILES['gallery-file']['error'] == UPLOAD_ERR_FORM_SIZE
            ) {
                $vars['error'] = 'Gallery_UploadFileTooLarge';
                return false;
            }
        	if ($_FILES['gallery-file']['error'] != UPLOAD_ERR_OK) {
                $vars['error'] = 'Gallery_UploadError';
                return false;
            }
            $userDir = new PDataDir('gallery/user'.$User->getId());
            $insert = $this->dao->prepare('
INSERT INTO `gallery_items`
(`id`, `user_id_foreign`, `file`, `original`, `flags`, `mimetype`, `width`, `height`, `title`, `created`)
VALUES
(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $itemId = false;
            $insert->bindParam(0, $itemId);
            $userId = $User->getId();
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
            	if ($error != UPLOAD_ERR_OK)
                    continue;
                $img = new MOD_images_Image($_FILES['gallery-file']['tmp_name'][$key]);
                if (!$img->isImage()) {
                    $vars['error'] = 'Gallery_UploadNotImage';
                    return false;
                }
                $size = $img->getImageSize();
                $type = $size[2];
                // maybe this should be changed by configuration
                if ($type != IMAGETYPE_GIF && $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG) {
                     $vars['error'] = 'Gallery_UploadInvalidFileType';
                     return false;
                }
                $hash = $img->getHash();
                if ($userDir->fileExists($img->getHash())) {
                     $vars['error'] = 'Gallery_UploadImageAlreadyUploaded';
                     return false;
                 }
                 if (!$userDir->copyTo($_FILES['gallery-file']['tmp_name'], $hash)) {
                     $vars['error'] = 'Gallery_UploadError';
                     return false;
                }
                if (!$img->createThumb($userDir->dirName(), 'thumb', 100)) {
                    $vars['error'] = 'Gallery_UploadError';
                    return false;
                }
                if ($size[0] > 550)
                    $img->createThumb($userDir->dirName(), 'thumb2', 500);
                $itemId = $this->dao->nextId('gallery_items');
                $orig = $_FILES['gallery-file']['name'];
                $mimetype = image_type_to_mime_type($type);
                $width = $size[0];
                $height = $size[1];
                $title = $orig;
                try {
                	$insert->execute();
                } catch (PException $e) {
                	error_log($e->__toString());
                }
            }
        	return false;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
}
?>
