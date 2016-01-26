<?php

use Phinx\Migration\AbstractMigration;

class ResetPasswordWordCode extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('ResetPasswordFlashNotice', '<p>We have just sent you an email containing your new password.<br />Please enter the credentials as given in the email below and change your password immediately.</p>', 'Flash notice shown after the email with the new credentials has been sent.');
        $this->AddWordCode('PasswordSetFlashNotice', 'Your password has been changed.', 'Notice shown after a successful password reset.');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('ResetPasswordFlashNotice');
        $this->RemoveWordCode('PasswordSetFlashNotice');
    }
}