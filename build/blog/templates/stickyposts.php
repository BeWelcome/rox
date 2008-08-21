<?php
/**
 * sticky posts
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$Blog = new Blog();
$BlogView = new BlogView($Blog);
?>
<div class="blog-sticky">
<?
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();
    if (preg_match('%/page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
    } else {
        $page = 1;
    }
    $requestStr = preg_replace('%[/]page\d+%', '', $requestStr);
    
    // display matching tags and matching posts.
    $postIt      = $Blog->getStickyPostIt();
    $pages       = PFunctions::paginate($postIt, $page);
    $postIt      = $pages[0];
    $maxPage     = $pages[2];
    $pages       = $pages[1];
    $currentPage = $page;
    foreach ($postIt as $blog) {
        require 'blogitem.php';
    }
    $BlogView->pages($pages, $currentPage, $maxPage, $requestStr.'/page%d');
?>
</div>
