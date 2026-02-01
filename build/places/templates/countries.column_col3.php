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
                    <div class="card-body u:grid u:grid-cols-[auto_1fr] u:gap-x-8 u:gap-y-4">
                            <?php
                            if (isset($this->countries[$continent])) {
                                foreach ($this->countries[$continent] as $country) {

                                    $country_id = $country->country;
                                    echo '<div class="u:grid u:grid-cols-subgrid u:col-span-2"><div><i class="o-flag o-flag--' . $country_id . ' u:-mb-4" title="' . $country->name . '"></i></div>';
                                    echo '<div>';
                                    if ($country->number) {
                                        echo '<a href="/places/' . htmlspecialchars((string) $country->name) . '/' . $country->country . '">';
                                    }
                                    echo htmlspecialchars((string) $country->name);;
                                    if ($country->number) {
                                        echo '</a><span class="small ml-1 badge badge-primary">' . $country->number . '</span>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</div>
