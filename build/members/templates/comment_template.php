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
   
foreach($comments as $comment) {
if(isset($comment['from'])) {
    echo '<div class="frame">';
} else { 
    echo '<div>';
}    ?>
<div class="subcolumns profilecomment">
    <?php if (isset($comment['from'])) { 
        $c = $comment['from']; 
        $quality = strtolower($c->comQuality); 
        $tt = explode(',', $c->Lenght); ?>
    <div class="c75l" >
      <div class="subcl" >
        <a href="members/<?=$c->UsernameFromMember?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->UsernameFromMember?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p class="floatbox">
              <strong class="<?=$quality?>"><?=$c->comQuality?></strong><br/>
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
                <a class="flagbutton" href="members/reportcomment/<?php echo $this->member->Username;?>/<?php echo $c->id;?>" title="<?=$ww->ReportCommentProblem ?>"><img src="images/icons/noun_project_flag.png" alt="<?=$ww->ReportCommentProblem ?>"></a>
              <?php endif;?>
            </p>
            <p>
              <em><?php echo $purifier->purify(nl2br($c->TextWhere)); ?></em>
            </p>
            <p>
              <?php echo $purifier->purify(nl2br($c->TextFree)); ?>
            </p>
            <p>
              <? if ($this->loggedInMember && $c->IdFromMember == $this->loggedInMember->id): ?>
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
    } else { 
        $cc = $comment['to'];?>
    <div class="c75l" >
      <div class="subcl" >
        <a href="members/<?=$cc->UsernameToMember?>">
           <img class="float_left framed"  src="members/avatar/<?=$c->UsernameToMember?>/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <div class="comment">
            <p class="floatbox"><strong class="neutral"><?php echo $words->get('CommentNoComment'); ?></strong><br />
              <span class="small grey">
                <?=$words->get('CommentFrom','<a href="members/'.$cc->UsernameToMember.'">'.$cc->UsernameToMember.'</a>')?> <?= $words->get('CommentTo') ?> <a href="members/<?= $cc->UsernameFromMember ?>"><?= $cc->UsernameFromMember ?></a>
              </span>
            </p>
        </div>
      </div>
    </div>
    <?php
    }
    ?>
  </div> <!-- subcolumns -->
  <?php 
    if (isset($comment['to'])) {
        $cc = $comment['to'];
        $quality = strtolower($cc->comQuality);
        $tt = explode(',', $cc->Lenght); ?> 
  <div class="profilecomment floatbox counter">
      <div class="subcolumns profilecomment">

        <div class="c75l" >
          <div class="subcl" >
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
                    <a class="flagbutton" href="members/reportcomment/<?php echo $cc->UsernameToMember; ?>/<?php echo $cc->id;?>/<?php echo $cc->UsernameFromMember; ?>" title="<?=$ww->ReportCommentProblem ?>"><img src="images/icons/noun_project_flag.png" alt="<?=$ww->ReportCommentProblem ?>"></a>
                  <?php endif;?>
                </p>
                <p>
                  <em><?php echo $purifier->purify(nl2br($cc->TextWhere)); ?></em>
                </p>
                <p>
                  <?php echo $purifier->purify(nl2br($cc->TextFree)); ?>
                </p>
                <p>
                  <? if ($this->loggedInMember && $cc->IdFromMember == $this->loggedInMember->id): ?>
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
<?php } ?>
</div>
<hr>
<?php
}
?>