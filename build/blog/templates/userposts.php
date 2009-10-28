<?
/**
 * user blog page template controller
 *
 * defined vars:
 * $blogIt     - iterator over the blogs to display.
 * $userId     - user ID
 * $userHandle - handle of the user.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

/* removed - referencing app_user
$titleSetting = A PP_User::getSetting($userId, 'blog_title');
*/
if (!isset($titleSetting)) {
?>
<!--<h2><?=$titleSetting?></h2> -->
<?php
} else {
?>
<h2><?=$titleSetting->value?></h2>
<?php
}
?>
<a href="rss/blog/author/<?=$member->id?>" alt="Get the RSS-Feed of this page" class="float_right"><img src="images/icons/feed.png"></a>
<?php
foreach($blogIt as $blog) {
    require 'blogitem.php';
}
