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
        require 'templates/singletrip.php';
            
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('trip', $trip->trip_id);

    }

    protected function teaserHeadline()
    {
        return $this->heading;
    }

    protected function leftSideBar()
    {
        echo $this->sidebar;
    }
}
