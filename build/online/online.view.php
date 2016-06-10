<?php
/**
 * Gallery view
 *
 * @package gallery
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class OnlineView extends PAppView {
    private $_model;

    public function __construct(Online $model)  {
        $this->_model =& $model;
    }

    public function Teaser()
    {
        $TMembers=$this->_model->GetMembers() ;
        $TGuests=$this->_model->GetGuests() ;
        $TotMembers=$this->_model->GetTotMembers() ;
        require 'templates/teaser.php';
    }

    public function ShowOnline()      {
        global $_SYSHCVOL ;

        $words = new MOD_words($this->getSession());
        PVars::getObj('page')->title = $words->getBuffered('WhoIsOnLinePage');
        $TMembers=$this->_model->GetMembers() ;
        $TGuests=$this->_model->GetGuests() ;
        $TotMembers=$this->_model->GetTotMembers() ;
        require 'templates/showonline.php';
    }
}
?>
