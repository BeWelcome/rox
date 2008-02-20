<?php
/**
* Country view
*
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class TourView extends PAppView {
	private $_model;
	
	public function __construct(Tour $model) {
		$this->_model = $model;
	}
    
	public function customStyles1Col()	{		
	// calls a 1column layout 
		 echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/bw_basemod_1col.css\" type=\"text/css\"/>";
		 echo "<link rel=\"stylesheet\" href=\"styles/lightview.css\" type=\"text/css\"/>";
	}    

    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    public function ShowSimpleTeaser($title,$step)    {
        require TEMPLATE_DIR.'apps/tour/teaser_simple.php';
    }

// PreContent (Everything in 'PreContent')    
    public function precontenttour($step)    {
        require TEMPLATE_DIR.'apps/tour/precontent_tour.php';
    }   
    
// Pages (Everything in 'Content')

    public function tourpage()    {
        require TEMPLATE_DIR.'apps/tour/tourpage.php';
    }   
    public function tourpage2()    {
        require TEMPLATE_DIR.'apps/tour/tourpage2.php';
    }   
    public function tourpage3()    {
        require TEMPLATE_DIR.'apps/tour/tourpage3.php';
    }   
    public function tourpage4()    {
        require TEMPLATE_DIR.'apps/tour/tourpage4.php';
    }   
    public function tourpage5()    {
        require TEMPLATE_DIR.'apps/tour/tourpage5.php';
    }   
    public function tourpage6()    {
        require TEMPLATE_DIR.'apps/tour/tourpage6.php';
    }   
}
?>
