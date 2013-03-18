<?php
/**
 * blog model
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id:blog.model.php 201 2007-02-11 14:07:56Z marco $
* Fix by JeanYves on July 2011 1st to avoid kicked members post to be displayed
 */
class Blog extends RoxModelBase 
{
    private $_namespace;

    /* Note: Comments counting via this query is unreliable, because it does
     *       not respect commenter's visibility. Use Blog::countComments($id)
     *       instead. Another JOIN to respect commenter's visibility here makes
     *       the query very slow unfortunately.
     */
    const SQL_BLOGPOST = "
        SELECT b.blog_id,
               b.IdMember,
               m.Status                     AS MemberStatus,
               b.flags                      AS flags,
               bd.blog_title,
               bd.blog_text,
               m.Username                   AS user_handle,
               UNIX_TIMESTAMP(blog_created) AS unix_created,
               COUNT(c.id)                  AS comments,
               geonames_cache.latitude,
               geonames_cache.longitude,
               geonames_cache.name          AS geonamesname,
               geonames_countries.name      AS geonamescountry,
               user_geonames_cache.fk_countrycode
        FROM   blog AS b
               JOIN blog_data AS bd
                 ON b.blog_id = bd.blog_id
               LEFT JOIN trip_data AS td
                 ON b.trip_id_foreign = td.trip_id
               JOIN members AS m
                 ON b.IdMember = m.id
                    AND m.Status IN ('Active', 'Pending', 'ChoiceInactive', 'OutOfRemind', 'PassedAway')
               LEFT JOIN blog_comments AS c
                 ON c.blog_id_foreign = b.blog_id
               LEFT JOIN geonames_cache AS geonames_cache
                 ON bd.blog_geonameid = geonames_cache.geonameid
               LEFT JOIN geonames_countries
                 ON geonames_cache.fk_countrycode = geonames_countries.iso_alpha2
               LEFT JOIN addresses AS a
                 ON a.IdMember = m.id
               LEFT JOIN geonames_cache AS user_geonames_cache
                 ON a.IdCity = user_geonames_cache.geonameid
            ";

    /*
     * Blogentry flags
     * 
     * binary
     */
    /**
     * private
     */
    const FLAG_VIEW_PRIVATE   = 1;
    /**
     * protected
     */
    const FLAG_VIEW_PROTECTED = 2;
    const FLAG_STICKY         = 8; 
    /**
     * anyone may comment
     */
    const FLAG_COMMENT_ALL    = 16;

    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * fetches a blog post entity
     *
     * @param int $blog_id
     *
     * @access public
     * @return false|BlogEntity
     */
    public function getBlogPost($blog_id)
    {
        return $this->createEntity('BlogEntity')->findById($blog_id);
    }
    
