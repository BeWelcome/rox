<?php


use Rox\Tools\RoxMigration;

class FeedbackNullableMember extends RoxMigration
{
    public function up()
    {
        $feedbacks = $this->table('feedbacks');
        $feedbacks->changeColumn('idMember', 'integer', ['null' => true])
            ->save();
        $this->execute('UPDATE feedbacks SET idMember = NULL WHERE idMember = 0');
    }

    public function down()
    {
        $this->execute('UPDATE feedbacks SET idMember = 0 WHERE idMember = NULL');

        $feedbacks = $this->table('feedbacks');
        $feedbacks->changeColumn('idMember', 'integer', ['null' => false])
            ->save();
    }
}
