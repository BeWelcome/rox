<?php
/**
 * user friends template
 *
 * @package user
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
$friendsText = array();
$i18n = new MOD_i18n('apps/user/friends.php');
$friendsText = $i18n->getText('friendsText');
?>
<h2><?=$friendsText['title']?></h2>
<form method="get" action="user/find" class="def-form">
    <div class="bw-row">
        <label for="friend-search"><?=$friendsText['label_friendsearch']?></label><br/>
        <input type="text" id="friend-search" name="q" class="long"/>
        <p class="desc"><?=$friendsText['desc_friendsearch']?></p>
    </div>
    <p>
        <input type="submit" class="button" value="<?=$friendsText['submit_friendsearch']?>"/>
    </p>
</form>
<?php
if (!isset($friends) || !$friends) {
	echo '<p class="notify">'.$friendsText['no_friends'].'</p>';
} else {
?>
<h3><?=$friendsText['title_friendlist']?></h3>
<?php
    foreach ($friends as $friend) {
?>
<div class="user">
    <a href="user/<?=$friend->handle?>">
        <img src="user/avatar/<?=$friend->handle?>" alt="<?=$friend->handle?>" class="l"/>
        <?=$friend->handle?>
    </a>
</div>
<?php
    }
}
?>