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
$i18n = new MOD_i18n('apps/user/userpage.php');
?>

<p>result:
<?php
 echo $picture;
 if (!$picture) {
 echo 'no picture';
 }
?>
</p>