<div class="card">
    <div class="card-header" id="heading-myinterests">
        <a data-toggle="collapse" href="#collapse-myinterests" aria-expanded="false"
           aria-controls="collapse-myinterests" class="mb-0 d-block collapsed">
            <?= $words->get('ProfileInterests') ?>
        </a>
    </div>
    <div id="collapse-myinterests" class="collapse" data-parent="#editProfile" aria-labelledby="heading-myinterests">
        <div class="card-body">
            <div class="o-form-group row mb-2">
                <label for="Hobbies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileHobbies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea id="Hobbies" name="Hobbies" class="o-input" rows="3"><?= $vars['Hobbies'] ?></textarea>
                </div>
            </div>

            <div class="o-form-group row mb-2">
                <label for="Books" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileBooks') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea id="Books" name="Books" class="o-input" rows="3"><?= $vars['Books'] ?></textarea>
                </div>
            </div>

            <div class="o-form-group row mb-2">
                <label for="Music" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMusic') ?>
                </label>
                <div class="col-12 col-md-10 mb-2">
                    <textarea id="Music" name="Music" class="o-input" rows="3"><?= $vars['Music'] ?></textarea>
                </div>
            </div>

            <div class="o-form-group row mb-2">
                <label for="Movies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMovies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea id="Movies" name="Movies" class="o-input" rows="3"><?= $vars['Movies'] ?></textarea>
                </div>
            </div>

            <div class="o-form-group row mb-2">
                <label for="Organizations" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileOrganizations') ?>
                </label>
                <div class="col-12 col-md-10">
                                <textarea id="Organizations" name="Organizations" class="o-input"
                                          rows="3"><?= $vars['Organizations'] ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
