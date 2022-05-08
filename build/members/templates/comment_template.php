<div class="row m-1">
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

use Carbon\Carbon;

$purifier = MOD_htmlpure::getBasicHtmlPurifier();
$rights = new MOD_right;
$rights->HasRight('Comments');

function getShowCondition($com,$login){
   // show comment when marked as display in public (default situation)
   if ($com->DisplayInPublic == 1) return 1;
   // show comment to Safety team
   if (MOD_right::get()->HasRight('Comments')) return 2;
   // show comment to writer
   if ($com->UsernameFromMember == $login) return 3;
   // do not show comment
   return false;
}

function getEditCondition($com,$login){

    // don't allow edit bad comment if not marked so
    if ($com->Quality == 'Bad' && $com->AllowEdit != 1) return false;
    // don't allow edit is not logged in as writer
    if ($com->UsernameFromMember != $login) return false;

    // allow edit
    return true;
}

$loginuser = $this->loggedInMember->Username;

if (!$this->passedAway) {
    echo '<div class="col-12 p-0 py-3">'.$words->get('CommentGuidlinesLink').'</div>';
}

$showfrom = false; $showto = false;
foreach ($comments as $com){
    if (isset($com['from'])
        && $com['from']->UsernameFromMember == $loginuser){
            $showfrom = getShowCondition($com['from'],$loginuser);
    }
    // define if to-comment should be shown
    if (isset($com['to'])
        && $com['to']->UsernameToMember == $loginuser){
            $showto = getShowCondition($com['to'],$loginuser);
    }
}

if (!$showfrom && !$this->myself) {
    // Show "Add comment" button
   ?>
        <div class="col-12 p-0 pb-3"><a href="members/<?php echo $username; ?>/comments/add"
                class="btn btn-primary"><?php echo $words->get('addcomments'); ?></a></div>
    <?php
}

