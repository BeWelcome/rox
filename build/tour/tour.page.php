<?php
/** Tour Pages
 * 
 * @package Tour
 * @author lupochen
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class TourPage extends PageWithActiveSkin
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/tour.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/lightview.css';
        return $stylesheets;
    }

    protected function getPageTitle()
    {
        return 'The Tour - BeWelcome';
    }
    
    protected function teaserContent()
    {
        $words = $this->getWords();
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <h1><a href="tour"><?= $words->get("TheTour");?></a> 
        <?php 
        switch ($this->page_number) {
            case '2':
                echo "&raquo; " . $words->get("tour_link_openness");
                break;
            case '3':
                echo "&raquo; " . $words->get("tour_link_share");
                break;
            case '4':
                echo "&raquo; " . $words->get("tour_link_meet");
                break;
            case '5':
                echo "&raquo; " . $words->get("tour_link_trips");
                break;
            case '6':
                echo "&raquo; " . $words->get("tour_link_maps");
                break;
        } ?>       
        </h1>
        </div>
        <?
        
    }
    
    protected function column_col1()
    {
        $request = PRequest::get()->request;
        if (!isset($request[1]) || $request[1]== '')
            $step = 'tour';
        else $step = $request[1];
        require 'templates/precontent_tour.php';
    }

    protected function column_col3()
    {
        $words = new MOD_words($this->getSession());
        // needs $this->page_number declared in the controller to work
         require 'templates/tourpage'.$this->page_number.'.php';
    }
    
    protected function quicksearch() {
        parent::quicksearch();
    }
    
}
?>
