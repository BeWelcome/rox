<?php


use Rox\Tools\RoxMigration;

class SetDatabaseEngineToInnoDbForAllTables extends RoxMigration
{
    /**
     * Alter all tables to use utf8mb4 as charset. Set all collations to utf8mb4_unicode_520_ci.
     */
    public function up()
    {
        $tables = $this->fetchAll("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
        foreach($tables as $table) {
            $tableName = $table[0];
            $this->execute(
                "ALTER TABLE ".$tableName." ENGINE = InnoDB"
            );
        }
    }

    public function down()
    {
        /* This can't be reverted so we do nothing. */
    }
}
