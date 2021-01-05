<?php

use Carbon\Carbon;

class EditProfilePage extends ProfilePage
{
    public function __construct()
    {
        parent::__construct();
        $this->addLateLoadScriptFile('build/bsfileselect.js');
        $this->addLateLoadScriptFile('build/rangeslider.js');
        $this->addLateLoadScriptFile('build/tempusdominus.js');
    }

    // Utility function to sort the languages
    private function _cmpEditLang($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
    }

    protected function getSubmenuActiveItem()
    {
        return 'editmyprofile';
    }

    private function sortLanguages($languages)
    {
        $words = new MOD_words();
        $langarr = array();
        foreach($languages as $language) {
            $lang = $language;
            $lang->TranslatedName = $words->getSilent($language->WordCode);
            $langarr[] = $lang;
        }
        usort($langarr, [$this, "_cmpEditLang"]);
        return $langarr;
    }

    /**
     * @param \Member $member
     *
     * @return array
     * @throws PException
     */
    protected function editMyProfileFormPrepare($member)
    {
        $member->setEditMode(true);
        $Rights = MOD_right::get();
        $lang = $this->model->get_profile_language();
        $hesData = $this->model->getHostingEagernessData($member);

        $profile_language = $lang->id;
        $all_spoken_languages = $this->sortLanguages($this->model->getSpokenLanguages());
        $all_signed_languages = $this->sortLanguages($this->model->getSignedLanguages());

        $layoutkit = $this->layoutkit;
        $formkit = $layoutkit->formkit;
        $ReadCrypted = 'MemberReadCrypted';
        if ($this->adminedit) {
            $ReadCrypted = 'AdminReadCrypted';
        }
        $vars = array();

        // Prepare $vars
        $vars['ProfileSummary'] = ($member->ProfileSummary > 0) ? $member->get_trad('ProfileSummary', $profile_language) : '';
        $vars['BirthDate'] = $member->BirthDate;
        list($vars['BirthYear'], $vars['BirthMonth'], $vars['BirthDay']) = explode('-', $member->BirthDate);
        $vars['HideBirthDate'] = $member->HideBirthDate;
        $vars['Occupation'] = ($member->Occupation > 0) ? $member->get_trad('Occupation', $profile_language) : '';
        $vars['Gender'] = $member->Gender;
        $vars['HideGender'] = $member->HideGender;
        if ($vars['Gender'] == 'IDontTell') {
            $vars['Gender'] = 'other';
            $vars['HideGender'] = true;
        }

        $vars['language_levels'] = $member->language_levels;
        $vars['languages_all_spoken'] = $all_spoken_languages;
        $vars['languages_all_signed'] = $all_signed_languages;
        $vars['languages_selected'] = $member->languages_spoken;

        $vars['FirstName'] = $member->FirstName;
        $vars['SecondName'] = $member->SecondName;
        $vars['LastName'] = $member->LastName;
        $vars['HouseNumber'] = $member->get_housenumber();
        $vars['Street'] = $member->get_street();
        $vars['Zip'] = $member->get_zip();
        $modCrypt = new MOD_crypt();
        $vars['IsHidden_FirstName'] = ($member->HideAttribute & \Member::MEMBER_FIRSTNAME_HIDDEN) ? 'Yes' : 'No';
        $vars['IsHidden_SecondName'] = ($member->HideAttribute & \Member::MEMBER_SECONDNAME_HIDDEN) ? 'Yes' : 'No';
        $vars['IsHidden_LastName'] = ($member->HideAttribute & \Member::MEMBER_LASTNAME_HIDDEN) ? 'Yes' : 'No';
        $vars['IsHidden_Address'] = $modCrypt->IsCrypted($member->address->StreetName);
        $vars['IsHidden_Zip'] = $modCrypt->IsCrypted($member->address->Zip);
        $vars['IsHidden_HomePhoneNumber'] = $modCrypt->IsCrypted($member->HomePhoneNumber);
        $vars['IsHidden_CellPhoneNumber'] = $modCrypt->IsCrypted($member->CellPhoneNumber);
        $vars['IsHidden_WorkPhoneNumber'] = $modCrypt->IsCrypted($member->WorkPhoneNumber);
        $vars['HomePhoneNumber'] = ($member->HomePhoneNumber > 0) ? $modCrypt->$ReadCrypted($member->HomePhoneNumber) : '';
        $vars['CellPhoneNumber'] = ($member->CellPhoneNumber > 0) ? $modCrypt->$ReadCrypted($member->CellPhoneNumber) : '';
        $vars['WorkPhoneNumber'] = ($member->WorkPhoneNumber > 0) ? $modCrypt->$ReadCrypted($member->WorkPhoneNumber) : '';
        $vars['Email'] = $member->Email;
        $vars['WebSite'] = $member->WebSite;

        $vars['messengers'] = $member->messengers();

        $vars['Accomodation'] = $member->Accomodation;
        if ($member->hosting_interest !== 0)
        {
            $vars['hosting_interest'] = $member->hosting_interest;
        }

        /** @var Carbon $hesData->enddate */
        $vars['hes-id'] = $hesData->id;
        $vars['hes-duration'] = $hesData->endDate;
        $vars['hes-boost'] = ($hesData->step < 0) ? 'No' : 'Yes';

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

        $vars['Relations'] = $member->get_all_relations() ;
        $vars['Groups'] = $member->getGroups() ;
        if ($memory = $formkit->getMemFromRedirect()) {
            if ($memory->post) {
                $post = $memory->post;
                foreach ($post as $key => $value) {
                    $vars[$key] = $value;
                }
                // update $vars for messengers
                if (isset($vars['messengers'])) {
                    $ii = 0;
                    foreach ($vars['messengers'] as $me) {
                        $val = 'chat_' . $me['network_raw'];
                        $vars['messengers'][$ii++]['address'] = $vars[$val];
                    }
                }
                // update $vars for $languages
                if (!isset($vars['languages_selected'])) {
                    $vars['languages_selected'] = array();
                }
                $ii = 0;
                $ii2 = 0;
                $lang_used = array();
                foreach ($vars['memberslanguages'] as $lang) {
                    if (ctype_digit($lang) and !in_array($lang, $lang_used)) { // check $lang is numeric, hence a legal IdLanguage
                        $vars['languages_selected'][$ii]->IdLanguage = $lang;
                        $vars['languages_selected'][$ii]->Level = $vars['memberslanguageslevel'][$ii2];
                        array_push($lang_used, $vars['languages_selected'][$ii]->IdLanguage);
                        $ii++;
                    }
                    $ii2++;
                }
            }
        }

        return $vars;
    }
}
