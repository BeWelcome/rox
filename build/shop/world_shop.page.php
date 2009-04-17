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
class WorldShopPage extends ShopPage
{
    protected function column_col3() {
        require 'templates/world_body_shop.php';
    }
}


?>