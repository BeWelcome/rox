<?php
/**
 * Events view class.
 *
 * @author shevek
 */
class ActivitiesView extends RoxAppView
{
    /**
     * Constructor.
     */
    public function __construct(
        /**
         * Declaring private variable.
         */
        private readonly ActivitiesModel $_model
    )
    {
    }
}
