<?php


class SignupPage extends PageWithRoxLayout
{
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = "styles/YAML/screen/custom/signup.css";
        return $stylesheets;
    }
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('signup');
    }
    
    protected function columnsArea()
    {
        // retrieve the callback ID
        $callbackId = $this->model->registerProcess();
        
        // default values
        $selCountry = 0;
        $javascript = false;
        $selCity = null;
        $selYear = 0;
        
        // values from previous form submit
        if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            // this is a fresh form
        } else {
            $vars = $mem_redirect->post;
            
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
        
        // don't show the register form, if user is logged in. Redirect to "my" page instead.
        if ($User = APP_User::login()) {
            $url = PVars::getObj('env')->baseuri.'user/'.$User->getHandle();
            header('Location: '.$url);
            PPHP::PExit();
        }
        
        require TEMPLATE_DIR . 'apps/signup/registerform.php';
    }
    
    
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