    public function createData($blogId, $title, $text, $start = false, $geonameId = false)
    {
        $query = '
INSERT INTO `blog_data`
(`blog_id`, `blog_title`, `blog_text`, `blog_start`, `blog_geonameid`)
VALUES
(
    '.$blogId.',
    \''.$this->dao->escape($title).'\',
    \''.$this->dao->escape($text).'\',
    '.($start ? '\''.$this->dao->escape($start).'\'' : 'NULL').', 
    '.($geonameId ? "'".$this->dao->escape($geonameId)."'" : "NULL").'
)';
        return $this->dao->exec($query);
    }
    
    public function createEntry($flags, $userId, $tripId = false)
    {
        $query = '
INSERT INTO `blog`
(`blog_id`, `flags`, `blog_created`, IdMember, `trip_id_foreign`)
VALUES
(
    '.$this->dao->nextId('blog').',
    '.(int)$flags.',
    NOW(), 
    '.(int)$userId.',
    '.($tripId ? (int)$tripId : 'NULL').'
)'; 
        $s = $this->dao->query($query);
        return $s->insertId();
    }
    
    public function setTripPosition($trip, $blogId) {
    	// Get the last trip entry
    	$query = sprintf("SELECT MAX(`blog_display_order`) 
			FROM `blog_data` 
			LEFT JOIN `blog` ON (`blog_data`.`blog_id` = `blog`.`blog_id`)
			WHERE `trip_id_foreign` = '%d'",
			$trip);
		$s = $this->dao->query($query);
		if ($s) {
			$max = $s->fetchColumn();
		} else {
			$max = 0;
			error_log('Could not fetch max display order');
		}
		$max += 1000000; // We add the item at the bottom of the list, 1 Mio apart of the last entry's position
		$query = "UPDATE `blog_data` SET `blog_display_order` = '".$max."' WHERE `blog_id`= '".$blogId."'";
		$this->dao->query($query);
    }
    
    public function deleteData($postId)
    {
        return $this->dao->exec('DELETE FROM `blog_data` WHERE `blog_id` = '.(int)$postId);
    }
    
    public function deleteEntry($blogId)
    {
        return $this->dao->exec('DELETE FROM `blog` WHERE `blog_id` = '.(int)$blogId);
    }
    
    public function ajaxEditPost($id, $title = false, $text = false,$geoid = false)
    {
        if ($geoid) {
            if (!$this->checkGeonamesCache($geonameId)) return false;
        }
		$this->dao->query("START TRANSACTION");
        $query = "UPDATE `blog_data` ";
        if ($title) $query .= "SET `blog_title` = '".$this->dao->escape($title)."'";
        elseif ($text) $query .= "SET `blog_text` = '".$this->dao->escape($text)."'";
        elseif ($geoid) {
            $query .= "SET `blog_geonameid` = '".(int)$geoid."'";
        }
        $query .= "WHERE `blog_id`= ".$id;
        $this->dao->exec($query);
		$this->dao->query("COMMIT");
    }
    
    public function getEditData($blogId)
    {
        $query = '
SELECT
    `b`.`trip_id_foreign`,
    (`b`.`flags` & '.(int)Blog::FLAG_STICKY.') AS `is_sticky`, 
    (`b`.`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.') AS `is_private`, 
    (`b`.`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.') AS `is_protected`, 
    `bd`.`blog_title`, `bd`.`blog_text`, `bd`.`blog_start`, `bd`.`blog_end`,
    `bd`.`blog_geonameid`,
    `bc`.`blog_category_id_foreign` AS `category`,
    `geonames_cache`.`latitude`, `geonames_cache`.`longitude`, `geonames_cache`.`name` AS `geonamesname`, `geonames_cache`.`fk_countrycode`, `geonames_cache`.`fk_admincode`, `geonames_countries`.`name` AS `geonamecountry`
FROM `blog` AS `b`
JOIN `blog_data` AS `bd` ON `b`.`blog_id` = `bd`.`blog_id`
LEFT JOIN `blog_to_category` AS `bc` ON (`b`.`blog_id` = `bc`.`blog_id_foreign`)
LEFT JOIN `geonames_cache` ON (`bd`.`blog_geonameid` = `geonames_cache`.`geonameid`)
LEFT JOIN `geonames_countries` ON (`geonames_cache`.`fk_countrycode` = `geonames_countries`.`iso_alpha2`)
WHERE `b`.`blog_id` = '.(int)$blogId.'
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog entry!');
        }
        if ($s->numRows() == 0) {
            return false;
        } elseif ($s->numRows() > 1) {
            throw new PException('Inconsistent data.');
        }
        return $s->fetch(PDB::FETCH_OBJ);
    }
    
    public function getTags($blogId)
    {
        // fetch tags.
        $query = '
SELECT bt.`name`
FROM `blog_to_tag` b2t
JOIN `blog_tags` bt ON b2t.`blog_tag_id_foreign` = bt.`blog_tag_id`
WHERE b2t.`blog_id_foreign` = '.(int)$blogId.'
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve tags!');
        }
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    public function updateTags($blogId, $tags)
    {
        $blogId = (int)$blogId;
        $this->dao->exec('DELETE FROM `blog_to_tag` WHERE `blog_id_foreign` = '.(int)$blogId);
        $tag = false;
        $tagId = 0;

        $createTag = $this->dao->prepare('
INSERT INTO `blog_tags` (`blog_tag_id`, `name`) VALUES (?, ?)');
        $createTag->bindParam(0, $tagId);
        $createTag->bindParam(1, $tag);
        
        $addAssoc = $this->dao->prepare('
INSERT INTO `blog_to_tag` 
(`blog_id_foreign`, `blog_tag_id_foreign`) VALUES (?, ?)');
        $addAssoc->bindParam(0, $blogId);
        $addAssoc->bindParam(1, $tagId);
        foreach ($tags as $idx=>$tag) {
            $tag = trim($tag);
            if (!$tag)
                continue;
            $tagId = $this->dao->query('
SELECT `blog_tag_id` FROM `blog_tags` WHERE `name` = \''.$this->dao->escape($tag).'\'');
            if (!$tagId || !$tagId->numRows()) {
                $tagId = (int)$this->dao->nextId('blog_tags');
                $createTag->execute();
            } else {
                $tagId = (int)$tagId->fetch(PDB::FETCH_OBJ)->blog_tag_id;
            }
            $addAssoc->execute();
        }

        return true;
    }

    /**
     * Fills the posthandler vars with the blog from $blogId.
     *
     * @return false if no blog could be found with id $blogId, otherwise true.
     */
    public function editFill($blogId, &$vars)
    {
        $query = '
SELECT
    `b`.`trip_id_foreign`,
    (`b`.`flags` & '.(int)Blog::FLAG_STICKY.') AS `is_sticky`, 
    (`b`.`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.') AS `is_private`, 
    (`b`.`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.') AS `is_protected`, 
    `bd`.`blog_title`, `bd`.`blog_text`, `bd`.`blog_start`, `bd`.`blog_end`,
    `bd`.`blog_geonameid`,
    `bc`.`blog_category_id_foreign` AS `category`,
    `geonames_cache`.`latitude`, `geonames_cache`.`longitude`, `geonames_cache`.`name` AS `geonamesname`, `geonames_cache`.`fk_countrycode`, `geonames_cache`.`fk_admincode`, `geonames_countries`.`name` AS `geonamecountry`
FROM `blog` AS `b`
JOIN `blog_data` AS `bd` ON `b`.`blog_id` = `bd`.`blog_id`
LEFT JOIN `blog_to_category` AS `bc` ON (`b`.`blog_id` = `bc`.`blog_id_foreign`)
LEFT JOIN `geonames_cache` ON (`bd`.`blog_geonameid` = `geonames_cache`.`geonameid`)
LEFT JOIN `geonames_countries` ON (`geonames_cache`.`fk_countrycode` = `geonames_countries`.`iso_alpha2`)
WHERE `b`.`blog_id` = '.(int)$blogId.'
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog entry!');
        }
        if ($s->numRows() == 0) {
            return false;
        } elseif ($s->numRows() > 1) {
            throw new PException('Inconsistent data.');
        }
        $b = $s->fetch(PDB::FETCH_OBJ);
        $vars['t'] = $b->blog_title;
        $vars['txt'] = $b->blog_text;
        $vars['tr'] = $b->trip_id_foreign;
        $vars['flag-sticky'] = $b->is_sticky;
        $vars['cat'] = $b->category;
        $vars['vis'] = 'pub';
        if ($b->is_private)
            $vars['vis'] = 'pri';
        if ($b->is_protected)
            $vars['vis'] = 'prt';
        if ($b->blog_start === null) {
            $vars['sty'] = '';
            $vars['stm'] = '';
            $vars['std'] = '';
        } else {
            $vars['sty'] = date('Y', strtotime($b->blog_start));
            $vars['stm'] = idate('m', strtotime($b->blog_start));
            $vars['std'] = date('d', strtotime($b->blog_start));
        }
        if ($b->latitude) {
            $vars['latitude'] = $b->latitude;
        }
        if ($b->longitude) {
            $vars['longitude'] = $b->longitude;
        }
        if ($b->blog_geonameid) {
            $vars['geonameid'] = $b->blog_geonameid;
        }
        if ($b->geonamesname) {
            $vars['geonamename'] = $b->geonamesname;
        }
        if ($b->fk_countrycode) {
            $vars['geonamecountrycode'] = $b->fk_countrycode;
        }
        if ($b->geonamecountry) {
            $vars['geonamecountry'] = $b->geonamecountry;
        }
        if ($b->fk_admincode) {
            $vars['admincode'] = $b->fk_admincode;
        }
        

        // fetch tags.
        $query = '
SELECT bt.`name`
FROM `blog_to_tag` b2t
JOIN `blog_tags` bt ON b2t.`blog_tag_id_foreign` = bt.`blog_tag_id`
WHERE b2t.`blog_id_foreign` = '.(int)$blogId.'
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve tags!');
        }
        $tags = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $tags[] = $row->name;
        }
        $vars['tags'] = implode(', ', $tags);
        return true;
    }

    /**
     * @return True if the blog category belongs to the user.
     */
    public function isUserBlogCategory($userId, $blogcatId)
    {

        $query = <<<SQL
SELECT COUNT(*) AS num
FROM blog_categories
WHERE IdMember = '{$this->dao->escape($userId)}' AND blog_category_id = '{$this->dao->escape($blogcatId)}'
SQL;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not validate blog category id!');
        }
        return ($s->fetchColumn(0) > 0);
    }
    
    public function isUserPost($userId, $postId)
    {
        $query = '
SELECT 
    `blog_id`
FROM `blog`
WHERE
    IdMember = '.(int)$userId.'
    AND
    `blog_id` = '.(int)$postId.'
        ';
        $s = $this->dao->query($query);
        return $s->numRows();
    }

    /**
     * @return True if the trip belongs to the user.
     */
    public function isUserTrip($userId, $tripId) {

        $query = <<<SQL
SELECT COUNT(*) AS num
FROM trip
WHERE IdMember = '{$this->dao->escape($userId)}' AND trip_id = '{$this->dao->escape($tripId)}'
SQL;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not validate trip id!');
        }
        return ($s->fetchColumn() > 0);
    }

    public function getComments($blogId) {
        $query = '
SELECT
    c.id AS comment_id,
    c.IdMember AS IdMember,
    m.Username AS `user_handle`,
    UNIX_TIMESTAMP(c.created) AS unix_created,
    c.title,
    c.text
FROM blog_comments c
LEFT JOIN members m ON c.IdMember =m.id
WHERE c.`blog_id_foreign` = '.(int)$blogId.'
AND m.Status in ("Active","Pending","ChoiceInactive","OutOfRemind","PassedAway")
        ';
        $s = $this->dao->query($query);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }

    /**
     * Count comments for a blog post, respecting commenter's visibility
     *
     * @param int $postId ID of blog post
     *
     * @return int Number of comments
     */
    public function countComments($postId) {
        $postId = intval($postId);
        $query = "
            SELECT
                COUNT(blog_comments.id) as count
            FROM
                blog_comments
            LEFT JOIN
                members
                ON blog_comments.IdMember = members.id
            WHERE
                blog_comments.blog_id_foreign = $postId
                AND
                members.Status IN (
                    'Active',
                    'Pending',
                    'ChoiceInactive',
                    'OutOfRemind',
                    'PassedAway'
                )
            ";
        $result = $this->bulkLookup($query);
        if (isset($result[0]) && isset($result[0]->count)) {
            $count = intval($result[0]->count);
        } else {
            $count = 0;
        }
        return $count;
    }

    public function getCategoryFromUserIt($userid,$galleryid = false)
    {
        $query = '
SELECT `blog_category_id`, `name` 
FROM `blog_categories` ';
        if ($userid) $query .= "
WHERE IdMember = '" .(int)$userid. "'";
        elseif ($galleryid) $query .= '
WHERE `blog_category_id` = \''.(int)$galleryid.'\'';
        else throw new PException('Could not retrieve blog categories! '.$userid);
        $query .= '
ORDER BY `name` ASC';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog categories!');
        }
        return $s;
    }

    /**
     * returns array of categories
     *
     * @param int $category_id
     * @param object $member
     * @access public
     * @return array
     * @throws PException
     */
    public function getCategoryArray($category_id = false, $member = false)
    {
        if (intval($category_id))
        {
            $where = "blog_category_id = {$this->dao->escape($category_id)}";
        }
        elseif ($member && $member instanceof Member)
        {
            $where = "IdMember = {$member->id}";
        }
        else
        {
            return array();
        }
        $query = <<<SQL
SELECT blog_category_id, name 
FROM blog_categories
WHERE {$where}
ORDER BY name ASC
SQL;
        return $this->bulkLookup($query);
    }

    public function getTripFromUserIt($userid)
    {
        $query = <<<SQL
SELECT t.trip_id, td.trip_name
FROM trip AS t, trip_data AS td
WHERE t.IdMember = '{$this->dao->escape($userid)}'
AND t.trip_id = td.trip_id
ORDER BY td.trip_name ASC
SQL;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve trips!');
        }
        return $s;
    }


    /**
     * Returns a statement iterator of all posts
     * or if $userId is given only those that belong
     * to that user. Sorted by created DESC.
     *
     * @arg int $userId Filters for posts having this user_id.
     */
    public function getRecentPostIt($userId = false, $categoryId = false)
    {
        $query = Blog::SQL_BLOGPOST;
        if ($categoryId) {
            $query .= '
JOIN `blog_to_category` bc ON (b.`blog_id` = bc.`blog_id_foreign`)
WHERE bc.`blog_category_id_foreign` = '.(int)$categoryId;
        } elseif ($userId) {
            $query .= '
WHERE b.IdMember = '.intval($userId);
        } else {
            $query .= '
WHERE 1';
// do not include sticky posts
//            $query .= 'WHERE b.`flags` & '.(int)Blog::FLAG_STICKY.' = 0';
        }
        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
        	$query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        /** temporarily removed, pending check on whether it's used - then needs refactoring
        OR (
            `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
            AND
            (SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = b.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$member->id.')
        )
            ';
            */
        }
        $query .= '
    )
