<?php

use Rox\Tools\RoxMigration;

class AddPollsTranslationsTable extends RoxMigration
{
    public function up()
    {
        $pollsTranslations = $this->table('polls_translations',  ['id' => false, 'primary_key' => ['poll_id', 'translation_id']]);
        $pollsTranslations
            ->addColumn('poll_id', 'integer')
            ->addColumn('translation_id', 'integer')
            ->addIndex(['poll_id'])
            ->addIndex(['translation_id'], ['unique' => true])
            ->addForeignKey('poll_id', 'polls', 'id')
            ->addForeignKey('translation_id', 'translations', 'id')
            ->create();
        $pollsChoiceTranslations = $this->table('poll_choices_translations',  ['id' => false, 'primary_key' => ['poll_choice_id', 'translation_id']]);
        $pollsChoiceTranslations
            ->addColumn('poll_choice_id', 'integer')
            ->addColumn('translation_id', 'integer')
            ->addIndex(['poll_choice_id'])
            ->addIndex(['translation_id'], ['unique' => true])
            ->addForeignKey('poll_choice_id', 'polls_choices', 'id')
            ->addForeignKey('translation_id', 'translations', 'id')
            ->create();
        $this->execute('INSERT INTO polls_translations ( poll_id, translation_id) SELECT p.id, t.id FROM polls p, translations t where p.Title = t.IdTrad');
        $this->execute('INSERT INTO poll_choices_translations ( poll_choice_id, translation_id) SELECT p.id, t.id FROM polls_choices p, translations t where p.IdChoiceText = t.IdTrad');
    }

    public function down()
    {
        $pollsTranslations = $this->table('polls_translations');
        $pollsTranslations->drop()
            ->save();
        $pollsChoiceTranslations = $this->table('poll_choices_translations');
        $pollsChoiceTranslations->drop()
            ->save();
    }
}
