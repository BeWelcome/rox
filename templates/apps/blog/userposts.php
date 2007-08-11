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
$blogText = array();
$i18n = new MOD_i18n('apps/blog/userposts.php');
$i18n->setEnvVar('userHandle', $userHandle);
$blogText = $i18n->getText('blogText');

$titleSetting = APP_User::getSetting($userId, 'blog_title');
if (!$titleSetting) {
?>
<h2><?=$blogText['page_title']?></h2>
<?php
} else {
?>
<h2><?=$titleSetting->value?></h2>
<?php
}

foreach($blogIt as $blog) {
    require TEMPLATE_DIR.'apps/blog/blogitem.php';
}
?>