<?php
namespace Rox\Admin\Logs;

use Illuminate\Database\Capsule\Manager as Capsule;

class LogsModel {

    public static function getLogTypes() {
        // Fetch distinct values from the type column
        // strip the stdclass from the result
        $types = array_map(
            function($a) {
                return $a->Type;
            },
            Capsule::select('SELECT DISTINCT Type FROM logs')
        );

        $types = [-1 => ''] + $types;
        return $types;
    }
}