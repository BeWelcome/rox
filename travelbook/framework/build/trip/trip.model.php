<?php
/**
 * trip model
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.model.php 174 2006-11-15 00:36:47Z kang $
 */
class Trip extends PAppModel {
    protected $dao;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function assignGallery($tripId, $galleryId)
    {
    	$query = '
DELETE FROM `trip_to_gallery` WHERE
`trip_id_foreign` = '.(int)$tripId.' AND `gallery_id_foreign` = '.(int)$galleryId.'
        ';
        $this->dao->exec($query);
        $query = '
INSERT INTO `trip_to_gallery` (`trip_id_foreign`, `gallery_id_foreign`) VALUES
('.(int)$tripId.', '.(int)$galleryId.')
        ';
        $s = $this->dao->query($query);
        return ($s->affectedRows() != -1);
    }
    
    public function createProcess()
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
    	if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            $errors = array();
            if (!isset($vars['n']) || !$vars['n'])
                $errors[] = 'name';
            $vars['errors'] = $errors;
            if (count($errors) > 0)
              return false;

            $tripId = $this->insertTrip($vars['n'], $vars['d'], (int)$User->getId());
            if (!$tripId) {
            	$vars['errors'][] = 'not_created';
                return false;
            }
            if (isset($vars['cg']) && $vars['cg']) {
                $Gallery = new Gallery;
                $galleryId = $Gallery->createGallery($vars['n']);
                if (!$galleryId) {
                    $vars['errors'][] = 'gallery_not_created';
                } else {
                	$this->assignGallery($tripId, $galleryId);
                }
            }
    		return false;
    	} else {
    		PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
    	}
    }
    
    /**
     * Intended to replace old functions:
     * tripropdown($userID)
     */
    public function getTripsForUser($userId) {
        $s = $this->dao->prepare('
SELECT 
    t.`trip_id`,
    d.`trip_name` 
FROM `trip` AS t
LEFT JOIN `trip_data` AS d ON
    d.`trip_id` = t.`trip_id`
WHERE t.`user_id_foreign` = ?
        ');
        $s->execute($userId);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }
    
    public function insertTrip($name, $description, $userId) {
        $s = $this->dao->prepare('
INSERT INTO `trip`
(`trip_id`, `trip_options`, `trip_touched`, `user_id_foreign`)
VALUES
(?, 0, NOW(), ?)
        ');
        $s->prepare('
INSERT INTO `trip_data` (`trip_id`, `trip_name`, `trip_text`, `trip_descr`) VALUES (?, ?, \'\', ?);
');
        $s->setCursor(0);
        $s->execute(array(0=>$this->dao->nextId('trip'), 1=>$userId));
        if (!$tripId = $s->insertId())
          return false;
        $s->setCursor(1);
        $s->execute(array(0=>$tripId, 1=>$name, 2=>$description)); 
        return $tripId;
    }
}
?>
