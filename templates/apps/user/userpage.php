<?php
/**
 * user page template controller
 *
 * defined vars:
 * $userId     - user ID
 * $userHandle - user handle
 *
 * @package user
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$Blog = new BlogController;
$pageText = array();
$i18n = new MOD_i18n('apps/user/userpage.php');
$pageText = $i18n->getText('pageText');
?>
<h2><?php echo $userHandle; ?></h2>
<p>
<?php
echo $pageText['default_desc'];
?>
</p>
<?php

echo $groupChange;

$Blog->userPosts($userHandle);?>