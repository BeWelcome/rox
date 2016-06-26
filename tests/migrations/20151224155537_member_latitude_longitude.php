<?php

use Rox\Tools\RoxMigration;

class MemberLatitudeLongitude extends RoxMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $members = $this->table("members");
        $members
            ->addColumn('Latitude', 'decimal', [  'after' => 'IdCity', 'precision' => 10, 'scale' => 7, 'null' => true])
            ->addColumn('Longitude', 'decimal', [ 'after' => 'Latitude', 'precision' => 10, 'scale' => 7, 'null' => true])
            ->save();

        $this->execute("
            UPDATE 
                members m, geonames g
             SET 
                m.latitude = g.latitude, 
                m.longitude = g.longitude
            WHERE 
                m.IdCity = g.geonameID
        ");
    }

    public function down()
    {
        $members = $this->table("members");
        $members
            ->dropColumn('Latitude')
            ->dropColumn('Longitude')
            ->save();
    }
}