GROUP BY b.`blog_id`
ORDER BY b.`blog_created` DESC';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog posts.');
        }
        return $s;
    }


    /**
     * Returns a statement iterator of all posts
     * or if $userId is given only those that belong
     * to that user. Sorted by created DESC.
     *
     * @arg int $userId Filters for posts having this user_id.
     */
    public function countRecentPosts($userId = false, $categoryId = false)
    {
        $query = Blog::SQL_BLOGPOST;
        if ($categoryId)
        {
            $query .= '
JOIN `blog_to_category` bc ON (b.`blog_id` = bc.`blog_id_foreign`)
WHERE bc.`blog_category_id_foreign` = '.(int)$categoryId;
        }
        elseif ($userId)
        {
            $query .= '
WHERE b.IdMember = '.intval($userId);
        }
        else
        {
            $query .= '
WHERE 1';
        }

        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember())
        {
        	$query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        }
        $query .= '
    )
GROUP BY b.`blog_id`
ORDER BY b.`blog_created` DESC';
        $query = "SELECT COUNT(blog_id) AS posts FROM ({$query}) AS temp";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog posts.');
        }
        $res = $s->fetch(PDB::FETCH_OBJ);
        return $res->posts;
    }

    /**
     * Returns a statement iterator of all posts
     * or if $userId is given only those that belong
     * to that user. Sorted by created DESC.
     *
     * @arg int $userId Filters for posts having this user_id.
     */
    public function getRecentPostsArray($userId = false, $categoryId = false, $page = 1)
    {
        $categoryId = is_numeric($categoryId) ? $categoryId : false;
        $query = Blog::SQL_BLOGPOST;
        if ($categoryId) {
            $query .= '
JOIN `blog_to_category` bc ON (b.`blog_id` = bc.`blog_id_foreign`)
WHERE bc.`blog_category_id_foreign` = '.(int)$categoryId;
        } elseif ($userId) {
            $query .= '
WHERE b.IdMember = '.intval($userId);
        } else {
            $query .= '
WHERE 1';
// do not include sticky posts
//            $query .= 'WHERE b.`flags` & '.(int)Blog::FLAG_STICKY.' = 0';
        }
        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
        	$query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        /** temporarily removed, pending check on whether it's used - then needs refactoring
        OR (
            `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
            AND
            (SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = b.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$member->id.')
        )
            ';
            */
        }
        $query .= '
    )
