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
if (isset($title)) echo '<h2>'.$title.'</h2>'."\n";
$map_conf = PVars::getObj('map');
?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
<?php
foreach($blogIt as $blog)
{
    require 'blogitem.php';
}
?>
