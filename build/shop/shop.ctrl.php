<?php

/**
 * shop controller
 *
 * @package shop
 * @author Manu (crumbking)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ShopController extends RoxControllerBase
{

 public function index($args = false)
    {
        $request = $args->request;

        if (!isset($request[0])) {
            $page = new ShopPage();
        } else {
            $page = new ShopPage();
        }

         if (!isset($request[1])) {
                    $page = new ShopPage();
                } else switch ($request[1]) {
                    case 'europe':
                        //spreadshirt.net shop for the european shipping countries
                        $page = new EuropeShopPage();
                        break;
                    case 'world':
                        //spreadshirt.com shop for the other shipping countries
                        $page = new WorldShopPage();
                        break;
        }
        return $page;
    }

}
?>