GROUP BY b.`blog_id`
ORDER BY b.`blog_created` DESC';

        $page = (($page < 1) ? 1 : $page);
        $offset = ($page - 1) * 5;
        $query .= " LIMIT {$offset}, 5";
        $recentPosts = $this->bulkLookup($query);

        return $recentPosts;
    }

    /**
     * Retrieves one single post.
     * @return The post object.
     */
    public function getPost($postId)
    {
        $query = Blog::SQL_BLOGPOST.'
WHERE b.`blog_id` = '.(int)$postId.'
GROUP BY b.`blog_id`
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog post.');
        }
        return $s->fetch(PDB::FETCH_OBJ);
    }

    public function getStickyPostIt()
    {
        $query = Blog::SQL_BLOGPOST;
        $query .= 'WHERE b.`flags` & '.(int)Blog::FLAG_STICKY;
        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
            $query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        /** temporarily removed, pending check on whether it's used - then needs refactoring
        OR (
            `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
            AND
            (SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = b.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$member->id.')
        )
            */
        }
        $query .= '
    )
GROUP BY b.`blog_id`
ORDER BY bd.`edited` DESC';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve blog posts.');
        }
        return $s;
    }

    /**
     * Get all tags or only those that contain a string.
     * @arg string $like String to be found in returned tags.
     * @arg int $limit How many tags to return at maximum.
     * @return The tag iterator (`name`, `usecount`).
     */
    public function getTagsIt($like = false, $limit = false)
    {
        $query = '
SELECT 
    `name`,
    COUNT(DISTINCT bl.`blog_id`) AS `usecount`
FROM `blog_tags` AS t
LEFT JOIN `blog_to_tag` AS b ON
    b.`blog_tag_id_foreign` = t.`blog_tag_id`
LEFT JOIN `blog` AS `bl` ON';
        // visibility
        $query .= '
    bl.`blog_id` = b.`blog_id_foreign` 
    AND
    (
        (
            bl.`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND bl.`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
            $query .= '
        OR (bl.`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND bl.IdMember = '.(int)$member->id.')
        OR (bl.`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND bl.IdMember = '.(int)$member->id.')
            ';
           /** taken out pending refactoring and removal of user table 
        OR (
            bl.`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
            AND
            (SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = bl.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$member->id.')
        )
            */
        }
        $query .= '
    )
WHERE
'.($like && !empty($like)?' AND `name` = \''.$this->dao->escape($like).'\'':'1').'
GROUP BY t.`blog_tag_id`
HAVING `usecount` > 0
ORDER BY `usecount` DESC
'.($limit?' LIMIT '.(int)$limit.'':'').'
';
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Failed enumerating tags.');
        }
        return $s;
    }

    public function getTaggedPostsIt($like, $latest = false)
    {
        $query = Blog::SQL_BLOGPOST.'
LEFT JOIN `blog_to_tag` b2t ON b.`blog_id` = b2t.`blog_id_foreign`
LEFT JOIN `blog_tags` bt ON b2t.`blog_tag_id_foreign` = bt.`blog_tag_id`
WHERE bt.`name` LIKE \'%'.$this->dao->escape($like).'%\'';
        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
            $query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        /* taken out, pending refactoring
        OR (
            `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
            AND
            (SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = b.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$member->id.')
        )
            */
        }
        $query .= '
    )
