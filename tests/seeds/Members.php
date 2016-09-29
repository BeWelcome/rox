<?php

use Rox\Tools\RoxSeed;

class Members extends RoxSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'id'    => 1,
                'Username' => 'member-1',
            ],
            [
                'id'    => 2,
                'Username' => 'member-2',
            ],
            [
                'id'    => 3,
                'Username' => 'member-3',
            ],
            [
                'id'    => 101,
                'Username' => 'member-101',
            ],
        ];

        $members = $this->table('members');
        $members->insert($data)
            ->save();
    }
}
