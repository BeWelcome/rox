<?php
/**
 * HC Interface model
 *
 * @package hcif
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: hcif.model.php 68 2006-06-23 12:10:27Z kang $
 */
class Hcif extends PAppModel {
    private $_dao;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function updateTanTable($userName, $onePad, $picPath) {
        $s = $this->_dao->prepare('REPLACE INTO `tantable` ( `Username` , `OnePad`, `picpath` ) VALUES (?, ?, ?)');
        $s->execute(array(1=>$userName, 2=>$onePad, 3=>$picPath));
        return $s->affectedRows();
    }
}
?>