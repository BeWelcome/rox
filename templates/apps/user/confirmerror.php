<?php
$confirmText = array();
$i18n = new MOD_i18n('apps/user/register.php');
$confirmText = $i18n->getText('confirmText');
if ($error) {
?>
<h2><?php echo $confirmText['error_title']; ?></h2>
<p><?php echo $confirmText['error_text']; ?></p>
<?php
} else {
?>
<h2><?php echo $confirmText['confirm_title']; ?></h2>
<p><?php echo $confirmText['confirm_text']; ?></p>
<?php
}
?>