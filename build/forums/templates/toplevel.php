<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
$User = $this->_model->getLoggedInMember();

$noForumLegendBox = true;
$ToogleTagCloud=true ;
$TagCloud=true ;
?><?php
if ($ownGroupsButtonCallbackId) {
    if ($boards->owngroupsonly == "No") {
        $buttonText = $this->words->getBuffered('SwitchShowOnlyMyGroupsTopics');
    } else {
        $buttonText = $this->words->getBuffered('SwitchShowAllGroupsTopics');
    }
    ?>
    <div class="row"><div class="col-12">
        <form method="post" action="<?php echo rtrim(implode('/', $request), '/').'/';?>">
            <input type="hidden" name="<?php echo $ownGroupsButtonCallbackId; ?>"  value="1">
            <input type="submit" class="btn btn-primary float-right" name="submit" value="<?php echo $buttonText; ?>">
        </form>
    </div>
    </div>
    <?php
    echo $this->words->flushBuffer();
}
$uri = 'forums/';
?>

<!-- Now displays the recent post list -->

<?php
    if ($threads = $boards->getThreads()) {
?>
        <div class="row">
  <div class="col-12 col-md-8">
    <h3><?php echo $this->words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?>
    </h3>
  </div><!--  row -->

<?php if (!$noForumNewTopicButton) { ?>
  <div class="col-12 col-md-4 mb-2">
      <a class="btn btn-primary float-right" role="button" href="<?php echo $uri; ?>new"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a><?php echo $this->words->flushBuffer(); ?>
  </div>
<?php
}

require 'boardthreads.php';
?>
        </div>
    <?php }

