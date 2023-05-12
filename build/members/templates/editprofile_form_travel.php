<div class="card">
    <div class="card-header" id="heading-travel">
        <a data-toggle="collapse" href="#collapse-travel" aria-expanded="false"
           aria-controls="collapse-travel" class="mb-0 d-block collapsed">
            <?= $words->get('ProfileTravelExperience') ?>
        </a>
    </div>
    <div id="collapse-travel" class="collapse" data-parent="#editProfile" aria-labelledby="heading-travel">
        <div class="card-body">
            <div class="o-form-group row  mb-2">
                <label for="PastTrips" class="col-md-3 col-form-label">
                    <?= $words->get('ProfilePastTrips') ?>
                </label>
                <div class="col-12 col-md-9">
                    <textarea name="PastTrips" class="o-input" rows="3"><?= $vars['PastTrips'] ?></textarea>
                </div>
            </div>

            <div class="o-form-group row mb-2">
                <label for="PlannedTrips" class="col-md-3 col-form-label">
                    <?= $words->get('ProfilePlannedTrips') ?>
                </label>
                <div class="col-12 col-md-9">
                                <textarea name="PlannedTrips" class="o-input"
                                          rows="3"><?= $vars['PlannedTrips'] ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
