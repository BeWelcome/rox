<?php

class TripSingleTripPage extends TripBasePage
{
    protected function getColumnNames()
    {
        return array('col1', 'col3');
    }

    protected function teaserHeadline()
    {
        $trip = $this->trip;
        return "<a href='trip'>{$this->words->get('tripsTitle')}</a> &raquo; <a href='trip/show/{$trip->handle}'>{$trip->handle}</a>";
    }
}
