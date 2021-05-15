<div class="card">
    <div class="card-header" id="heading-family">
        <a data-toggle="collapse" href="#collapse-family" aria-expanded="false"
           aria-controls="collapse-family" class="mb-0 d-block collapsed">
            <?= $words->get('MyRelations') ?>
        </a>
    </div>
    <div id="collapse-family" class="collapse" data-parent="#editProfile" aria-labelledby="heading-family">
        <div class="card-body">
            <?php
            $Relations = $vars['Relations'];
            foreach ($Relations as $Relation) {
                $comment = $words->mInTrad($Relation->Comment, $profile_language);
                if (is_numeric($comment)) {
                    $comment = '';
                }
                ?>
                <div class="form-row mb-3">

                    <div class="col-10 col-md-2 order-md-2">
                        <a href="members/<?= $Relation->Username ?>"><img
                                src="members/avatar/<?= $Relation->Username ?>/50"
                                height="50" width="50"
                                alt="Profile"/></a><br>
                        <span class="small
                        <?php
                        if ($Relation->Confirmed == 'Yes') {
                            echo ' font-weight-bold';
                        }
                        ?>
                        "><a href="members/<?= $Relation->Username ?>"><?= $Relation->Username ?></a></span>
                    </div>
                    <div class="col-2 col-md-1 order-md-1 pt-2">
                        <a href="/members/<?php echo $member->Username; ?>/relations/delete/<?php echo $Relation->id; ?>/editprofile"
                           class="btn btn-danger"
                           onclick="return confirm('<?php echo $words->getSilent('Relation_delete_confirmation'); ?>');"><i
                                class="fa fa-times"
                                title="<?php echo $words->getFormatted("delrelation", $Relation->Username); ?>"></i><?php echo $words->flushBuffer(); ?>
                        </a>
                    </div>
                    <div class="col-12 col-md-9 order-md-3">
                                    <textarea class="form-control"
                                              name="RelationComment_<?= $Relation->id ?>"><?= $comment ?></textarea>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
