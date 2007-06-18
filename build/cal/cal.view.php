<?php
/**
 * Cal view
 *
 * @package cal
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: cal.view.php 88 2006-06-30 10:07:13Z kang $
 */
class CalView extends PAppView 
{
    /**
     * @var Cal
     * @access private
     */
    private $_model;
    
    /**
     * @param Cal $model
     */
    public function __construct(Cal $model) 
    {
        $this->_model = $model;
    }

    /**
     * @param int $y year
     * @param int $m month
     */
    public function aCalMonth($y, $m) 
    {
        require TEMPLATE_DIR.'apps/cal/amonth.php';
    }

    /**
     * @param int $y year
     * @param int $m month
     * @param int $d day
     */
    public function calDay($y, $m, $d) 
    {
    }

    /**
     * @param int $y year
     * @param int $m month
     */
    public function calMonth($y, $m) 
    {
        require TEMPLATE_DIR.'apps/cal/month.php';
    }

    /**
     * @param void
     */
    public function monthSelector() 
    {
        require TEMPLATE_DIR.'apps/cal/monthselector.php';
    }
}
?>