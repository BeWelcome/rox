        <div class="card">
            <a id="link_<?= $d->id ?>" href="gallery/img?id=<?= $d->id ?>" class="p-1" title="<?= $d->title ?>" rel="image"
               data-toggle="lightbox" data-type="image">
                <img src="gallery/img?id=<?= $d->id ?>"
                     class="card-img-top" alt="<?= $d->title ?>">
            </a>
            <?php $title_short = ((strlen($d->title) >= 26) ? substr($d->title, 0, 20) . '...' : $d->title); ?>
            <div class="card-body"
            <h6 class="card-title">
                <a href="gallery/img?id=<?= $d->id ?>" title="<?= $d->title ?>" data-toggle="lightbox"
                   data-type="image"><?= $title_short ?></a>
            </h6>
            <p class="card-text"><?= $d->description ?></p>
        </div>
