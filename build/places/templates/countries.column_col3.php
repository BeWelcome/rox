<?php

$columns = array();
$lastcontinent = "";
?>
<div class="card-columns" id="accordionPlaces">


<? foreach($this->continents as $continent => $value) { ?>

    <div class="card">
        <div class="card-header bg-light" id="heading<?= $value[0] ?>">
            <h3 class="mb-0">
                <button class="btn btn-link btn-block" type="button" data-toggle="collapse" data-target="#collapse<?= $value[0] ?>" aria-expanded="true" aria-controls="collapse<?= $value[0] ?>">
                    <i class="fa fa-angle-double-down mr-3"></i><?= $value[0] ?><i class="fa fa-angle-double-down ml-3"></i>
                </button>
            </h3>
        </div>

        <div id="collapse<?= $value[0] ?>" class="expand" aria-labelledby="heading<?= $value[0] ?>" data-parent="#accordionPlaces">
            <div class="card-body">
                <div class="row">
                <?php

                foreach ($this->countries[$continent] as $country) {

                    echo '<div class="col-12"><i class="famfamfam-flag-' . strtolower($country->country) . ' mt-2 mr-1"></i>';
                    if ($country->number) {
                        echo '<a href="/places/' . htmlspecialchars($country->name) . '/' . $country->country . '">';
                    }
                    echo htmlspecialchars($country->name);;
                    if ($country->number) {
                        echo '</a><span class="small ml-1 badge badge-info">' . $country->number . '</span>';
                    }
                    echo '</div>';

                }

                ?>
                </div>
            </div>
        </div>
    </div>
    <? } ?>
</div>
