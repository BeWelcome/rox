<?php


use Rox\Tools\RoxMigration;

class DropUnnecessaryFaqFields extends RoxMigration
{
    public function up()
    {
        $faq = $this->table('faq');
        $faq->removeColumn('PageTitle')
            ->save();

        $faqCategories = $this->table('faqcategories');
        $faqCategories->removeColumn('Type')
            ->save();
    }

    public function down()
    {
        $faq = $this->table('faq');
        $faq->addColumn('PageTitle', 'string', [
                'null' => true,
            ])
            ->save();

        $faqCategories = $this->table('faqcategories');
        $faqCategories->addColumn('Type', 'string', [
                'null' => false,
                'default' => 'ForAll',
            ])
            ->save();
    }
}
