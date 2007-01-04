<?php
/**
 * Default view
 *
 * @package default
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: default.view.php 139 2006-07-15 13:25:52Z kang $
 */
class PDefaultView extends PAppView {
    public function doOutput($raw = false) {
        header('Content-type: text/html;charset="utf-8"');
        require_once TEMPLATE_DIR.'page.php';
        return true;
    }
}
?>