<?php

use Rox\Tools\RoxMigration;

class UpdateCharsetAndCollationToUtf8mb4 extends RoxMigration
{
    /**
     * Alter all tables to use utf8mb4 as charset. Set all collations to utf8mb4_unicode_520_ci.
     */
    public function up()
    {
        $tables = $this->fetchAll("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
        foreach($tables as $table) {
            $tableName = $table[0];
            echo $tableName . PHP_EOL;
            $this->execute(
                "ALTER TABLE ".$tableName." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci"
            );
        }
    }

    public function down()
    {
        $tables = $this->fetchAll("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
        foreach($tables as $table) {
            $tableName = $table[0];
            $this->execute("ALTER TABLE " . $tableName . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_520_ci");
        }
    }
}
