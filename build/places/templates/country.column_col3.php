<?php if (count($this->regions) > 0) { ?>
    <div class="col-12 px-0">
        <h2><?php echo $words->get('region_overview_title'); ?></h2>
    </div>

    <?php
    define('MINROWS', 5); // minimum number of rows to be used before next column
    echo '<div class="d-flex flex-column pb-3">';
    $listcnt = 0;
    $memberCount = 0;
    foreach ($this->regions as $code => $region) {
        // counting total members for possible login-to-see-more message
        $memberCount += $region['number'];

        $listcnt++;

        if ($listcnt > MINROWS) {
            echo '</div><div class="d-flex flex-column px-3 pb-3">';
            $listcnt = 1;
        }

        echo '<div class="p-1"><a href="places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode . '/'
            . htmlspecialchars($region['name']) . '/' . $code . '"> ' . htmlspecialchars($region['name']) . '</a><span class="small ml-1 badge badge-primary">' . $region['number'] . '</span></div>';

    }
    echo '</div>';
}
echo '</div>';

include_once 'memberlist.php';
?>
