<?php


use Rox\Tools\RoxMigration;

class UpdateMemberTableAccommodationEnum extends RoxMigration
{
    public function up()
    {
        $members = $this->table('members');
        $members
            ->changeColumn('Accomodation', 'enum', [
                'values' => [
                    'neverask',
                    'dependonrequest',
                    'anytime',
                ],
            ])
            ->update();
    }

    public function down()
    {
        // As the order of the enums isn't important for the old code just do nothing
    }
}
