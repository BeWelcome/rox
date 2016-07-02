<?php

use Rox\Tools\RoxSeed;

class CommunityNews extends RoxSeed
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
        $faker = Faker\Factory::create();

        $data = [
            [
                'title'    => 'Created by member-1',
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => 1,
                'created_at' => '2015-12-31T12:00:00',
            ],
            [
                'title'    => 'Created by member-2',
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => 2,
                'created_at' => '2016-01-31T12:30:00',
            ],
            [
                'title'    => 'Created and updated by member-3',
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => 3,
                'created_at' => '2016-02-29T13:00:00',
                'updated_by' => 3,
                'updated_at' => '2016-02-29T13:30:00',
            ],
            [
                'title'    => 'Created by member-1, updated by member-2 and deleted by member-3',
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => 1,
                'created_at' => '2016-03-31T14:00:00',
                'updated_by' => 2,
                'updated_at' => '2016-03-31T14:30:00',
                'deleted_by' => 3,
                'deleted_at' => '2016-04-01T15:00:00',
            ],
        ];

        $communityNews = $this->table('community_news');
        $communityNews->insert($data)
            ->save();
    }
}
