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

$purifier = MOD_htmlpure::getBasicHtmlPurifier();
$rights = new MOD_right;
$rights->HasRight('Comments');
if (!$this->passedAway) { echo $words->get('CommentGuidlinesLink') . '<br /><br />'; }
if (!$this->myself && !$comment_to_self_exists  && $this->loggedInMember) {

      // Show "Add comment" button
      echo '  <p class="floatbox"><a href="members/' . $username
          . '/comments/add" class="button">' . $words->get('addcomments')
          . '</a></p>' . "\n";
    }

foreach($comments as $comment) {
    
    // define if from-comment should be shown
    $showfrom = false;
    $editfrom = false;
    if (isset($comment['from'])){
        if ($comment['from']->DisplayInPublic == 1){$showfrom = true;}
        if ($comment['from']->AllowEdit == 1){$editfrom = true;}
    }

    // define if to-comment should be shown
    $showto = false;
    $editto = false;
    if (isset($comment['to'])){
        if ($comment['to']->DisplayInPublic == 1){$showto = true;}
        if ($comment['to']->AllowEdit == 1){$editto = true;}
    }
    
if($showfrom || $editfrom || $showto || $editto) {
    echo '<div class="frame">';
} else { 
    echo '<div>';
}    ?>
<div class="subcolumns profilecomment">
    <?php if ($showfrom || $editfrom) { 
        $c = $comment['from']; 
        $quality = strtolower($c->comQuality); 
        $tt = explode(',', $c->Lenght); ?>
    <div class="c75l" >
      <div class="subcl" >
      <?php if ($editfrom && !$showfrom ){echo $words->get('CommentHiddenEdit');} ?>
        <a href="members/<?=$c->UsernameFromMember?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->UsernameFromMember?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p class="floatbox">

              <?php if (!$this->passedAway) {?><strong class="<?=$quality?>"><?=$c->comQuality?></strong><br/><?php }?>
              <span class="small grey">
                <?=$words->get('CommentFrom','<a href="members/'.$c->UsernameFromMember.'">'.$c->UsernameFromMember.'</a>')?> <?= $words->get('CommentTo') ?> <a href="members/<?= $c->UsernameToMember ?>"><?= $c->UsernameToMember ?></a> -
                <span title="<?php echo $c->created; ?>">
                  <?php echo $layoutbits->ago($c->unix_created); ?>
                </span>
                <?php if ($c->created != $c->updated): ?>
                  (<?=$words->get('CommentLastUpdated')?>: <span title="<?php echo $c->updated; ?>"><?php echo $layoutbits->ago($c->unix_updated); ?></span>)
                <?php endif; ?>
              </span>
              <?php if ($this->loggedInMember) :?>
                <a class="flagbutton" href="members/reportcomment/<?php echo $this->member->Username;?>/<?php echo $c->id;?>" title="<?=$words->getSilent('ReportCommentProblem') ?>"><img src="images/icons/noun_project_flag.png" alt="<?=$words->getSilent('ReportCommentProblem') ?>"></a><?php echo $words->flushBuffer(); ?>
              <?php endif;?>
            </p>
            <p>
              <em><?php echo $purifier->purify(nl2br($c->TextWhere)); ?></em>
            </p>
            <p>
              <?php echo $purifier->purify(nl2br($c->TextFree)); ?>
            </p>
            <p>
              <?php if ($this->loggedInMember && $c->IdFromMember == $this->loggedInMember->id): ?>
                <a class="button small" href="members/<?= $this->member->Username ?>/comments/add" title="Edit"><?= $ww->edit ?></a>
              <? endif; ?>
            </p>
        </div> <!-- comment -->
      </div> <!-- subcl -->
    </div> <!-- c75l -->
    <div class="c25r" >
      <div class="subcr" >
        <ul class="linklist" >
            <li>
                <?php
                    for ($jj = 0; $jj < count($tt); $jj++) {
                        if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
                        echo "                    <li>", $words->get("Comment_" . $tt[$jj]), "</li>\n";
                    }
                ?>
            </li>
            <li>
            <?php if (MOD_right::get()->HasRight('Comments'))  { ?>
                <a href="bw/admin/admincomments.php?action=editonecomment&IdComment=<?php echo $c->id; ?>"><?=$words->get('EditComment')?></a>
                <?php
                };
                ?>
            </li>
        </ul>
      </div> <!-- subcr -->
    </div> <!-- c25r -->
    <?php 
    } elseif ($showto || $editto){
    // profileowner has no comment given but did get a comment
        $cc = $comment['to'];?>
    <div class="c50l" >
      <div class="subcl" >
        <a href="members/<?=$cc->UsernameToMember?>">
           <img class="float_left framed"  src="members/avatar/<?=$cc->UsernameToMember?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p class="floatbox">
              <strong class="neutral"><?php echo $words->get('CommentNoComment');?></strong><br/>
              <span class="small grey">
                <?=$words->get('CommentFrom','<a href="members/'.$cc->UsernameToMember.'">'.$cc->UsernameToMember.'</a>')?> <?= $words->get('CommentTo') ?> <a href="members/<?= $cc->UsernameFromMember ?>"><?= $cc->UsernameFromMember ?></a>
              </span>
        </div>
      </div>
   </div>
    <div class="c50r" >
      <div class="subcr" >
      <?php if ($this->loggedInMember && !$this->myself && $cc->IdToMember == $this->loggedInMember->id) { ?>
      <p class="float_right"><a href="members/<?php echo $username; ?>/comments/add" 
         class="button"><?php echo $words->get('addcomments'); ?></a></p>
      <?php } ?>
      </div>
    </div>
<?php
   }
   ?>
  </div> <!-- subcolumns -->
  <?php 
    if ($showto || $editto) {
        $cc = $comment['to'];
        $quality = strtolower($cc->comQuality);
        $tt = explode(',', $cc->Lenght); ?> 
  <div class="profilecomment floatbox counter">
      <div class="subcolumns profilecomment">

        <div class="c75l" >
          <div class="subcl" >
            <?php // give an aditional message when comment is shown for the reason that it has to be edited
            if (!$showto && $editto){echo $words->get('CommentHiddenEdit');}
            ?>

            <a href="members/<?= $cc->UsernameFromMember ?>">
               <img class="float_left framed" src="members/avatar/<?= $cc->UsernameFromMember ?>/?xs" height="50px" width="50px" alt="Profile" />
            </a>
            <div class="comment">
                <p class="floatbox">
                  <strong class="<?=$cc->comQuality?>"><?=$cc->comQuality?></strong><br/>
                  <span class="small grey">
                    <?= $words->get('CommentFrom', '<a href="members/' . $cc->UsernameFromMember . '">' . $cc->UsernameFromMember . '</a>') ?> <?= $words->get('CommentTo') ?> <a href="members/<?= $cc->UsernameToMember ?>"><?= $cc->UsernameToMember ?></a>
                    <br>
                    <span title="<?php echo $cc->created; ?>">
                      <?php echo $layoutbits->ago($cc->unix_created); ?>
                    </span>
                    <?php if ($cc->created != $cc->updated): ?>
                      (<?=$words->get('CommentLastUpdated')?>: <span title="<?php echo $cc->updated; ?>"><?php echo $layoutbits->ago($cc->unix_updated); ?></span>)
                    <? endif; ?>
                  </span>
                  <?php if ($this->loggedInMember) :?>
                    <a class="flagbutton" href="members/reportcomment/<?php echo $cc->UsernameToMember; ?>/<?php echo $cc->id;?>/<?php echo $cc->UsernameFromMember; ?>" title="<?=$words->getSilent('ReportCommentProblem') ?>"><img src="images/icons/noun_project_flag.png" alt="<?=$words->getSIlent('ReportCommentProblem') ?>"></a><?php echo $words->flushBuffer(); ?>
                  <?php endif;?>
                </p>
                <p>
                  <em><?php echo $purifier->purify(nl2br($cc->TextWhere)); ?></em>
                </p>
                <p>
                  <?php echo $purifier->purify(nl2br($cc->TextFree)); ?>
                </p>
                <p>
                  <? if ($this->myself && $this->loggedInMember && $cc->IdFromMember == $this->loggedInMember->id): ?>
                    <a class="button" href="members/<?= $cc->UsernameToMember ?>/comments/add" title="Edit"><?= $ww->edit ?></a>
                  <? endif; ?>
                </p>
            </div> <!-- comment -->
          </div> <!-- subcl -->
        </div> <!-- c75l -->
        <div class="c25r" >
          <div class="subcr" >
            <ul class="linklist" >
                <li>
                    <?php
                        for ($jj = 0; $jj < count($tt); $jj++) {
                            if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
                            echo "                    <li>", $words->get("Comment_" . $tt[$jj]), "</li>\n";
                        }
                    ?>
                </li>
                <li>
                <?php if (MOD_right::get()->HasRight('Comments'))  { ?>
                    <a href="bw/admin/admincomments.php?action=editonecomment&IdComment=<?php echo $cc->id; ?>"><?=$words->get('EditComment')?></a>
                    <?php
                    };
                    ?>
                </li>
            </ul>
          </div> <!-- subcr -->
        </div> <!-- c25r -->
      </div> <!-- subcolumns -->
  </div> <!-- profilecomment counter -->
<?php } else {
if ($this->myself && $showfrom) {
    $cc = $comment['from'] ?>
    <div class="subcolumns profilecomment">
    <p class="float_right"><a class="button" href="members/<?= $cc->UsernameFromMember?>/comments/add"
        title="<? echo $words->getBuffered('CommentAddComment'); ?>"><?= $words->get('CommentAddComment'); ?></a></p>
        </div>
<?php 
    }
} ?>
</div>
<hr>
<?php
}
?>