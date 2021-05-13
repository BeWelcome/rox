<div class="card">
    <div class="card-header" id="heading-myinterests">
        <a data-toggle="collapse" href="#collapse-myinterests" aria-expanded="false"
           aria-controls="collapse-myinterests" class="mb-0 d-block collapsed">
            <?= $words->get('ProfileInterests') ?>
        </a>
    </div>
    <div id="collapse-myinterests" class="collapse" data-parent="#editProfile" aria-labelledby="heading-myinterests">
        <div class="card-body">
            <div class="form-group row">
                <label for="Hobbies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileHobbies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Hobbies" class="form-control" rows="3"><?= $vars['Hobbies'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Books" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileBooks') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Books" class="form-control" rows="3"><?= $vars['Books'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Music" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMusic') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Music" class="form-control" rows="3"><?= $vars['Music'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Movies" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileMovies') ?>
                </label>
                <div class="col-12 col-md-10">
                    <textarea name="Movies" class="form-control" rows="3"><?= $vars['Movies'] ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="Organizations" class="col-md-2 col-form-label">
                    <?= $words->get('ProfileOrganizations') ?>
                </label>
                <div class="col-12 col-md-10">
                                <textarea name="Organizations" class="form-control"
                                          rows="3"><?= $vars['Organizations'] ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
