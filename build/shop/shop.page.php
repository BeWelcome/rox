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

    protected function getTopmenuActiveItem() {
        return 'explore';
    }

    protected function getPageTitle() {
        return 'Spread Bewelcome';
    }

    protected function teaserContent() {
        require 'templates/teaser_shop.php';
    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function column_col3() {
        require 'templates/body_shop.php';
    }
}


?>
