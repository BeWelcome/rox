<?php

use Phinx\Migration\AbstractMigration;

/*****************
 * Class MailToConfirmReminderWordCodes
 *
 * Create new wordcodes for additional mass mailing option 'MailToConfirmReminder'
 *
 * See ticket: 2276
 */
class MailToConfirmReminderWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode(
            'AdminMassMailEnqueueMailToConfirmReminder',
            'MailToConfirm Reminder',
            'Word code in AdminMassMail tool - no translation required.',
            'Yes'
        );
        $this->AddWordCode(
            'AdminMassMailEnqueueMailToConfirmReminderInfo',
            'Do you want to enqueue the reminder to %s members with status \'MailToConfirm\'',
            'Word code in AdminMassMail tool - no translation required.',
            'Yes'
        );
        $this->AddWordCode(
            'AdminMassMailEnqueueSubmitMailToConfirmReminder',
            'Submit',
            'Word code in AdminMassMail tool - no translation required.',
            'Yes'
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode(
            'AdminMassMailEnqueueMailToConfirmReminder'
        );
        $this->RemoveWordCode(
            'AdminMassMailEnqueueMailToConfirmReminderInfo'
        );
        $this->RemoveWordCode(
            'AdminMassMailEnqueueSubmitMailToConfirmReminder'
        );
    }
}