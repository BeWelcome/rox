<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @author Fake51
     */

    /**
     * represents a single gallery
     *
     * @package Apps
     * @subpackage Entities
     */
class Gallery extends RoxEntityBase
{
    protected $_table_name = 'gallery';

    public function __construct($gallery_id = false)
    {
        parent::__construct();
        if (intval($gallery_id))
        {
            $this->findById(intval($gallery_id));
        }
    }
    
    public function getNotEmpty()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        $sql = <<<SQL
            SELECT DISTINCT
            `id`, `user_id_foreign`, `flags`, `title`, `text`
            FROM `gallery`
            LEFT JOIN `gallery_items_to_gallery` AS `g` ON
                g.`gallery_id_foreign` = g.`gallery_id_foreign`
            WHERE g.`gallery_id_foreign` = gallery.`id`
            ORDER BY `id` DESC
SQL;
        return $this->findBySQLMany($sql);
    }

    public function getLatestGalleryItem()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        $sql = <<<SQL
            SELECT DISTINCT
            `id`, `user_id_foreign`, `flags`, `title`, `text`
            FROM `gallery`
            LEFT JOIN `gallery_items_to_gallery` AS `g` ON
                g.`gallery_id_foreign` = g.`gallery_id_foreign`
            WHERE g.`gallery_id_foreign` = gallery.`id`
            ORDER BY `id` DESC
SQL;
        return $this->findBySQLMany($sql);
    }

    public function getItems()
    {
        if (!$this->_has_loaded)
        {
            return false;
        }
        $sql = <<<SQL
            SELECT `gallery_items_to_gallery`.*
            FROM `gallery`
            LEFT JOIN `gallery_items_to_gallery` AS `g` ON
                g.`gallery_id_foreign` = g.`gallery_id_foreign`
            WHERE g.`gallery_id_foreign` = gallery.`id`
            ORDER BY `id` DESC
SQL;
        return $this->createEntity('GalleryItem')->findBySQLMany($sql);
    }
    
    public function getItems2($status = false, $offset = 0, $limit = null)
    {
        if (!$this->_has_loaded)
        {
            return false;
        }

        $status = (($status) ? $status : 'In');

        return $this->createEntity('GalleryItem')->getGalleryItems($this, $status, '', $offset, $limit);
    }

}
