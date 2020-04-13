<div class="tab-pane fade card" id="aboutme" role="tabpanel" aria-labelledby="aboutme-tab">
    <div class="card-header" role="tab" id="heading-aboutme">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-aboutme" data-parent="#content" aria-expanded="true" aria-controls="collapse-aboutme">
                <?= $words->get('ProfileSummary') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-aboutme" class="collapse" role="tabpanel" aria-labelledby="heading-aboutme">
        <div class="card-body">
            <div class="form-group row">
                <label for="Occupation" class="col-md-2 col-form-label"><?= $words->get('ProfileOccupation') ?></label>
                <div class="col-12 col-md-10"><input class="form-control" name="Occupation"
                                                     value="<?php echo htmlentities($vars['Occupation'], ENT_COMPAT, 'UTF-8'); ?>"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="ProfileSummary" class="col-md-2 col-form-label"><?= $words->get('ProfileSummary') ?></label>

                <div class="col-12 col-md-10"><textarea name="ProfileSummary" id="ProfileSummary" class="form-control"
                                                        rows="6"><?php echo htmlentities($vars['ProfileSummary'], ENT_COMPAT, 'UTF-8'); ?></textarea>
                </div>
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
