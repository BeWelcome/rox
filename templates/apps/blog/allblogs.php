<?
/**
 * blog page template controller
 *
 * defined vars:
 * $blogIt     - iterator over the blogs to display.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

$blogText = array();
$i18n = new MOD_i18n('apps/blog/allblogs.php');
$blogText = $i18n->getText('blogText');
echo '<h2>'.$blogText['page_title'].'</h2>'."\n";
foreach($blogIt as $blog)
{
    require TEMPLATE_DIR.'apps/blog/blogitem.php';
}
?>