GROUP BY b.`blog_id`
';
        if ($latest)
        {
            $query .= ' ORDER BY blog_created DESC';
        }
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve posts matching tag!');
        }
        return $s;
    }


    /**
     * Gets all tags of given post.
     * @return The iterator to enumerate all tagnames.
     */ 
    public function getPostTagsIt($postId, $tag = false)
    {
        $query = '
SELECT t.`name`
FROM `blog_to_tag` b2t
JOIN `blog_tags` t ON b2t.`blog_tag_id_foreign` = t.`blog_tag_id`
WHERE b2t.`blog_id_foreign` = '.(int)$postId;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve tags!');
        }
        return $s;
    }


    /**
     * Validate post id.
     * @return True if the given $id is belonging to a post.
     */
    public function isPostId($id) {
        $query = 'SELECT `blog_id` AS `num` FROM `blog` WHERE `blog_id` = '.(int)$id;
        $s = $this->dao->query($query);
        return ($s->numRows() !== 0);
    }

    /* removed - referencing app_user which is being deleted
    public function settingsProcess($vars)
    {
        if (!isset($vars['vis']))
            $vars['vis'] = 'v';
        switch($vars['vis']) {
            // public
            case 'p':
                A PP_User::addSetting($User->getId(), 'APP_blog_defaultVis', null, 2);
                break;
            
            case 'r':
                A PP_User::addSetting($User->getId(), 'APP_blog_defaultVis', null, 1);
                break;
            
            default:
                A PP_User::addSetting($User->getId(), 'APP_blog_defaultVis', null, 0);
                break;
        }
        return false;
    }
    */

    /**
     * Processing creation of a comment
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * title        - invalid(empty) title.
     * textlen      - too short or long text.
     * inserror     - db error while inserting.
     */
    public function commentProcess(&$vars, $request, $blogId)
    {
        // validate
        if (!isset($vars['ctxt']) || strlen($vars['ctxt']) == 0 || strlen($vars['ctxt']) > 5000) {
            $vars['errors'] = array('textlen');
            return false;
        }
        $member = $this->getLoggedInMember();

        $commentId = $this->dao->nextId('blog_comments');
        $query = '
INSERT INTO `blog_comments`
SET
    `id`='.$commentId.',
    `blog_id_foreign`='.(int)$blogId.',
    IdMember ='.$member->id.',
    `title`=\''.(isset($vars['ctit'])?$this->dao->escape($vars['ctit']):'').'\',
    `text`=\''.$this->dao->escape($vars['ctxt']).'\',
    `created`=NOW()';
        $s = $this->dao->query($query);
        if (!$s) {
            $vars['errors'] = array('inserror');
            return false;
        }
        return $commentId;
    }

    /**
     * Processing creation of a category
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * nameinvalid  - invalid category or not belonging to this user.
     * nameempty    - empty name.
     * namedupe     - the user has already a category with this name.
     * inserror     - db error while inserting.
     * upderror     - db error while updating.
     * delerror     - db error while deleting.
     */
    public function categoryProcess(&$vars, $request)
    {
        $member = $this->getLoggedInMember();
        if (isset($request[2])) {
            switch ($request[2]) {
            case 'edit':
                if (!$this->isUserBlogCategory($member->id, $request[3])) {
                    $vars['errors'] = array('nameinvalid');
                    return false;
                }
                if (!isset($vars['n']) || strcmp($vars['n'], '')==0) {
                    $vars['errors'] = array('nameempty');
                    return false;
                }
                $query = '
SELECT COUNT(*)
FROM `blog_categories`
WHERE IdMember = '.$member->id.' AND `name` = \''.$this->dao->escape($vars['n']).'\'
';
                $s = $this->dao->query($query);
                if (!$s) {
                    $vars['errors'] = array('upderror');
                    return false;
                }
                if ($s->fetchColumn(0) != 0) {
                    $vars['errors'] = array('namedupe');
                    return false;
                }

                $query = '
    UPDATE `blog_categories`
    SET `name` = \''.$this->dao->escape($vars['n']).'\'
    WHERE `blog_category_id` = '.(int)$request[3].'
    ';
                $s = $this->dao->query($query);
                if (!$s) {
                    $vars['errors'] = array('upderror');
                    return false;
                }
                break;


            case 'del':
                if (isset($vars['no']))
                {
                    return true;
                }
                if (!$this->isUserBlogCategory($member->id, $request[3])) {
                    $vars['errors'] = array('invalid');
                    return false;
                }
                $query = '
    DELETE FROM `blog_categories`
    WHERE `blog_category_id` = '.(int)$request[3].'
    ';
                $s = $this->dao->query($query);
                if (!$s) {
                    $vars['errors'] = array('delerror');
                    return false;
                }
                break;
            }
        } else { // !isset($request[2])
            if (!isset($vars['n']) || strcmp($vars['n'], '')==0) {
                $vars['errors'] = array('name');
                return false;
            }
            $query = '
SELECT COUNT(*)
FROM `blog_categories`
WHERE IdMember = '.$member->id.' AND `name` = \''.$this->dao->escape($vars['n']).'\'
';
            $s = $this->dao->query($query);
            if (!$s) {
                $vars['errors'] = array('inserror');
                return false;
            }
            if ($s->fetchColumn(0) != 0) {
                $vars['errors'] = array('namedupe');
                return false;
            }

            $query = '
INSERT INTO `blog_categories`
SET
`blog_category_id` = '.$this->dao->nextId('blog_categories').',
    `name` = \''.$this->dao->escape($vars['n']).'\',
    IdMember = '.$member->id.'
    ';
            $s = $this->dao->query($query);
            if (!$s) {
                $vars['errors'] = array('inserror');
                return false;
            }
        }

        return true;
    }
    
    /**
    * Search for tags to suggest
    * Checks which word is being edited and looks for possible matches
    *
    * @param string $search comma-delimited search words
    * @return stringarray 2dimensional array with the new suggested tags
    */
    public function suggestTags($search) 
    {
        // Split words
        $words = explode(',', $search);
        $cleaned = array();
        // Clean up
        foreach ($words as $word) {
            $word = trim($word);
            if ($word) {
                $cleaned[] = $word;
            }
        }
        $words = $cleaned;

        // Which word is the person changing?
        $number_words = count($words);
        if ($number_words && isset($_SESSION['prev_tag_content']) && $_SESSION['prev_tag_content']) {
            $search_for = false;
            $pos = false;
            for ($i = 0; $i < $number_words; $i++) {
                if (isset($words[$i]) && (!isset($_SESSION['prev_tag_content'][$i]) || $words[$i] != $_SESSION['prev_tag_content'][$i])) {
                    $search_for = $words[$i];
                    $pos = $i;
                }
            }
            if (!$search_for) {
                return array();
            }
        } else if ($number_words) {
            $search_for = $words[count($words) - 1]; // last word
            $pos = false;
        } else {
            return array();
        }

        if ($search_for) {
    
            $_SESSION['prev_tag_content'] = $words;
        
            // look for possible matches (from ALL tags)
// TODO:
// Limit number of returned tags? Order by popularity?
// TODO:
// Use $this->getTagsIt()?
            $query = "SELECT `name`
                FROM `blog_tags`
                WHERE `name` LIKE '".$this->dao->escape($search_for)."%'";
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve tag entries');
            }
            $tags = array();
            while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                $tags[] = $row->name;
            }
            
            if ($tags) {
                $out = array();
                $suggestion_number = 0;
                foreach ($tags as $w) {
                    $out[$suggestion_number] = array();
                    for ($i = 0; $i < count($words); $i++) {
                        if ($i == $pos) {
                            $out[$suggestion_number][] = $w;
                        } else {
                            $out[$suggestion_number][] .= $words[$i];
                        }
                    }
                    $suggestion_number++;
                }
                return $out;
            }
        }
        return array();
    }

    
    public function updatePost($blogId, $flags, $tripId = false)
    {
        // insert into db
        $query = '
UPDATE `blog`
SET
    `flags` = '.(int)$flags.',
    `trip_id_foreign` = '.($tripId ? (int)$tripId : 'NULL').'
WHERE `blog_id` = '.(int)$blogId.'
';
        return $this->dao->exec($query);
    }
    
    public function updatePostData($blogId, $title, $txt, $start = false, $geonameId = false)
    {
        $query = '
UPDATE `blog_data`
SET
    `edited` = NOW(),
    `blog_title` = \''.$this->dao->escape($title).'\',
    `blog_text` = \''.$this->dao->escape($txt).'\',
    `blog_start` = '.($start ? $this->dao->escape($start) : 'NULL').',
    `blog_geonameid` = '.($geonameId ? (int)$geonameId : 'NULL').'
WHERE `blog_id` = '.(int)$blogId.'
        ';
        return $this->dao->exec($query);
    }
    
    public function updateBlogToCategory($blogId, $category)
    {
        $query = '
SELECT COUNT(*) AS num
FROM `blog_to_category`
WHERE
    `blog_id_foreign` = '.(int)$blogId.'
    ';
        $s = $this->dao->query($query);
        if ($s) {
        $query = '
DELETE
FROM `blog_to_category`
WHERE
    `blog_id_foreign` = '.(int)$blogId.'
    ';
        $this->dao->exec($query);
        }

        if ($category) {
        $query = '
INSERT INTO `blog_to_category`
SET
    `created` = NOW(),
    `blog_category_id_foreign` = \''.$this->dao->escape($category).'\',
    `blog_id_foreign` = '.(int)$blogId.'
    ';
        return $this->dao->exec($query);
        }
    }

	
	//replaced by functionality in geo, see below
    // /**
    // * Checks if a location is already in the local geonames cache
    // * If not -> add it
    // * @return true on success
    // * @return false if the location could not be stored
    // */
    // public function checkGeonamesCache($geonameid, $latitude, $longitude, $geonamename, $geonamecountrycode, $admincode) {
        // $s = $this->dao->prepare("SELECT `geonameid` FROM `geonames_cache` WHERE `geonameid` = ?");
        // $s->execute(array($geonameid));
        // if ($s->numRows() == 0) { // We have to insert it
            // $query = "
