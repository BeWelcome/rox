<?php

$Forums = new ForumsController;
$citylatlong = $this->model->getAllCityLatLong();
$google_conf = PVars::getObj('config_google');

// TODO: Creating new rox model base, because I don't know how to get logged in
//       member otherwise. Feel free to correct.
$roxModelBase = new RoxModelBase();
$member = $roxModelBase->getLoggedInMember();

// Get preference for showing profile visits
$preference = $member->getPreference('PreferenceShowProfileVisits', 'Yes');
if ($preference == 'Yes') {
    $showVisitors = true;
} else {
    $showVisitors = false;
}

//Blog model to fetch the Community News
$Blog = new Blog();
$postIt      = $Blog->getTaggedPostsIt('Community News for the frontpage', true);
$format = array('short'=>$words->getSilent('DateFormatShort'));

//magpierss support for BV Blog
require_once SCRIPT_BASE.'build/rox/magpierss/rss_fetch.inc';
