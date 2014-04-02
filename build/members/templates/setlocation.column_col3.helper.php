<?php
// Overwrite SetLocation-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
if (isset($_SESSION['GeoVars']) && isset($_SESSION['GeoVars']['id']) && isset($_SESSION['GeoVars']['geonameid'])) {
    foreach ($_SESSION['GeoVars'] as $key => $value) {
        $vars[$key] = $value;
    }
    $Member = new MembersModel;    
    // set the location
    $result = $Member->setLocation($vars['id'],$vars['geonameid']);
    $errors['Geonameid'] = 'Geoname not set';
    if (count($result['errors']) > 0) {
        $vars['errors'] = $result['errors'];
    }
    // unset($_SESSION['GeoVars']);
} 
$callback_tag = $this->layoutkit->formkit->setPostCallback('MembersController', 'setLocationCallback');
$member = $this->member;
$m = new StdClass;
$m->firstname = MOD_crypt::MemberReadCrypted($member->FirstName,'');
$m->secondname = MOD_crypt::MemberReadCrypted($member->SecondName,'');
$m->lastname = MOD_crypt::MemberReadCrypted($member->LastName,'');
$m->geonameid = $member->IdCity;
$m->id = $member->id;
$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();

// values from previous form submit
if (!$mem_redirect || !isset($mem_redirect->post['id'])) {
    // this is a fresh form
    foreach ($m as $key => $value) {
        $vars[$key] = $value;
    }
    if (isset($vars['geonameid']) && !isset($vars['geonamename'])) {
        $geo = new GeoModel;
        $location = $geo->getLocationById($vars['geonameid']);
        if ($location) {
        $country = $location->getCountry();
        $parent = $location->getParent();
        $vars['geonamename'] = $location->name;
        $vars['geonamecountrycode'] = $location->fk_countrycode;
        $vars['latitude'] = $location->latitude;
        $vars['longitude'] = $location->longitude;
        if (isset($parent->name))
        {
        	$vars['admincode'] = $parent->name;
        }
        $vars['geonamecountry'] = $country->name;
        $vars['countryname'] = $country->name;
        }
    }
} else {
    $vars = $mem_redirect->post;
    $vars['errors'] = $mem_redirect->errors;
}
if (!isset($vars['errors']) || empty($vars['errors']))
    $vars['errors'] = array();
// Overwrite Signup-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
if (isset($_SESSION['GeoVars'])) {
    foreach ($_SESSION['GeoVars'] as $key => $value) {
    $vars[$key] = $value;
    }
}
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$words = $this->getWords();

?>