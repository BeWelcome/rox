<?php


use Rox\Tools\RoxMigration;

class DropUnnecessaryFaqFields extends RoxMigration
{
    public function up()
    {
        $faq = $this->table('faq');
        $faq->removeColumn('PageTitle');

        $faqCategories = $this->table('faqcategories');
        $faqCategories->removeColumn('Type');
    }

    public function down()
    {
        $faq = $this->table('faq');
        $faq->addColumn('PageTitle', 'string', [
            'null' => true,
        ]);

        $faqCategories = $this->table('faqcategories');
        $faqCategories->addColumn('Type', 'string', [
            'null' => false,
            'default' => 'ForAll',
        ]);
    }
}
