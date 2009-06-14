<?php


class EditProfilePage extends ProfilePage
{

    protected function getSubmenuActiveItem()
    {
        return 'editmyprofile';
    }


    protected function editMyProfileFormPrepare($member)
    {
        $member->setEditMode(true);
        $Rights = MOD_right::get();
        $lang = $this->model->get_profile_language();
        $profile_language = $lang->id;
        $profile_language_code = $lang->ShortCode;
        $profile_language_name = $lang->Name;
        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $ReadCrypted = 'AdminReadCrypted';

        $vars = array();

        // Prepare $vars
        $vars['ProfileSummary'] = ($member->ProfileSummary > 0) ? $member->get_trad('ProfileSummary', $profile_language) : '';
        $vars['BirthDate'] = $member->BirthDate;
        $vars['HideBirthDate'] = $member->HideBirthDate;
        $vars['Occupation'] = ($member->Occupation > 0) ? $member->get_trad('Occupation', $profile_language) : '';
        $vars['Gender'] = $member->Gender;
        $vars['HideGender'] = $member->HideGender;

        $vars['language_levels'] = $member->language_levels;
        $vars['languages_all'] = $member->languages_all;
        $vars['languages_selected'] = $member->languages_spoken;

        $vars['FirstName'] = $member->get_firstname();
        $vars['SecondName'] = $member->get_secondname();
        $vars['LastName'] = $member->get_lastname();
        $vars['HouseNumber'] = $member->get_housenumber();
        $vars['Street'] = $member->get_street();
        $vars['Zip'] = $member->get_zip();
        $vars['IsHidden_FirstName'] = MOD_crypt::IsCrypted($member->FirstName);
        $vars['IsHidden_SecondName'] = MOD_crypt::IsCrypted($member->SecondName);
        $vars['IsHidden_LastName'] = MOD_crypt::IsCrypted($member->LastName);
        $vars['IsHidden_Address'] = MOD_crypt::IsCrypted($member->Address);
        $vars['IsHidden_Zip'] = MOD_crypt::IsCrypted($member->zip);
        $vars['IsHidden_HomePhoneNumber'] = MOD_crypt::IsCrypted($member->HomePhoneNumber);
        $vars['IsHidden_CellPhoneNumber'] = MOD_crypt::IsCrypted($member->CellPhoneNumber);
        $vars['IsHidden_WorkPhoneNumber'] = MOD_crypt::IsCrypted($member->WorkPhoneNumber);
        $vars['HomePhoneNumber'] = ($member->HomePhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->HomePhoneNumber) : '';
        $vars['CellPhoneNumber'] = ($member->CellPhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->CellPhoneNumber) : '';
        $vars['WorkPhoneNumber'] = ($member->WorkPhoneNumber > 0) ? MOD_crypt::MemberReadCrypted($member->WorkPhoneNumber) : '';
        $vars['Email'] = ($member->Email > 0) ? MOD_crypt::MemberReadCrypted($member->Email) : '';
        $vars['WebSite'] = $member->WebSite;

        $vars['messengers'] = $member->messengers();

        $vars['Accomodation'] = $member->Accomodation;
        $vars['MaxGuest'] = $member->MaxGuest;
        $vars['MaxLenghtOfStay'] = $member->get_trad("MaxLenghtOfStay", $profile_language);
        $vars['ILiveWith'] = $member->get_trad("ILiveWith", $profile_language);
        $vars['PleaseBring'] = $member->get_trad("PleaseBring", $profile_language);
        $vars['OfferGuests'] = $member->get_trad("OfferGuests", $profile_language);
        $vars['OfferHosts'] = $member->get_trad("OfferHosts", $profile_language);
        $vars['TabTypicOffer'] = $member->TabTypicOffer;
        $vars['PublicTransport'] = $member->get_trad("PublicTransport", $profile_language);
        $vars['TabRestrictions'] = $member->TabRestrictions;
        $vars['OtherRestrictions'] = $member->get_trad("OtherRestrictions", $profile_language);
        $vars['AdditionalAccomodationInfo'] = $member->get_trad("AdditionalAccomodationInfo", $profile_language);
        $vars['OfferHosts'] = $member->get_trad("OfferHosts", $profile_language);
        $vars['Hobbies'] = $member->get_trad("Hobbies", $profile_language);
        $vars['Books'] = $member->get_trad("Books", $profile_language);
        $vars['Music'] = $member->get_trad("Music", $profile_language);
        $vars['Movies'] = $member->get_trad("Movies", $profile_language);
        $vars['Organizations'] = $member->get_trad("Organizations", $profile_language);
        $vars['PastTrips'] = $member->get_trad("PastTrips", $profile_language);
        $vars['PlannedTrips'] = $member->get_trad("PlannedTrips", $profile_language);

        if (!$memory = $formkit->getMemFromRedirect()) {
            // no memory
            // echo 'no memory';
        } else {
            // from previous form
            if ($memory->post) {
                $post = $memory->post;
                foreach ($post as $key => $value) {
                    $vars[$key] = $value;
                }
                // update $vars for messengers
                if(isset($vars['messengers'])) {
                    $ii = 0;
                    foreach($vars['messengers'] as $me) {
                        $val = 'chat_' . $me['network_raw'];
                        $vars['messengers'][$ii++]['address'] = $vars[$val];
                    }
                }
                // update $vars for $languages
                if(!isset($vars['languages_selected'])) {
                    $vars['languages_selected'] = array();
                }
                $ii = 0;
                $ii2 = 0;
                $lang_used = array();
                foreach($vars['memberslanguages'] as $lang) {
                    if (ctype_digit($lang) and !in_array($lang,$lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                        $vars['languages_selected'][$ii]->IdLanguage = $lang;
                        $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                        array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                        $ii++;
                    }
                    $ii2++;
                }
            }
            // problems from previous form
            if (is_array($memory->problems)) {
                require_once 'edit_warning.php';
            }
        }
        // var_dump($vars);

        return $vars;
    }

}




?>
