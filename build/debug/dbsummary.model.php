<?php


class DatabaseSummaryModel extends RoxModelBase
{
    function getTablesByDatabase()
    {
        // returned an array hierarchy,
        // sorted by schema (=dbname), table name, column name
        return $this->bulkLookup(
            "
SELECT
    *
FROM
    information_schema.COLUMNS
WHERE
    TABLE_SCHEMA != 'information_schema'
            ",
            array('TABLE_SCHEMA', 'TABLE_NAME', 'COLUMN_TYPE', 'COLUMN_NAME')
        );
    }
}


?>