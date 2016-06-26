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

        $data = array(
            array(
                'title'    => $faker->realText(40),
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => rand(1, 1000),
                'updated_by' => rand(1, 1000),
            ),
            array(
                'title'    => $faker->realText(40),
                'text'    => $faker->realText(rand(200, 4000)),
                'created_by' => rand(1, 1000),
                'updated_by' => rand(1, 1000),
            )
        );

        $communityNews = $this->table('community_news');
        $communityNews->insert($data)
            ->save();
    }
}
