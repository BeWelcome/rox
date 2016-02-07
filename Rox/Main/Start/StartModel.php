<?php

namespace Rox\Main\Start;

/**
 * dashboard controller
 *
 * @package Dashboard
 * @author Amnesiac84
 */
class StartModel extends \RoxModelBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
    }

    public function getStatistics() {
        return array(
            'members' => '101101',
            'countries' => '102',
            'languages' => '103',
            'comments' => '10400',
            'activities' => '4096'
        );
    }
}
