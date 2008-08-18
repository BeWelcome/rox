<?php


/**
 * Explore page
 *
 * @package explore
 * @author: Micha (bw: lupochen)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ExplorePage extends RoxPageView
{
    
    protected function getTopmenuActiveItem() {
        return 'explore';
    }
    
    protected function getPageTitle() {
        return 'Explore BeWelcome *';
    }
    
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

    protected function teaserContent() {
        require 'templates/teaser_explore.php';
    }
    
    protected function getColumnNames ()
    {
        return array('col3');
    }
    
    protected function leftSidebar()
    {
        $currentSubPage = $this->getCurrentSubPage();
        require 'templates/aboutbar.php';
    }
    
    protected function column_col3() {
        require 'templates/body_explore.php';
    }
    
    protected function quicksearch() {
    
    }
}


?>