<?php

use App\Doctrine\DomainType;
use Rox\Tools\RoxMigration;

class AddDomainColumnForTranslations extends RoxMigration
{
    public function up()
    {
        $translations = $this->table('words');
        $translations
            ->addColumn('domain', 'enum', [
                'after' => 'code',
                'default' => 'messages',
                'values' => [
                    DomainType::MESSAGES,
                    DomainType::ICU_MESSAGES,
                    DomainType::VALIDATORS
                ]
            ])
            ->save()
        ;
        $this->execute("UPDATE words SET `domain` = '". DomainType::VALIDATORS . "' WHERE code like 'search.location%'");
        $this->execute("UPDATE words SET `domain` = '". DomainType::ICU_MESSAGES . "' WHERE code like 'label.admin.groups.awaiting.approval'");
        $this->execute("UPDATE words SET `domain` = '". DomainType::ICU_MESSAGES . "' WHERE code like 'label.admin.comments.reported'");
        $this->execute("UPDATE words SET `domain` = '". DomainType::ICU_MESSAGES . "' WHERE code like 'label.admin.messages.reported'");
    }

    public function down()
    {
        $translations = $this->table('words');
        $translations
            ->removeColumn('domain')
            ->save()
        ;
    }
}
