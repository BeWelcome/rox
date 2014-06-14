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
<?php foreach($this->flags as $flag) : ?>
    <dt><?= $flag->Name ?></dt>
    <dd><?= $flag->Description ?></dd>
<?php endforeach; ?>
    </dl>
</div>