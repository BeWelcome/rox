<div class="tab-pane fade card" id="myinterests" role="tabpanel" aria-labelledby="myinterests-tab">
    <div class="card-header" role="tab" id="heading-myinterests">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-myinterests" data-parent="#content" aria-expanded="true" aria-controls="collapse-myinterests">
                <?= $words->get('ProfileInterests') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-myinterests" class="collapse" role="tabpanel" aria-labelledby="heading-myinterests">
        <div class="card-body">
            <div class="form-group row">
                <label for="Hobbies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileHobbies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Hobbies" class="o-input" rows="3"><?= $vars['Hobbies'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Books" class="col-md-2 col-form-label">
                        <?= $words->get('ProfileBooks') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Books" class="o-input" rows="3"><?= $vars['Books'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Music" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMusic') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Music" class="o-input" rows="3"><?= $vars['Music'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Movies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMovies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Movies" class="o-input" rows="3"><?= $vars['Movies'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Organizations" class="col-md-2 col-form-label">
                        <?= $words->get('ProfileOrganizations') ?>
                </label>
                <div class="col-12 col-md-10">
                                <textarea name="Organizations" class="o-input"
                                          rows="3"><?= $vars['Organizations'] ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mt-3">
                    <input type="submit" class="btn btn-primary float-right m-2" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
