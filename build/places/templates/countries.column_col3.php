<?php

$columns = array();
$lastcontinent = "";
foreach($this->continents as $continent => $value) {

    echo '<div class="col-6 col-md-4 col-lg-2">';
    echo '<h3>' . $value[0] . '</h3>';

    foreach ($this->countries[$continent] as $country) {

        echo '<p><i class="famfamfam-flag-' . strtolower($country->country) . '"></i><span class="pr-1"></span>';
        if ($country->number) {
            echo '<a href="/places/' . htmlspecialchars($country->name) . '/' . $country->country . '">';
        }
        echo htmlspecialchars($country->name);;
        if ($country->number) {
            echo '</a><span class="ml-1 badge badge-secondary">' . $country->number . '</span>';
        }
        echo '</p>';

    }

   echo '</div>';
}
?>



<div class="col-6 col-md-4 col-lg-3">
    Africa
</div>
<div class="col-6 col-md-4 col-lg-2">
    Eurasia
</div>
<div class="col-6 col-md-4 col-lg-2">
    Antartica
</div>
<div class="col-6 col-md-4 col-lg-2">
    Oceania
</div>
<div class="col-6 col-md-4 col-lg-3">
    America
</div>




<table id="places"><tr>
<?php

$i = 0;
$max = 53;
$top = true;
$columns = array();
$lastcontinent = "";
foreach($this->continents as $continent => $value) {
    foreach($this->countries[$continent] as $country) {
        if ($top) {
            echo '<td>';
            if ($continent == $lastcontinent) {
                echo '<h3 class="top">' . $value[1] . '</h3>';
            } else {
                echo '<h3 class="top">' . $value[0] . '</h3>';
            }
            echo '<ul>';
            $top = false;
        } else {
            if ($continent != $lastcontinent) {
                echo '</ul><h3 class="column">' . $value[0] . '</h3>';
                echo '<ul>';
                $i++;
            }
        }
        $lastcontinent = $continent;
        echo '<li><i class="famfamfam-flag-' . strtolower($country->country) .'"></i><div class="name"><a';
        if ($country->number) {
            echo ' class="highlighted"';
        }
        echo ' href="/places/' . htmlspecialchars($country->name) . '/' . $country->country . '">'. htmlspecialchars($country->name) . '</a>';
        if ($country->number) {
            echo ' <span class="small grey">(' . $country->number . ')</span>';
        }
        echo '</div></li>';
        $i++;
        if ($i > $max) {
            $i = 0;
            echo '</ul></td>';
            $top = true;
        }
    }
}
if ($i <= $max) {
    echo '</ul></td>';
} ?>
</tr></table>