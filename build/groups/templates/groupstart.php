<h3><?php echo $words->get('GroupDescription'); ?></h3>
<p><?=$this->getGroupDescription() ?></p>
<?php
/* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
?>
<h3><?php echo $words->get('GroupMembers'); ?></h3>
<div><?php $memberlist_widget->render() ?></div>
<h3><?php echo $words->get('GroupForums'); ?></h3>
<div><?php $forums_widget->render() ?></div>
<h3><?php echo $words->get('GroupWiki'); ?></h3>
<div><?php echo $wiki->getWiki($wikipage,false); ?></div>