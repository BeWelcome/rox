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

    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body_index.php';
    }
    
    protected function getStylesheets() {
        $stylesheets[] = 'styles/minimal_index.css';
        return $stylesheets;
    }
    
    protected function getStylesheetPatches()
    {
        $stylesheet_patches[] = 'styles/YAML/patches/patch_2col_left_seo.css';
        return $stylesheet_patches;
    }

    protected function getPageTitle()
    {
        return 'BeWelcome - The Tour';
    }
    
    protected function teaserContent()
    {
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="tour">- The Tour -</a></h1>
        </div>
        </div><?php
    }
    
    protected function column_col2()
    {
        $request = PRequest::get()->request;
        if (!isset($request[1]) || $request[1]== '')
            $step = 'tour';
        else $step = $request[1];
        require TEMPLATE_DIR.'apps/tour/precontent_tour.php';
    }
    
    protected function leftSidebar()
    {
        
    }

    protected function column_col3()
    {
        $words = new MOD_words();
        // needs $this->page_number declared in the controller to work
         require TEMPLATE_DIR.'apps/tour/tourpage'.$this->page_number.'.php';
    }
    
    protected function quicksearch()
    {
        PPostHandler::setCallback('quicksearch_callbackId', 'SearchmembersController', 'index');
    }
}


?>
