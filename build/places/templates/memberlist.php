<?php
echo '<div class="row"><div class="col-12"><h2 class="m-0">' . $words->get('members') . '</h2></div>';
$User = new APP_User;
$words = new MOD_words();
$layoutbits = new MOD_layoutbits;
$url = '/places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode . '/';
if ($this->regionCode) {
    $url .= htmlspecialchars($this->regionName) . '/' . $this->regionCode . '/';
}
if ($this->cityCode) {
    $url .= htmlspecialchars($this->cityName) . '/' . $this->cityCode . '/';
}
$loginUrlOpen = '<a href="login' . $url . '#login-widget">';
$loginUrlClose = '</a>';
if (!$this->members) {
    if ($this->totalMemberCount) {
        echo $words->get('PlacesMoreMembers', $words->getSilent('PlacesMoreLogin'), $loginUrlOpen, $loginUrlClose) . $words->flushBuffer();
    } else {
        echo $words->get('PlacesNoMembersFound', htmlspecialchars($this->placeName));
    }
} else {
    if ($this->totalMemberCount != $this->memberCount) {
        echo $words->get('PlacesMoreMembers', $words->getSilent('PlacesMoreLogin'), $loginUrlOpen, $loginUrlClose) . $words->flushBuffer();
    }
    // divide members into pages of Places::MEMBERS_PER_PAGE (20)
    $params = new StdClass;
    $params->strategy = new HalfPagePager('right');
    $params->page_url = $url;
    $params->page_url_marker = 'page';
    $params->page_method = 'url';
    $params->items = $this->memberCount;
    $params->active_page = $this->pageNumber;
    $params->items_per_page = Places::MEMBERS_PER_PAGE;
    $pager = new PagerWidget($params);

    echo '';
    foreach ($this->members as $member) {
        $image = new MOD_images_Image('',$member->username);
        if ($member->HideBirthDate=="No") {
            $member->age = floor($layoutbits->fage_value($member->BirthDate));
        } else {
            $member->age = $words->get("Hidden");
        }
        ?>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="d-flex flex-row">
                <div class="mr-2"><a href="members/<? echo $member->username; ?>"><? echo MOD_layoutbits::PIC_50_50($member->username,''); ?></a></div>
                <div><a href="members/<? echo $member->username; ?>"><? echo $member->username; ?></a><br>
                    <small><? echo $words->get("yearsold",$member->age) . '<br>' . $member->city; ?></small>
                </div>
            </div>
        </div>

        <? } ?>
    </div>

<?
}
$pager->render();
?>