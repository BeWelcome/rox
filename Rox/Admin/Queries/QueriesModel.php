<?php

namespace Rox\Admin\Queries;

use Rox\Models\Query;

class QueriesModel
{
    public static function getQueries() {
        $queries = Query::orderBy('id')->get(['id', 'name']);
        $select = [];
        foreach($queries as $query) {

            $select[trim($query['name'])] = $query['id'];
        }
        return $select;
    }

    public static function getQueryParameters($query)
    {
        $parameterArray = [];
        $parameters = Query::where('id', $query)->first();
        if ($parameters) {
            if ($parameters->param1) {
                $parameterArray[1] = $parameters->param1;
            }
            if ($parameters->param2) {
                $parameterArray[2] = $parameters->param2;
            }
        }
        return $parameterArray;
    }
}