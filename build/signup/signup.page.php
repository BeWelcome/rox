<?php


class SignupPage extends SignupBasePage
{
    /** @var int Stores the current signup step */
    private $_step;

    /**
     * SignupPage constructor.
     *
     * @param int $step
     */
    public function __construct($step = 0)
    {
        parent::__construct();
        $this->_step = $step;
        if ($step == 2) {
            $this->addLateLoadScriptFile('build/tempusdominus.js');
        }
        if ($step == 3) {
            $this->addLateLoadScriptFile('build/jquery_ui.js');
            $this->addLateLoadScriptFile('build/leaflet.js');
            $this->addLateLoadScriptFile('script/signup/createmap.js');
            $this->addLateLoadScriptFile('script/search/searchlocation.js');
        }
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        if ($this->_step == 3) {
            $stylesheets[] = '/build/leaflet.css';
            $stylesheets[] = '/build/jquery_ui.css';
        }
        return $stylesheets;
    }
    
    private function _cmpEditLang($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
    }

    private function _sortLanguages($languages)
    {
        $words = new MOD_words;
        $langarr = array();
        foreach($languages as $language) {
            $lang = $language;
            $lang->TranslatedName = $words->getSilent($language->WordCode);
            $langarr[] = $lang;
        }
        usort($langarr, array($this, "_cmpEditLang"));
        return $langarr;
    }

    protected function getAllLanguages($spoken, $selected) {
        $member = new Member();
        if ($spoken) {
            $languages = $this->_sortLanguages($member->get_all_spoken_languages());
        } else {
            $languages = $this->_sortLanguages($member->get_all_signed_languages());
        }
        $options = "";
        foreach($languages as $language) {
            $options .= '<option value="' . $language->id . '"';
            if ($language->id == $selected) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . $language->TranslatedName . '</option>';
        }
        return $options;
    }

    protected function column_col3()
    {
        $selYear = 0;

        //get baseuri
        $baseuri = PVars::getObj('env')->baseuri;
        if (PVars::getObj('env')->force_ssl_sensitive) {
            $baseuri = PVars::getObj('env')->baseuri_https;
        }

        // Overwrite Signup-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
        if ($this->_session->has( 'GeoVars' )) {
            foreach ($this->_session->get('GeoVars') as $key => $value) {
            $this->_session->set( 'SignupBWVars[' . $key . ']', $value );
            }
            $this->_session->remove('GeoVars');
        }

        // values from previous form submit
        if (!($mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) && !$this->_session->has( 'SignupBWVars' )) {
            // this is a fresh form
        } else {
            if ($this->_session->has( 'SignupBWVars' )) {
                // we have vars stored already
                $vars = $this->_session->get('SignupBWVars');
            } else {
                $vars = $mem_redirect->post;
            }
            
            if (isset($vars['birthyear'])) {
                $selYear = $vars['birthyear'];
            }
        }
        
        $birthYearOptions = $this->buildBirthYearOptions($selYear);
        
        // get current request
        $request = PRequest::get()->request;
        if (!isset($vars['errors']) || !is_array($vars['errors'])) {
            $vars['errors'] = array();
        }
        
        $words = $this->layoutkit->words;
        
        $callback_tag = $this->layoutkit->formkit->setPostCallback('SignupController', 'signupFormCallback');
        
        if ($User = APP_User::login()) {
            // show the page anyway.
            // redirect should happen in the controller.
            // but for translators show the page.
            echo '
<div style="background:yellow; border:1px solid black; padding:10px; width: 44em; margin-bottom: 2em;">
<div style="font-size:160%;">
You can see the signup page because you are a translator.<br>
Normally you cannot see it when logged in.<br>
Please only use the page for translations!
</div>
<br>
Related page: <a href="signup/finish">Signup confirmation</a>
</div>
'
            ;
        }

        require 'templates/registerform'.$this->_step.'.php';
    }
    
// END OF LAYOUT FUNCTIONS
    
    
    protected function buildBirthYearOptions($selYear = 0) {

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
    
    
}
