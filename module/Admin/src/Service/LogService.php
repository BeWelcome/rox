<?php

namespace Rox\Admin\Service;

use Illuminate\Database\ConnectionInterface;

/**
 * Class LogService.
 */
class LogService
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getLogTypes()
    {
        // Fetch distinct values from the type column
        // strip the StdClass from the result
        $types = array_map(
            function ($value) {
                return $value->Type;
            },
            $this->connection->select('SELECT DISTINCT Type FROM logs')
        );

        // TODO What is this bit for?
        $types = [-1 => ''] + $types;

        return $types;
    }
}
