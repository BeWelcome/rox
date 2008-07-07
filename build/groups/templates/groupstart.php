<h3>Group Description</h3>
<?=$this->getGroupDescription() ?><br>
<?php
/* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
?>
<h3>Group Members</h3>
<div><?php $memberlist_widget->render() ?></div>
<h3>Group Forum</h3>
<div><?php $forums_widget->render() ?></div>

