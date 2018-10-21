<div class="tab-pane fade card" id="travel" role="tabpanel" aria-labelledby="travel-tab">
    <div class="card-header" role="tab" id="heading-travel">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-travel" data-parent="#content" aria-expanded="true" aria-controls="collapse-travel">
                <?= $words->get('ProfileTravelExperience') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-travel" class="collapse" role="tabpanel" aria-labelledby="heading-travel">
        <div class="card-body">
            <div class="form-row">
                <label for="PastTrips" class="col-md-3 col-form-label">
                    <?= $words->get('ProfilePastTrips') ?>
                </label>
                <div class="col-12 col-md-9">
                    <textarea name="PastTrips" class="form-control" rows="3"><?= $vars['PastTrips'] ?></textarea>
                </div>
            </div>

            <div class="form-row mt-2">
                <label for="PlannedTrips" class="col-md-3 col-form-label">
                    <?= $words->get('ProfilePlannedTrips') ?>
                </label>
                <div class="col-12 col-md-9">
                                <textarea name="PlannedTrips" class="form-control"
                                          rows="3"><?= $vars['PlannedTrips'] ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" id="submit" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>