<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 08.04.14
 * Time: 20:48
 */
?>
<div class="card-columns">
<?php foreach($this->rights as $right) : ?>
    <div class="card p-2">
        <p class="h5"><?= $right->Name ?></p>
        <p class="small"><?= $right->Description ?></p>
    </div>
<?php endforeach; ?>
</div>

