<?php
/**
 * Gallery controller
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

require_once("../htdocs/bw/lib/rights.php") ; // Requiring BW right

class OnlineController extends PAppController {
    private $_model;
    private $_view;

    public function __construct() {
        parent::__construct();
        $this->_model = new Online();
        $this->_view  = new OnlineView($this->_model);
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function index() {

    /* teaser content */
        ob_start();
        $this->_view->Teaser();
        $str = ob_get_contents();
        $P = PVars::getObj('page');
        $P->teaserBar .= $str;
        ob_end_clean();;

    /* page content */
        ob_start();
        $this->_view->ShowOnline() ;
        $str = ob_get_contents();
        ob_end_clean();
        $P = PVars::getObj('page');
        $P->content .= $str;

   } // end of index

}
?>
