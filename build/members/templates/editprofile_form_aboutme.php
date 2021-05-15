<div class="card">
    <div class="card-header" id="heading-aboutme">
        <a data-toggle="collapse" href="#collapse-aboutme" aria-expanded="false"
           aria-controls="collapse-aboutme" class="mb-0 d-block collapsed">
            <?= $words->get('ProfileSummary') ?>
        </a>
    </div>
    <div id="collapse-aboutme" class="collapse" data-parent="#editProfile" aria-labelledby="heading-aboutme">
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
            </div>
        </div>
    </div>
</div>