// INSERT INTO `geonames_cache` 
// (`geonameid`, `latitude`, `longitude`, `name`, `fk_countrycode`, `fk_admincode`)
// VALUES
// (
    // '".$this->dao->escape($geonameid)."',
    // '".$this->dao->escape($latitude)."',
    // '".$this->dao->escape($longitude)."',
    // '".$this->dao->escape($geonamename)."',
    // '".$this->dao->escape($geonamecountrycode)."',
    // '".$this->dao->escape($admincode)."'
// )";
            // try {
                // $s = $this->dao->query($query);
            // } catch (PException $e) {
                // if (PVars::get()->debug) {
                    // throw $e;
                // } else {
                    // error_log($e->__toString());
                // }
                // return false;
            // }
        // }
        // return true;
    // }



	/**
	* Add location to the databse
	* adds a new location to geonames_cache if it does not yet exist, updates the hierarchy and usage tables
	**/
	public function checkGeonamesCache($geonameId) {
		$geomodel = new GeoModel();
		if(!$geomodel->checkGeonameId($geonameId,'trip')) {
			return false;
		} else {
			return true;
		}
	}
	
	
    /**
     * Search for blog posts
     *
     * @param string $search plus(+)-delimited search words
     * @access public
     * @return array
     * @throws PException
     */
    public function searchPosts($search_for) 
    {
        $query = Blog::SQL_BLOGPOST;
/*        $query .= "JOIN `blog_tags`.`name` AS `tags` ON (`blog_tags` LIKE '".$this->dao->escape($search_for)."%')"; */
        $query .= "WHERE `blog_title` LIKE '%{$this->dao->escape($search_for)}%'
                    OR `blog_text` LIKE '%{$this->dao->escape($search_for)}%'
                    OR m.Username LIKE '%{$this->dao->escape($search_for)}%'
                    ";
                    
        // visibility
        $query .= '
    AND
    (
        (
            `flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0 
            AND `flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
        )
        ';
        if ($member = $this->getLoggedInMember()) {
            $query .= '
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND b.IdMember = '.(int)$member->id.')
        OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND b.IdMember = '.(int)$member->id.')
        ';
        }
        $query .= '
    )
GROUP BY b.`blog_id`
ORDER BY b.`blog_created` DESC LIMIT 20';
        return $this->bulkLookup($query);
    }

    public function getMemberByUsername($username)
    {
        return $this->createEntity('Member')->findByUsername($username);
    }

    public function getTinyMCEPreference() {
        $member = $this->getLoggedInMember();
        return $member->getPreference("PreferenceDisableTinyMCE", $default = "No");
    }
}
