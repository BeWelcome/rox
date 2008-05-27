<?php

/**
 * Hello universe controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ContactusController extends RoxControllerBase
{
    /**
     * decide which page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        
	$page = new ContactusPage();
        return $page;
    }
    
    
}


?>