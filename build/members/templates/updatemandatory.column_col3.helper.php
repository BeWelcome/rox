<?php

// TODO: Move to other spot and share with SignupPage
    function buildBirthYearOptions($selYear = 0) {

        $old_member_born = date('Y') - 100;
        $young_member_born = date('Y') - SignupModel::YOUNGEST_MEMBER;

        $out = '';
        for ($i=$young_member_born; $i>$old_member_born; $i--) {
            if (!empty($selYear) && $selYear == $i) {
                $out .= "<option value=\"$i\" selected=\"selected\">$i</option>";
            } else {
                $out .= "<option value=\"$i\">$i</option>";
            }
        }
        return $out;
    }


$callback_tag = $this->layoutkit->formkit->setPostCallback('MembersController', 'updateMandatoryCallback');
$member = $this->member;
$modCrypt = new MOD_crypt();
$m = new \stdClass();
$m->firstname = $modCrypt->MemberReadCrypted($member->FirstName,'');
$m->secondname = $modCrypt->MemberReadCrypted($member->SecondName,'');
$m->lastname = $modCrypt->MemberReadCrypted($member->LastName,'');
$m->geonameid = $member->IdCity;
$m->street = $modCrypt->MemberReadCrypted($member->address->StreetName);
$m->housenumber = $modCrypt->MemberReadCrypted($member->address->HouseNumber);
$m->zip = $modCrypt->MemberReadCrypted($member->address->Zip);
$m->birthday = date("d",strtotime($member->BirthDate));
$m->birthmonth = date("m",strtotime($member->BirthDate));
$selYear = date("Y",strtotime($member->BirthDate));
$birthYearOptions = buildBirthYearOptions($selYear);
$m->gender = $member->Gender;

// values from previous form submit
if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
    // this is a fresh form
    foreach ($m as $key => $value) {
        $vars[$key] = $value;
    }
    $Geo = new GeoModel;
    if (isset($vars['geonameid']) && !isset($vars['geonamename']))
        $vars['geonamename'] = $Geo->getDataById($vars['geonameid'])->name;
        $vars['geonamecountry'] = '';
    if (!isset($vars['errors']))
        $vars['errors'] = array();
} else {
    $vars = $mem_redirect->post;
}

$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;
$words = $this->getWords();

$messengers = $member->messengers();
$website = $member->WebSite;

$groups = $member->get_group_memberships();

//var_dump($member);

?>