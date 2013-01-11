<?php

class TripSingleTripPage extends PageWithActiveSkin
{

    protected function column_col3()
    {
        $trip = $this->trip;
        $trip_data = $this->trip_data;
        $words = new MOD_words();
        $layoutbits = new MOD_layoutbits();
        $member = $this->model->getLoggedInMember();
        $google_conf = PVars::getObj('config_google');
        $Model = new Blog;
        $callback = $this->getCallbackOutput('BlogController', 'createProcess');
        // get the saved post vars
        // todo: grab from page model
        $vars = array();
        $request = PRequest::get()->request;
        $errors = array();
        $lang = array();
        //$tripIt = $Model->getTripFromUserIt($member->id);
        //$defaultVis = A PP_User::getSetting($User->getId(), 'APP_blog_defaultVis');
        // defaults to public then
        $defaultVis = false;
        
        if (!$member)
        {   
            $isOwnTrip = false;
        }   
        else
        {   
            $isOwnTrip = ($trip->IdMember == $member->id);
        }   
        require 'templates/singletrip_details.php';
        require 'templates/singletrip.php';
            
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('trip', $trip->trip_id);

    }

    protected function teaserHeadline()
    {
        $trip = $this->trip;
        return "<a href='trip'>{$this->words->get('tripsTitle')}</a> &raquo; <a href='trip/show/{$trip->handle}'>{$trip->handle}</a>";
    }
    
    protected function getStylesheets() 
    {  
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/trip_single_trip.css';
        
        return $stylesheets;
    }
    
}
