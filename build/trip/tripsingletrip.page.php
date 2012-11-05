<?php

class TripSingleTripPage extends PageWithActiveSkin
{

    protected function column_col3()
    {
        $trip = $this->trip;
        $trip_data = $this->trip_data;
        $member = $this->model->getLoggedInMember();
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
        $layoutbits = new MOD_layoutbits();

        $return = <<<HTML
        <div class="float_left">
            {$layoutbits->PIC_50_50($trip->handle)}
        </div>
            {$trip->trip_name}
HTML;
        return $return;
    }

    protected function leftSideBar()
    {
        echo $this->sidebar;
    }
    
    protected function getStylesheets() 
    {
       $stylesheets = parent::getStylesheets();
       $stylesheets[] = 'styles/css/minimal/screen/custom/trip_single_trip.css';
       return $stylesheets;
    }
    
}
