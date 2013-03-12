<?php
/**
 * Events view class.
 *
 * @author shevek
 */
class EventsView extends RoxAppView
{
    /**
     * Declaring private variable.
     */
    private $_model;

    /**
     * Constructor.
     */
    public function __construct(EventsModel $model) {
        $this->_model = $model;
    }
}
