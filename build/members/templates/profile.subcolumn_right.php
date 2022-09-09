<?php
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
?>

<div class="d-lg-block d-none mb-sm-3 mb-lg-0">
    <?php

    use Carbon\Carbon;

    if (!$this->passedAway){
    $accIdSuffix = 'Right';
    require 'profile.subcolumn_accommodation.php';
}

?>
</div>
<?php

    // build array with combined comments
    $comments = [];
    $commentsReceived = $this->member->comments;
    $commentsWritten = $this->member->comments_written;

    foreach ($commentsReceived as $key => $value) {
        $comments[$key] = [
            'from' => $value,
        ];
    }
    foreach ($commentsWritten as $key => $value) {
        if (isset($comments[$key])) {
            $comments[$key] = array_merge($comments[$key], [
                'to' => $value,
            ]);
        }
    }

    // \todo: do something here
    $username = $this->member->Username;
    $layoutbits = new MOD_layoutbits();

    $max = 5;
    if (count($comments) > 0) {

        ?>
        <div id="comments" class="card mb-3">
            <h3 class="card-header bg-secondary">
                <?php if ($this->passedAway) {
                    echo $words->get('LatestCommentsAndCondolences');
                } else {
                    echo $words->get('LatestComments');
                }
                if ($showEditLinks): ?>
                    <span class="float-right">
                    <a href="members/<?php echo $member->Username; ?>/comments/" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
                <?php endif; ?>
            </h3>
            <div class="p-2">

                <?php
                   $tt = array ();
                   $commentLoopCount = 0;
                   foreach ($comments as $key => $c) {
                       // stop looping when maximum has been reached
                       if (++$commentLoopCount>$max){break;}
?>

                       <?php if (isset($c['from'])) {
                           $comment = $c['from'];
                           // skip items that are hidden for public
                           if ($comment->DisplayInPublic == 0) {continue;}
                           $quality = "neutral";
                           if ($comment->comQuality == "Good") {
                               $quality = "good";
                           }
                           if ($comment->comQuality == "Bad") {
                               $quality = "bad";
                           }                            ?>
                       <div class="comment-bg-<?=$quality?> p-2 mt-1 <?= (!isset($c['to'])) ? 'mb-2' : '' ?> ">
                           <div class="my-1 clearfix">
                               <a href="members/<?=$comment->UsernameFromMember?>">
                                   <img class="float-left mr-2 profileimg avatar-48"  src="members/avatar/<?=$comment->UsernameFromMember?>/48" alt="<?=$comment->UsernameFromMember?>" />
                               </a>
                               <div>
                                   <p class="m-0" style="line-height: 1.0;">
                                       <?php if (!$this->passedAway) { ?>
                                           <span class="commenttitle <?=$quality?>"><?= $words->get('CommentQuality_'.$comment->comQuality.''); ?></span>
                                           <span class="float-right">
                                       <?php if ($this->loggedInMember){ ?>
                                           <a href="/members/<?= $this->member->Username;?>/comment/<?php echo $comment->id;?>/report" title="<?=$words->getSilent('ReportCommentProblem') ?>" class="gray"><i class="fa fa-flag" alt="<?=$words->getSilent('ReportCommentProblem') ?>"></i></a>
                                       <?php } ?>
                                   </span>
                                       <?php }?>
                                       <br><small><?=$words->get('CommentFrom','<a href="members/'.$comment->UsernameFromMember.'">'.$comment->UsernameFromMember.'</a>')?></small>
                                       <br><small><span title="<?=$comment->created?>"><?php
                                               $created = Carbon::createFromFormat('Y-m-d H:i:s', $comment->created);
                                               echo $created->diffForHumans();
                                               ?></span></small>
                                   </p>
                               </div>
                           </div>
                           <div class="w-100 pt-2">
                               <p class="mb-1 js-read-more">
                                   <?php
                                   echo htmlentities($comment->TextFree);
                                   ?>
                               </p>
                           </div>
                       </div>
                       <?php }

                       if (isset($c['to'])) {
                           $comment = $c['to'];
                           // skip items that are hidden for public
                           if ($comment->DisplayInPublic == 0) {continue;}
                           $quality = "neutral";
                           if ($comment->comQuality == "Good") {
                               $quality = "good";
                           }
                           if ($comment->comQuality == "Bad") {
                               $quality = "bad";
                           }                           ?>

                       <div class="comment-bg-<?=$quality?> p-2 mt-1 <?= !(isset($c['from'])) ? 'mt-1' : '' ?> "">
                           <div class="d-flex flex-row justify-content-end">
                               <div class="mr-2">
                                   <p class="m-0 text-right" style="line-height: 1.0;">
                                           <span class="commenttitle <?=$quality?>"><?= $words->get('CommentQuality_'.$comment->comQuality.''); ?></span>
                                       <br><small><?= $words->get('CommentTo'); ?> <a href="members/<?= $comment->UsernameToMember ?>"><?= $comment->UsernameToMember; ?></a></small>
                                       <br><small><span title="<?=$comment->created?>"><?php
                                               $created = Carbon::createFromFormat('Y-m-d H:i:s', $comment->created);
                                               echo $created->diffForHumans();
                                               ?></span></small>
                                   </p>
                               </div>
                               <div>
                                   <a href="members/<?=$comment->UsernameToMember?>">
                                    <img class="mr-2 profileimg avatar-48"  src="members/avatar/<?=$comment->UsernameToMember?>/48" alt="<?=$comment->UsernameToMember?>" />
                                </a>
                               </div>
                           </div>
                           <div class="w-100 pt-2">
                               <p class="mb-1 js-read-more">
                                   <?php
                                   echo htmlentities($comment->TextFree);
                                   ?>
                               </p>
                           </div>
                       </div>

                      <?php
                      }
                      echo '<hr class="my-2">';
                   }
                 ?>
            </div>
            <a href="members/<?=$member->Username?>/comments/" class="btn btn-block btn-sm btn-outline-primary"><?=$words->get('ShowAllComments')?><span class="badge badge-primary"><?php echo count($comments) - 1; ?></span></a>
        </div>
<?php }

