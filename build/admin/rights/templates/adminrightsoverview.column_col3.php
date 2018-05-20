<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 08.04.14
 * Time: 20:48
 */
?>

<?php foreach($this->rights as $right) : ?>
    <div class="col-12 col-md-6 col-lg-4 p-1">
        <div class="card p-2">
            <p class="h5"><?= $right->Name ?></p>
            <p class="small"><?= $right->Description ?></p>
        </div>
    </div>
<?php endforeach; ?>

