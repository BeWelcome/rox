<h3><?= $words->get('ActivitiesActions'); ?></h3>
<ul class="linklist">
<?php
    foreach($this->sidebarItems as $sidebarItem) {
        echo '<li><a href="' . $sidebarItem["href"] . '">' . $words->get($sidebarItem["wordCode"]) . '</a></li>';
    }
?>
</ul>