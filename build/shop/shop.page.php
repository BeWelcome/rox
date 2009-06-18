<?php


/**
 * Shop page
 *
 * @package shop
 * @author: Manu (bw: crumbking)
 * @copyright Ula! what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ShopPage extends RoxPageView
{
    
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3_75percent.css';
        return $stylesheets;
    }

    protected function getTopmenuActiveItem() {
        return 'explore';
    }

    protected function getPageTitle() {
        return 'Spread Bewelcome';
    }

    protected function teaserContent() {
        require 'templates/teaser_shop.php';
    }

    protected function column_col3() {
        require 'templates/body_shop.php';
    }
}


?>