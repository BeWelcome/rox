<div class="tab-pane fade card" id="family" role="tabpanel" aria-labelledby="family-tab">
    <div class="card-header" role="tab" id="heading-family">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-family" data-parent="#content" aria-expanded="true" aria-controls="collapse-family">
                <?= $words->get('MyRelations') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-family" class="collapse" role="tabpanel" aria-labelledby="heading-family">
        <div class="card-body">
            <?php
            $Relations = $vars['Relations'];
            foreach ($Relations as $Relation) {
                $comment = $words->mInTrad($Relation->Comment, $profile_language);
                if (is_numeric($comment)) {
                    $comment = '';
                }
                ?>
                <div class="row mb-3">

                    <div class="col-10 col-md-2 order-sm-2">
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
                    <div class="col-2 col-md-1 order-sm-1 pt-2">
                        <a href="/members/<?php echo $member->Username; ?>/relations/delete/<?php echo $Relation->id; ?>?redirect=editmyprofile#!specialrelations"
                           class="btn btn-danger"
                           onclick="return confirm('<?php echo $words->getSilent('Relation_delete_confirmation'); ?>');"><i class="fa fa-times" title="<?php echo $words->getFormatted("delrelation", $Relation->Username); ?>"></i><?php echo $words->flushBuffer(); ?></a>
                    </div>
                    <div class="col-12 col-md-9 order-sm-3">
                                    <textarea class="w-100"
                                              name="RelationComment_<?= $Relation->id ?>"><?= $comment ?></textarea>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" id="submit" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>