<?php


/**
 * Hello universe view, a first simple version.
 * We redefine the methods of RoxPageView to configure this page.
 * We don't need to redefine all the methods, we already get something for an empty subclass of RoxPageView.
 * For the start, we only redefine the content of the main column.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class Bwtest1Page extends PageWithRoxLayout
{

    /**
     * content of the middle column - this is the most important part
    **/ 
    
    function body() 
    {
    	echo 'something';
	//include_once 'facebook-platform/php/facebook.php';    	
    	include 'facebook-platform/bwtest1/index.php';	
    }

    
}




?>