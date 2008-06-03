<?php


class DatabaseSummaryModel extends RoxModelBase
{
    function getDatabaseFieldsSorted($where = array())
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
    ".(empty($where) ? '' : ('AND '.implode(' AND ', $where)))."
            ",
            array('TABLE_SCHEMA', 'TABLE_NAME', 'COLUMN_TYPE', 'COLUMN_NAME')
        );
    }
    
    function getDatabaseTablesSorted($where = array())
    {
        return $this->bulkLookup(
            "
SELECT
    *
FROM
    information_schema.TABLES
WHERE
    TABLE_SCHEMA != 'information_schema'
    ".(empty($where) ? '' : ('AND '.implode(' AND ', $where)))."
            ",
            array('TABLE_SCHEMA', 'TABLE_NAME')
        );
    }
    
    function getDatabaseTablesWithFieldsSorted()
    {
        $tables_sorted = $this->getDatabaseTablesSorted();
        $fields_sorted = $this->getDatabaseFieldsSorted();
        foreach ($tables_sorted as $schema => $tables_in_schema) {
            foreach ($tables_in_schema as $tablename => $table) {
                if (isset($fields_sorted[$schema][$tablename])) {
                    $table->fields = $fields_sorted[$schema][$tablename];
                } else {
                    $table->fields = array();
                }
            }
        }
        return $tables_sorted;
    }
}


?>