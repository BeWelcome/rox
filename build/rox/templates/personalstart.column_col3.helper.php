<?php

$Forums = new ForumsController;
$citylatlong = $this->model->getAllCityLatLong();
$google_conf = PVars::getObj('config_google');


//Blog model to fetch the Community News
$Blog = new Blog();
$postIt      = $Blog->getTaggedPostsIt('Community News for the frontpage');
$format = array('short'=>$words->getSilent('DateFormatShort'));

//magpierss support for BV Blog
require_once SCRIPT_BASE.'htdocs/script/magpierss/rss_fetch.inc';
