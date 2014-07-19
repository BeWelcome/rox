<?php

use Phinx\Migration\AbstractMigration;

/*************************
 * Class MailFormatPreference
 *
 * Adds a preference to allow members to choose their preferred mail format
 *
 * See ticket: #1853
 */
class MailFormatPreference extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO
	`preferences`
	(`id`, `position`, `codeName`, `codeDescription`, `Description`, `created`, `DefaultValue`, `PossibleValues`, `EvalString`, `Status`)
VALUES
(NULL, '51', 'PreferenceHtmlMails', 'PreferenceHtmlMailsDesc', 'This allows the member to choose the format of the
messages send by bewelcome.org. Defaults to HTML', CURRENT_TIMESTAMP, 'Yes', 'Yes;No', '', 'Normal');");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM `preferences` WHERE `codeName` = 'PreferenceHtmlMails'");
    }
}

