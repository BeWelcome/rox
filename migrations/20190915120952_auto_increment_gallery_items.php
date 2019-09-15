<?php

use Rox\Tools\RoxMigration;

class AutoIncrementGalleryItems extends RoxMigration
{
    public function up()
    {
        $table = $this->table('gallery_items');
        $table
            ->changeColumn('id', 'integer', [
                'identity' => true,
            ])
            ->changeColumn('file', 'string', [
                'limit' => 50,
            ]);

        $table->update();
    }

    public function down()
    {
        $table = $this->table('gallery_items');
        $table
            ->changeColumn('id', 'integer', [
                'identity' => false,
            ])
            ->changeColumn('file', 'string', [
                'limit' => 40,
            ]);
        $table->update();
    }
}
