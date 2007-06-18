<?php
/**
 * trip createform template
 *
 * @package trip
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$vars =& PPostHandler::getVars($callbackId);

$i18n = new MOD_i18n('apps/trip/del.php');
$delText = $i18n->getText('delText');

?>
<form method="post" action="trip/del" class="def-form">
    <h2><?php echo $delText['title']; ?></h2>

	<p><?php echo $delText['really_question']; ?></p>
	<p class="small"><?php echo $delText['info_blogentries']; ?></p>
	<p></p>
    <?php
if (isset($vars['n']) && $vars['n'])
    echo '<p>'.htmlentities($vars['n'], ENT_COMPAT, 'utf-8').'</p>';
            ?>

    <p>
<?php
	if (isset($vars['trip_id']) && $vars['trip_id']) {
		echo '<input type="hidden" name="trip_id" value="'.$vars['trip_id'].'" />';
	}
?>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?php echo $delText['Yes'];?>"/> 
        <input type="button" value="<?php echo $delText['No']; ?>" onclick="javascript: history.back();"/>
    </p>
</form>
<script type="text/javascript">//<!--
createFieldsetMenu();
setFieldsetMenu('trip-main');
//-->
</script>
<?php
PPostHandler::clearVars($callbackId);
?>