<?php
/**
 * Events view class.
 *
 * @author shevek
 */
class ActivitiesView extends RoxAppView
{
    /**
     * Declaring private variable.
     */
    private $_model;

    /**
     * Constructor.
     */
    public function __construct(ActivitiesModel $model) {
        $this->_model = $model;
    }
}
