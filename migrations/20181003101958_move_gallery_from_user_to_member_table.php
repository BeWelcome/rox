<?php


use Rox\Tools\RoxMigration;

class MoveGalleryFromUserToMemberTable extends RoxMigration
{
    public function up()
    {
        // replace all user_id_foreign in gallery to point to the member id instead
        $this->execute("      
            update gallery g 
            INNER JOIN user u ON g.user_id_foreign = u.id
            INNER JOIN members m ON u.handle = m.username
            SET g.user_id_foreign = m.id
        ");
        $this->execute("      
            update gallery_items g 
            INNER JOIN user u ON g.user_id_foreign = u.id
            INNER JOIN members m ON u.handle = m.username
            SET g.user_id_foreign = m.id
        ");
        $this->execute("      
            update gallery_comments g 
            INNER JOIN user u ON g.user_id_foreign = u.id
            INNER JOIN members m ON u.handle = m.username
            SET g.user_id_foreign = m.id
        ");
        // now rename the gallery directories
        $stmt = $this->query("select m.id as memberId, u.id as userId from members m, user u where m.username = u.handle order by u.id desc");
        $ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($ids as $row)
        {
            $oldDirectory = 'data/gallery/user' . $row['userId'];
            if (is_dir($oldDirectory)) {
                try {
                    rename($oldDirectory, 'data/gallery/member' . $row['memberId']);
                } catch (Exception $e) {
                    echo 'Couldn\'t rename data/gallery/user' . $row['userId'] . ' to data/gallery/member' . $row['memberId'] . PHP_EOL;
                }
            }
        }
    }

    public function down()
    {
        // replace all user_id_foreign in gallery to point to the member id instead
        $this->execute("      
            update gallery g 
            INNER JOIN members m ON g.user_id_foreign = m.id
            INNER JOIN user u ON u.handle = m.username
            SET g.user_id_foreign = u.id
        ");
        $this->execute("      
            update gallery_items g 
            INNER JOIN members m ON g.user_id_foreign = m.id
            INNER JOIN user u ON u.handle = m.username
            SET g.user_id_foreign = u.id
        ");
        $this->execute("      
            update gallery_comments g 
            INNER JOIN members m ON g.user_id_foreign = m.id
            INNER JOIN user u ON u.handle = m. username
            SET g.user_id_foreign = u.id
        ");
        // now rename the gallery directories
        $stmt = $this->query("select m.id as memberId, u.id as userId from members m, user u where m.username = u.handle order by m.id desc");
        $ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($ids as $row)
        {
            $oldDirectory = 'data/gallery/member' . $row['memberId'];
            if (is_dir($oldDirectory)) {
                try {
                    rename('data/gallery/member' . $row['memberId'], 'data/gallery/user' . $row['userId']);
                } catch (Exception $e) {
                    echo 'Couldn\'t rename data/gallery/member' . $row['memberId'] . ' to data/gallery/user' . $row['userId'] . PHP_EOL;
                }
            }
        }
    }
}
