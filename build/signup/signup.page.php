<?php


class SignupPage extends PageWithRoxLayout
{
    
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/tour.css';
        $stylesheets[] = "styles/css/minimal/screen/custom/signup.css?2";
        return $stylesheets;
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
        // default values
        $selCountry = 0;
        $javascript = false;
        $selCity = null;
        $selYear = 0;

        //get baseuri
        $baseuri = PVars::getObj('env')->baseuri;
        if (PVars::getObj('env')->force_ssl_sensitive) {
            $baseuri = PVars::getObj('env')->baseuri_https;
        }

        
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
            
            if (isset($vars['city'])) {
                $selCity = $vars['city'];
            }
            
            if (isset($vars['admincode'])) {
                $selCity = $vars['admincode'];
            }
    
            if (isset($vars['javascriptactive'])) {
                // nothing?
            }
            
            if (isset($vars['javascriptactive']) && $vars['javascriptactive'] === 'true') {
                $javascript = true;
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
    
// END OF LAYOUT FUNCTIONS
    
    
    protected function buildBirthYearOptions($selYear = 0) {

        $old_member_born = date('Y') - 100;
        $young_member_born = date('Y') - SignupModel::YOUNGEST_MEMBER;

        $out = '';
        for ($i=$young_member_born+5; $i>$old_member_born; $i--) {
            if (!empty($selYear) && $selYear == $i) {
                $out .= "<option value=\"$i\" selected=\"selected\">$i</option>";
            } else {
                $out .= "<option value=\"$i\">$i</option>";
            }
        }
        return $out;
    }
    
    
}
