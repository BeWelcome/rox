<?php
/**
 * HC Interface view
 *
 * @package hcif
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: hcif.view.php 68 2006-06-23 12:10:27Z kang $
 */
class HcifView extends PAppView {
    private $_model;
    
    public function __construct(Hcif $model) {
        $this->_model = $model;
    }
    
    public function hcTopmenu() {
        require TEMPLATE_DIR.'apps/hcif/topmenu.php';
    }
}
?>