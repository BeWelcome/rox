<?php


use Rox\Tools\RoxMigration;

class SetDatabaseEngineToInnoDbForAllTables extends RoxMigration
{
    /**
     * Alter all tables to use InnoDB engine.
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
