<?php

use Phinx\Migration\AbstractMigration;

/*************************************
 * Class CleanOrphanedGalleryitemsRelations
 *
 * Removes redundant records that have no connection with parenttable anymore
 *
 * See ticket: #1711
 *
 */
class CleanOrphanedGalleryitemsRelations extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute('
DELETE 
FROM gallery_items_to_gallery 
WHERE NOT EXISTS (
    SELECT *
    FROM gallery_items
    WHERE gallery_items.id = gallery_items_to_gallery.item_id_foreign)
                            ');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
/*
    Recording the individual records would be quite useless,
    and it doesn't disturb migrating up-and-down if it's not.
*/
    }
}