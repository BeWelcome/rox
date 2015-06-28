<?php

use Phinx\Migration\AbstractMigration;

class AddMailToConfirmReminder extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("
            ALTER TABLE
              `broadcast`
            MODIFY COLUMN `Type`ENUM ('Normal','RemindToLog','Specific','SuggestionReminder','TermsOfUse', 'MailToConfirmReminder');
                ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
            ALTER TABLE
              `broadcast`
            MODIFY COLUMN `Type`ENUM ('Normal','RemindToLog','Specific','SuggestionReminder','TermsOfUse');
        ");
    }
}