/**********************
 ** Profile Relations **
 **********************/

$relations = $member->relations;
if (count($relations) > 0) { ?>


    <div id="relations" class="card mb-3">
        <h3 class="card-header bg-secondary"><?php echo $words->get('MyRelations'); ?>
            <?php if ($showEditLinks): ?>
                <span class="float-right">
                    <a href="editmyprofile/<?php echo $profile_language_code; ?>#!specialrelations" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                </span>
            <?php endif; ?>
        </h3>
        <div class="p-2">
                <?php   if ($this->model->getLoggedInMember()){ ?>

                        <?php
                        foreach ($relations as $rel) {
                            $comment = $words->mInTrad($rel->IdTradComment, $profile_language, true);

                            // Hack to filter out accidental '0' or '123456' comments that were saved
                            // by users while relation comment update form was buggy (see #1580)
                            if (is_numeric($comment)) {
                                $comment = '';
                            }

                            $rel->Comment = $purifier->purify(stripslashes($comment));
                            ?>
                            <div class="d-flex d-column w-100">
                                <div>
                                    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
                                        <img class="float-left profileimg avatar-48"  src="members/avatar/<?=$rel->Username?>/48" alt="Profile" />
                                    </a>
                                </div>
                                <div>
                                    <a class="float-left" href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
                                    <br>
                                    <?php echo $rel->Comment; ?>
                                </div>
                            </div>
                        <?php } ?>
                <?php } else {
                    echo $this->getLoginLink('/members/' . $member->Username,'ProfileShowRelations');
                }?>
            </div>
        </div>

<?php }

/**********************
** Profile Gallery  **
**********************/

$userid = $member->id;
$gallery = new GalleryModel;
$statement = $userid ? $gallery->getLatestItems($userid) : false;
if ($statement) {
?>
    <div id="gallery" class="card mb-3">
        <h3 class="card-header bg-secondary"><?php echo $words->get('GalleryTitleLatest'); ?>
            <?php if ($showEditLinks): ?>
                <span class="float-right">
                        <a href="/gallery/manage" class="btn btn-sm btn-secondary p-0"><?php echo $words->get('Edit'); ?></a>
                    </span>
            <?php endif; ?>
        </h3>

        <div class="p-2 d-flex flex-wrap justify-content-around">

    <?php
    // if the gallery is NOT empty, go show it
    $p = PFunctions::paginate($statement, 1, $itemsPerPage = 8);
    $statement = $p[0];

    foreach ($statement as $d) {
        echo '<div><a href="gallery/show/image/'.$d->id.'">' .
           '<img src="gallery/thumbimg?id='.$d->id.'"' .
               ' alt="image"' .
               ' style="height: 50px; width: 50px; margin: 1rem;"/>' .
           '</a></div>';
    }
    ?>
        </div>
      <a class="btn btn-sm btn-block btn-outline-primary" href="gallery/show/user/<?php echo $member->Username;?>/images" title="<?php echo $words->getSilent('GalleryTitleLatest');?>">
          <?php echo $words->get('GalleryShowAll');?></a>
    </div>
    <?php echo $words->flushBuffer();
}
