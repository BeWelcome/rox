<div class="d-lg-block d-none mb-sm-3 mb-lg-0">
    <?php

if (!$this->passedAway){
    $accIdSuffix = 'Right';
    require 'profile.subcolumn_accommodation.php';
}

?>
</div>
<?

    $comments = $this->member->comments;
    $username = $this->member->Username;
    $layoutbits = new MOD_layoutbits();

    $max = 3;
    if (count($comments) > 0) { ?>

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
                  if ($this->model->getLoggedInMember()){
                   $tt = array ();
                   $commentLoopCount = 0;
                   foreach ($comments as $c) {
                   // skip items that are hidden for public
                   if ($c->DisplayInPublic == 0) {continue;}
                   // stop looping when maximum has been reached
                   if (++$commentLoopCount>$max){break;}
                   $quality = "neutral";
                   if ($c->comQuality == "Good") {
                   $quality = "good";
                   }
                   if ($c->comQuality == "Bad") {
                   $quality = "bad";
                   } ?>

                       <? if ($commentLoopCount > 1){ ?><hr><? } ?>
                       <div class="w-100 mt-1">
                           <a href="members/<?=$c->UsernameFromMember?>">
                               <img class="float-left mr-2"  src="members/avatar/<?=$c->UsernameFromMember?>/50"  height="50"  width="50"  alt="<?=$c->UsernameFromMember?>" />
                           </a>
                           <div>
                               <p class="m-0" style="line-height: 1.1;">
                                   <?php if (!$this->passedAway) { ?>
                                       <span class="commenttitle <?=$quality?>"><?=$c->comQuality?></span><br>
                                   <?php }?>
                                   <span class="small grey"><?=$words->get('CommentFrom','<a href="members/'.$c->UsernameFromMember.'">'.$c->UsernameFromMember.'</a>')?><br><?=$c->created?></span>
                               </p>
                           </div>
                       </div>
                           <div class="w-100 pt-2">
                               <p class="mb-1">
                                   <?php
                                   $textStripped = strip_tags($c->TextFree, '<font>');
                                   echo $textStripped;
                                   ?>
                               </p>
                           </div>


                   <?php } ?>

                <?php
                  } else {
                      // hide comments from others when not logged in
                      echo $this->getLoginLink('/members/' . $member->Username,'ProfileShowComments');
                  } ?>
            </div>
            <a href="members/<?=$member->Username?>/comments/" class="btn btn-block btn-sm btn-outline-dark"><?=$words->get('ShowAllComments')?></a>
        </div>

<?php }

/**********************
 ** Profile Relations **
 **********************/

$purifier = MOD_htmlpure::getBasicHtmlPurifier();
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

                            $rel->Comment = $purifier->purify($comment);
                            ?>
                            <div class="d-flex d-column w-100">
                                <div>
                                    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
                                        <img class="framed float-left"  src="members/avatar/<?=$rel->Username?>/50"  height="50"  width="50"  alt="Profile" />
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
      <a class="btn btn-sm btn-block btn-outline-dark" href="gallery/show/user/<?php echo $member->Username;?>/images" title="<?php echo $words->getSilent('GalleryTitleLatest');?>">
          <?php echo $words->get('GalleryShowAll');?></a>
    </div>
    <?php echo $words->flushBuffer();
}
