<?php
echo '<div class="row mt-3"><div class="col-12"><h2 class="m-0">' . $words->get('members') . '</h2></div>';
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

    ?>
    <table id="memberlist" class="table table-striped table-hover w-100 mx-3">
        <thead class="thead-light">
        <tr>
            <th colspan="2">Member</th>
            <th>Details</th>
            <th>About Me</th>
        </tr>
        </thead>
        <tbody>
    <?
    foreach ($this->members as $member) {
        $image = new MOD_images_Image('',$member->username);
        if ($member->HideBirthDate=="No") {
            $member->age = floor($layoutbits->fage_value($member->BirthDate));
        } else {
            $member->age = $words->get("Hidden");
        }
        ?>

        <tr>
            <td>
                <? echo MOD_layoutbits::PIC_100_100($member->username,''); ?>
                <div class="text-center"><a href="members/<?= $member->username; ?>"><?= $member->username; ?></a></div>
            </td>
            <td>
                <div>
                    <strong><?= $member->username; ?></strong>
                </div>
                <div class="small">
                    <p class="m-0 mb-2">
                        <? echo $words->get("yearsold",$member->age); ?>
                    </p>
                    <div class="m-0 mb-2 d-flex">
                        <div class="mr-1"><i class="fa fa-2x fa-map-marker"></i></div>
                        <div><strong><?= $member->city; ?></strong><br><?= htmlspecialchars($this->countryName); ?></div>
                    </div>
                    <p class="m-0 mb-1 font-italic">Occupation</p>
                    <p class="m-0">member since: <strong>01-01-01</strong></p>
                </div>
            </td>
            <td>
                <div class="row px-2">
                    <div><img src="/images/icons/anytime.png"
                              alt="accommodation"></div>
                    <div class="ml-2"><i class="fa fa-bed p-1"></i><span class="h4">max guests</span>
                    </div>
                </div>
                <div class="w-100 font-weight-bold nowrap">
                    <a href="members/<?= $member->username; ?>/comments"><i
                                class="fa fa-comments mr-1"></i>number of comments
                    </a>
                </div>
                <div class="small mt-2">
                    <span>last login: <strong>01-01-01</strong></span>
                </div>
            </td>
            <td class="col summary py-2"><a href="/members/<?= $member->username; ?>"
                                            style="color: #000; text-underline: none; display: block; width: 100%;">About me</a>
            </td>
        </tr>

        <? } ?>

        </tbody>
    </table>

<?
}
$pager->render();
?>