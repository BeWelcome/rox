<?php
/**
 * categories
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$Blog = new Blog;
$words = new MOD_words();
// get current request
$request = PRequest::get()->request;

if (!$username) $username = $request[1];
$member = APP_User::userId($username);
$catIt = $Blog->getCategoryFromUserIt($member);

?>
<div id="blog-category">
<h3><?=$words->getFormatted('blog_categories')?></h3>

<ul>
<?
foreach ($catIt as $cat) {
    echo '    <li>
        <a href="blog/'.$username.'/cat/'.$cat->blog_category_id.'">'.$cat->name.'</a>
        </li>';
}
?>
</ul>

</div>
