<?php
/**
 * user search result template
 *
 * @package user
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$resultText = array();
$i18n = new MOD_i18n('apps/user/searchresult.php');
$resultText = $i18n->getText('resultText');

$error = true;
if (is_object($res))
    $error = false;
?>
<h2><?=$resultText['title']?></h2>
<form method="get" action="user/find" class="def-form">
    <div class="row">
        <label for="friend-search"><?=$resultText['label_search']?></label><br/>
        <input type="text" id="friend-search" name="q" class="long" <?php
if (isset($_GET['q']))
    echo 'value="'.htmlentities(stripslashes($_GET['q']), ENT_COMPAT, 'utf-8').'"';
        ?>/>
        <p class="desc"><?=$resultText['desc_search']?></p>
    </div>
    <p>
        <input type="submit" value="<?=$resultText['submit_search']?>"/>
    </p>
</form>
<?php
if ($error) {
	if ($error == 'format')
        echo '<p class="error">'.$resultText['format_error'].'</p>';
    else
        echo '<p class="notify">'.$resultText['no_result'].'</p>';
} else {
    $User = APP_User::login();
	foreach ($res as $user) {
?>
<div class="user">
    <a href="user/<?=$user->handle?>">
        <img src="user/avatar/<?=$user->handle?>" alt="<?=$user->handle?>" class="l"/>
        <?=$user->handle?>
    </a><?php
        if ($User && $user->is_friend) {
        	echo ' <span class="notify">'.$resultText['friend'].'</span>';
        } elseif ($User && $user->id == $User->getId()) {
            echo ' <span class="notify">'.$resultText['you'].'</span>';
        }
    ?>
</div>
<?php		
	}
}
?>