<?php
/**
 * Created by PhpStorm.
 * User: raymund
 * Date: 08.04.14
 * Time: 20:48
 */
?>
<div>
    <dl>
<?php foreach($this->rights as $right) : ?>
    <dt><?= $right->Name ?></dt>
    <dd><?= $right->Description ?></dd>
<?php endforeach; ?>
    </dl>
</div>