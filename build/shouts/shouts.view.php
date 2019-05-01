<?php

use App\Utilities\SessionTrait;

/**
 * blog view
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: blog.view.php 186 2006-12-11 13:37:47Z david $
 */
class ShoutsView extends PAppView 
{
    use SessionTrait;

    private $_model;
    
    public function __construct(Shouts $model) 
    {
        $this->_model = $model;
        $this->setSession();
    }
    
    public function showShoutsList($table,$table_id) 
    {
        require 'templates/shoutlist.php';
    }
    
    public function pages($pages, $currentPage, $maxPage, $request) 
    {
        require 'templates/pages.php';
    }
    
}
?>