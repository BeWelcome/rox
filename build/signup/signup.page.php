<?php


class SignupPage extends PageWithRoxLayout
{
    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body_index.php';
    }
    
    protected function getStylesheets()
    {
        $stylesheets[] = 'styles/minimal_index.css';
        $stylesheets[] = 'styles/YAML/screen/custom/tour.css';
        $stylesheets[] = "styles/YAML/screen/custom/signup.css";
        return $stylesheets;
    }
    
    protected function getStylesheetPatches()
    {
        $stylesheet_patches[] = 'styles/YAML/patches/patch_2col_left_seo.css';
        return $stylesheet_patches;
    }
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('signup');
    }
    
    protected function leftSidebar()
    {

    }
    
    protected function column_col2()
    {
        $request = PRequest::get()->request;
        if (!isset($request[1]) || $request[1]== '')
            $step = '1';
        else $step = $request[1];
        require 'templates/sidebar.php';
    }
    
    protected function column_col3()
    {
        // retrieve the callback ID
        $callbackId = $this->model->registerProcess();
        
        // default values
        $selCountry = 0;
        $javascript = false;
        $selCity = null;
        $selYear = 0;
        
        // Overwrite Signup-Geo-Info with GeoVars-Session (used for non-js users), afterwards unset it again.
        if (isset($_SESSION['GeoVars'])) {
            foreach ($_SESSION['GeoVars'] as $key => $value) {
            $_SESSION['SignupBWVars'][$key] = $value;
            }
            unset($_SESSION['GeoVars']);
        }

        // values from previous form submit
        if (!($mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) && !isset($_SESSION['SignupBWVars'])) {
            // this is a fresh form
        } else {
            if (isset($_SESSION['SignupBWVars'])) {
                // we have vars stored already
                $vars = $_SESSION['SignupBWVars'];
            }
            else $vars = $mem_redirect->post;
            
            // last time something went wrong.
            // recover old form input.  
            if (isset($vars['country'])) {
                $selCountry = $vars['country'];
            }
    
            if (isset($vars['javascriptactive'])) {
                // nothing?
            }
            
            if (isset($vars['javascriptactive']) && $vars['javascriptactive'] === 'true') {
                $javascript = true;
            }
            if (isset($vars['city'])) {
                $selCity = $vars['city'];
            }
    
            if (isset($vars['birthyear'])) {
                $selYear = $vars['birthyear'];
            }
        }
        
        $countries = $this->getAllCountriesSelectOption($selCountry);
        $birthYearOptions = $this->buildBirthYearOptions($selYear);
        $city = $this->getCityElement($selCity, $javascript);
        
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
<div style="background:yellow; border:1px solid black; padding:10px; width: 44em; margin-left:10em;">
<div style="font-size:160%;">
You can see the signup page because you are a translator.<br>
Normally you cannot see it when logged in.<br>
Please only use the page for translations!
</div>
<br>
Related pages:
<a href="signup/terms">terms</a> ::
<a href="signup/privacy">privacy</a> ::
<a href="signup/finish">finish</a>
</div>
'
            ;
        }

        require 'templates/registerform'.$this->step.'.php';
        
        echo '<p class="small">* '.$words->get('SignupMandatoryFields').'</p>';
    }
    
    protected function quicksearch()
    {
    }
    
    protected function topnav() {
        parent::topnav();
        // require SCRIPT_BASE . 'build/rox/templates/_languageselector.helper.php';
        // $languageSelectorDropDown = _languageSelectorDropDown();
        // echo '<div class="float_left" style="padding-left:15px">'.$languageSelectorDropDown.'</div>';
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
    
    
    /**
     * @see geo.lib.php method guessCity
     * @see signup.model.php method checkRegistrationForm
     * @param object $city either empty or empty or string or array
     * @param boolean $javascript true or false
     * @return string displaying the city selection, either an
     *                   input text field or a select option box;
     *                   possibly accompanied by additional fields
     *                   needed
     */
    public function getCityElement($city, $javascript)
    {
        if (empty($city)) {
            return '<input type="text" id="city" name="city"  />'."\n";
        } else if (!is_array($city)) {
            return '<input type="text" id="city" name="city"
                value="' . htmlentities($city, ENT_COMPAT, 'utf-8') . '"  />'."\n";
        } else {

            $html = '';
            if (!$javascript) {
                // TODO: needs an explanation in the page (words()...)
                $html .= '<input type="text" id="city" name="city" />'."\n";
            }
            $html .= '<select name="city_id" />';
            foreach ($city as $id => $arr) {
                $text = $arr[0] . " --- " . $arr[1];
                $html .= '<option value="' . $id . '">' . $text . '</option>';
            }

            $html .= "</select>\n";
            return $html;
        }
    }
    
    
    /**
     * @param string $selCountry the selected country
     */
    private function getAllCountriesSelectOption($selCountry) {
        $countries = MOD_geo::get()->getAllCountries();
        $out = '<select id="country" name="country" onchange="change_country(\'formname\');">'."\n";
        $out .= '<option value="0">';
        $words = new MOD_words();
        $out .= $words->get('MakeAChoice');
        $out .= '</option>'."\n";
        foreach ($countries as $countryId => $country) {
            $out .= '<option value="' . $countryId . '"';
            if ($countryId == $selCountry)
                $out .= ' selected';
            $out .= '>';
            $out .= $country;
            $out .= "</option>\n";
        }
        $out .= "</select>\n";
        return $out;
    }
    
}


?>
