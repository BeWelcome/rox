<?php

$columns = [];
$lastcontinent = "";
?>
<div class="row">
<div class="col-12">
    <div class="card-columns">
        <?php foreach ($this->continents as $continent => $value) { ?>
            <div class="card">
                <div class="card-header bg-primary" id="heading<?= $value[0] ?>">
                    <h5 class="m-0"><?= $value[0] ?></h5>
                </div>
                <div aria-labelledby="heading<?= $value[0] ?>">
                    <div class="card-body pt-0">
                        <div class="row">
                            <?php
                            if (isset($this->countries[$continent])) {
                                foreach ($this->countries[$continent] as $country) {

                                    echo '<div class="col-12"><i class="famfamfam-flag-' . strtolower((string) $country->country) . ' mt-2 mr-1"></i>';
                                    if ($country->number) {
                                        echo '<a href="/places/' . htmlspecialchars((string) $country->name) . '/' . $country->country . '">';
                                    }
                                    echo htmlspecialchars((string) $country->name);;
                                    if ($country->number) {
                                        echo '</a><span class="small ml-1 badge badge-primary">' . $country->number . '</span>';
                                    }
                                    echo '</div>';

                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</div>