foreach($comments as $comment) {

    $showfrom = false; $showto = false; $editfrom = false; $editto = false;

    // define if from-comment should be shown
    if (isset($comment['from'])){
        $showfrom = getShowCondition($comment['from'],$loginuser);
        $editfrom = getEditCondition($comment['from'],$loginuser);
    }
    // define if to-comment should be shown
    if (isset($comment['to'])){
        $showto = getShowCondition($comment['to'],$loginuser);
        $editto = getEditCondition($comment['to'],$loginuser);
    }


    if (isset($comment['from'])) {$c = $comment['from'];}
//            echo $c->UsernameFromMember;
    if ($showfrom || $editfrom) {
        $quality = strtolower($c->comQuality);
        $tt = explode(',', $c->Relations); ?>

        <div class="col-12 col-sm-6 card comment-bg-<?=$quality?> mb-3">
            <div>
                <?php if ($showfrom > 1){echo '<strong>'.$words->get('CommentHiddenEdit').'</strong>'; } ?>
            </div>
            <div class="d-flex flex-row justify-content-between">
                <?php if ($this->loggedInMember){ ?>
                    <div><a href="/members/<?= $this->member->Username;?>/comment/<?php echo $c->id;?>/report" title="<?=$words->getSilent('ReportCommentProblem') ?>" class="gray"><i class="fa fa-flag" alt="<?=$words->getSilent('ReportCommentProblem') ?>"></i></a></div>
                <?php } ?>
                <div><?php if (!$this->passedAway) { ?><p class="h4 m-0 ml-lg-5 <?=$quality?>"><?= $words->get('CommentQuality_'.$c->comQuality.''); ?></p><?php } ?></div>
            <div><span title="<?php echo $c->created; ?>" class="small float-right"><?php echo $layoutbits->ago($c->unix_created); ?></span></div>
            </div>

            <div class="d-flex flex-row justify-content-center">
                <div class="mr-lg-3 text-center">
                    <a href="members/<?= $c->UsernameFromMember ?>">
                        <img class="profileimg" src="members/avatar/<?= $c->UsernameFromMember ?>/48" alt="Profile">
                        <p class="username m-0"><?= $c->UsernameFromMember ?></p></a>
                </div>
                <div class="text-center">
                    <p class="text-mute p-3 m-0 font-italic">
                        <?= $words->get('about') ?>
                    </p>
                    <?php if ($editfrom){ ?>
                        <a class="btn btn-sm btn-primary" href="members/<?= $this->member->Username ?>/comments/add" title="Edit"><?= $ww->edit ?></a>
                    <?php } ?>
                </div>
                <div class="ml-lg-3 text-center">
                    <a href="members/<?= $c->UsernameToMember ?>">
                        <img class="profileimg" src="members/avatar/<?= $c->UsernameToMember ?>/48" alt="Profile">
                        <p class="username m-0"><?= $c->UsernameToMember ?></p></a>
                </div>
            </div>

            <div>
                <?php
                    $created = Carbon::createFromFormat('Y-m-d H:i:s', $c->created);
                    $updated = Carbon::createFromFormat('Y-m-d H:i:s', $c->updated);
                if ($c->created != $c->updated){ ?>
                    <p class="small">(<?=$words->get('CommentLastUpdated')?>: <span title="<?= $c->updated; ?>"><?= $updated->diffForHumans(); ?></span>)</p>
                <?php } ?>

                        <?php
                        for ($jj = 0; $jj < count($tt); $jj++) {
                            // if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
                            echo '<p class="small font-italic p-0 mb-1">', $words->get("comment_" . $tt[$jj]), "</p>\n";
                        }
                        ?>

                <?php echo $purifier->purify(nl2br($c->TextFree)); ?>

                <?php if (MOD_right::get()->HasRight('Comments'))  { ?>
                    <a href="/admin/comment/<?php echo $c->id; ?>"><?=$words->get('EditComment')?></a>
                    <?php } ?>

            </div>
        </div>

    <?php
    } else { ?>
        <div class="col-12 col-sm-6 card comment-bg-neutral mb-3">
            <?php if (!$this->myself && ($c->UsernameToMember==$loginuser)){ ?>
            <a href="members/<?php echo $c->UsernameFromMember; ?>/comments/add"
           class="btn btn-primary mt-3"><?php echo $words->get('addcomments'); ?></a>
            <?php } else { ?>
                <div><p class="text-center mt-3"><?php echo $words->get('CommentNoComment'); ?></p></div>
            <?php } ?>
        </div>
    <?php }

     if ($showto || $editto){
         $cc = $comment['to'];
         $quality = strtolower($cc->comQuality);
         $tt = explode(',', $cc->Relations); ?>
        <div class="col-12 col-sm-6 card comment-bg-<?=$quality?> mb-3">

            <div>
                <?php // give an aditional message when comment is exceptionally shown
                if ($showto > 1){echo '<strong>'.$words->get('CommentHiddenEdit').'</strong>';} ?>
            </div>

            <div class="d-flex flex-row justify-content-between">
                <div>
                    <span class="small" title="<?php echo $cc->created; ?>">
                      <?php echo $layoutbits->ago($cc->unix_created); ?>
                    </span>
                </div>

                <div><?php if (!$this->passedAway) { ?><p class="h4 m-0 mr-lg-5 <?=$quality?>"><?= $words->get('CommentQuality_'.$cc->comQuality.''); ?></p><?php } ?></div>

                <?php if ($this->loggedInMember) :?>
                    <div><a href="members/<?php echo $cc->UsernameToMember;?>/comment/<?php echo $cc->id;?>/report" title="<?=$words->getSilent('ReportCommentProblem') ?>"><i class="fa fa-flag" title="<?=$words->getSilent('ReportCommentProblem') ?>"></i></a></div>
                <?php endif;?>
            </div>

            <div class="d-flex flex-row justify-content-center">
                <div class="mr-lg-3 text-center">
                    <a href="members/<?= $cc->UsernameFromMember ?>">
                        <img class="profileimg" src="members/avatar/<?= $cc->UsernameFromMember ?>/48" alt="Profile">
                        <p class="username m-0"><?= $cc->UsernameFromMember ?></p></a>
                </div>
                <div class="text-center">
                    <p class="text-mute p-3 m-0 font-italic">
                        <?=$words->getSilent('about') ?>
                    </p>
                    <?php if ($editto){ ?>
                    <a class="btn btn-sm btn-primary" role="button" href="members/<?= $cc->UsernameToMember ?>/comments/add" title="Edit"><?= $ww->edit ?></a>
                    <?php } ?>
                </div>
                <div class="ml-lg-3 text-center">
                    <a href="members/<?= $cc->UsernameToMember ?>">
                        <img class="profileimg" src="members/avatar/<?= $cc->UsernameToMember ?>/48" alt="Profile">
                        <p class="username m-0"><?= $cc->UsernameToMember ?></p></a>
                </div>
            </div>

            <div>

             <?php if ($cc->created != $cc->updated){ ?>
                 <p class="small">(<?=$words->get('CommentLastUpdated')?>: <span title="<?= $cc->updated; ?>"><?php echo $layoutbits->ago($cc->unix_updated); ?></span>)</p>
             <?php }

             for ($jj = 0; $jj < count($tt); $jj++) {
                echo '<p class="small font-italic p-0 mb-1">'.$words->get("Comment_" . $tt[$jj]).'</p>';
             }

             echo $purifier->purify(nl2br($cc->TextFree));

             if (MOD_right::get()->HasRight('Comments'))  { ?>
                <a href="/admin/comment/<?php echo $cc->id; ?>"><?=$words->get('EditComment')?></a>
                 <?php } ?>
            </div>
        </div>

<?php
   } else {
         ?>
        <div class="col-12 col-sm-6 card comment-bg-neutral mb-3">

         <?php if ($this->myself && ($c->UsernameToMember==$loginuser)){ ?>
             <a href="members/<?php echo $c->UsernameFromMember; ?>/comments/add"
                class="btn btn-primary mt-3"><?php echo $words->get('addcomments'); ?></a>
            <?php } else { ?>
             <p class="text-center mt-3"><?php echo $words->get('profile.comments.nocomment'); ?></p>
             <?php } ?>
        </div>
        <?php }
    } ?>
</div>

