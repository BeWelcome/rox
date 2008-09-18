<h3><?php echo $words->get('GroupDescription'); ?></h3>
<p><?=$this->getGroupDescription() ?></p>
<?php
/* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
?>
<h3><?php echo $words->get('GroupMembers'); ?></h3>
<div><?php $memberlist_widget->render() ?></div>
<h3><?php echo $words->getFormatted('ForumRecentPostsLong');?></a></h3>
<a href=forums/new/u<?echo $this->getGroupId()?>><?echo $words->get('ForumGroupNewPost');?></a>
                <div class="floatbox">
                <?php echo $Forums->showExternalGroupThreads($group_id); ?>
                </div>
<h3><?php echo $words->get('GroupWiki'); ?></h3>
<div><?php echo $wiki->getWiki($wikipage,false); ?></div>