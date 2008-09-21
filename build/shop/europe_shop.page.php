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
class EuropeShopPage extends RoxPageView
{

    protected function getTopmenuActiveItem() {
        return 'explore';
    }

    protected function getPageTitle() {
        return 'Spread Bewelcome';
    }

    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body_index.php';
    }

    protected function getStylesheets() {
        $stylesheets[] = 'styles/minimal_index.css';
        return $stylesheets;
    }

    protected function teaserContent() {
        require 'templates/teaser_shop.php';
    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function column_col3() {
        require 'templates/europe_body_shop.php';
    }
}


?>