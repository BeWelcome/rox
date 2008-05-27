<?php
/**
* Forum Topic
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
*  @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
*/


class ForumTopic extends RoxEntityBase
{
    public function __construct($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    //--------------------------------------------------------------------------------
    
    
    protected function get_posts()
    {
        return $this->bulkLookup(
            "
SELECT 
    postid,
    UNIX_TIMESTAMP(create_time)    AS posttime,
    message,
    IdContent,
    user.id                        AS user_id,
    user.handle                    AS user_handle,
    geonames_cache.fk_countrycode
FROM
    forums_posts
    LEFT JOIN user            ON (forums_posts.authorid = user.id)
    LEFT JOIN geonames_cache  ON (user.location = geonames_cache.geonameid)
WHERE
    threadid  =  ".$this->topic_id."
ORDER BY
    posttime ASC
            "
        );
    }
    
    //--------------------------------------------------------------------------------
    
    
    protected function get_postsInRange($begin, $count)
    {
        $posts = $this->bulkLookup(
            "
SELECT 
    forums_posts.id                           AS post_id,
    forums_posts.id                           AS postid,
    forums_posts.threadid                     AS topic_id,
    UNIX_TIMESTAMP(forums_posts.create_time)  AS posttime,
    message,
    IdContent,
    user.id                                   AS user_id,
    user.handle                               AS user_handle,
    geonames_cache.fk_countrycode
FROM
    forums_posts
    LEFT JOIN user            ON (forums_posts.authorid = user.id)
    LEFT JOIN geonames_cache  ON (user.location = geonames_cache.geonameid)
WHERE
    threadid  =  ".$this->topic_id."
ORDER BY
    posttime ASC
LIMIT $begin, $count
            ",
            'post_id'
        );
        
        if (empty($posts)) {
            return $posts;
        }
        
        $min_unixtime = reset($posts)->posttime;
        $max_unixtime = end($posts)->posttime;
        
        foreach ($this->bulkLookup(
            "
SELECT
    forum_trads.Sentence,
    languages.id          AS language_id,
    languages.ShortCode   AS language_code,
    forums_posts.id       AS post_id,
    languages.*,
    members.Username      AS TranslatorUsername
FROM
    forum_trads,
    languages,
    members,
    forums_posts
WHERE
    forum_trads.TableColumn = 'forums_posts.IdContent'         AND
    languages.id = forum_trads.IdLanguage                      AND
    forum_trads.IdRecord = forums_posts.id                     AND
    members.id = forum_trads.IdTranslator                      AND
    UNIX_TIMESTAMP(forums_posts.create_time) >= $min_unixtime  AND
    UNIX_TIMESTAMP(forums_posts.create_time) <= $max_unixtime
ORDER BY
    forum_trads.id ASC
            ",
            array('post_id', 'language_code')
            
        ) as $post_id => $trad_array) {
            if (isset($posts[$post_id])) {
                $posts[$post_id]->trads = $trad_array;
                $lang = PVars::get()->lang;
                if (isset($trad_array[$lang])) {
                    $post->active_trad = $trad_array[$lang]; 
                }
            }
        }
        
        return $posts;
    }

    
    //--------------------------------------------------------------------------------
    
    
    protected function get_numberOfPosts()
    {
        return $this->singleLookup(
            "
SELECT
    COUNT(*) AS cnt
FROM
    forums_posts
WHERE
    threadid = ".$this->topic_id."
            "
        )->cnt;
    }
    
    //--------------------------------------------------------------------------------
    
    
    protected function get_tags()
    {
        $tags = $this->bulkLookup(
            "
SELECT
    forums_tags.* 
FROM
    tags_threads,
    forums_tags
WHERE
    tags_threads.IdThread  = ".$this->topic_id."    AND
    tags_threads.IdTag     = forums_tags.tagid
            ",
            'post_id'
        );
        
        if (empty($tags)) {
            // do nothing
        } else foreach (array(
            'tagname_trads' => 'IdName',
            'tagdesc_trads' => 'IdDescription'
        ) as $trad_type => $trad_colname) {
        
            foreach ($this->bulkLookup(
                "
SELECT
    forum_trads.*,
    tags_threads.IdTag   AS tag_id,
    languages.id         AS language_id,
    languages.ShortCode  AS language_code,
    members.Username     AS TranslatorUsername
FROM
    forum_trads,
    languages,
    members,
    tags_threads
WHERE
    tags_threads.IdThread   = $this->topic_id              AND
    tags_threads.IdTag      = forum_trads.IdRecord         AND
    forum_trads.TableColumn = 'forums_tags.$trad_colname'  AND
    languages.id            = forum_trads.IdLanguage       AND
    members.id              = forum_trads.IdTranslator
                ",
                array('tag_id', 'language_code')
                
            ) as $tag_id => $trad_array) {
                if (isset($tags[$tag_id])) {
                    $tags[$post_id]->$trad_type = $trad_array;
                    $lang = PVars::get()->lang;
                    if (isset($trads[$lang])) {
                        $trad_type_active = $trad_type.'_active';
                        $tag->$trad_type_active = $trad_array[$lang];
                    }
                }
            }
        }
        
        return $tags;        
    }
    
    
    protected function get_subscriptionForMember($member_id)
    {
        return $this->singleLookup(
            "
SELECT
    members_threads_subscribed.id              AS IdSubscribe,
    members_threads_subscribed.UnSubscribeKey  AS IdKey 
FROM
    members_threads_subscribed
WHERE
    IdThread = $this->topic_id  AND
    IdSubscriber = $member_id
            "
        );
    }
    
    
    public function incrementViewCounter()
    {
        $this->dao->query(
            "
UPDATE
    forums_threads
SET
    views = (views + 1)
WHERE
    threadid = $this->topic_id
LIMIT 1
            "
        );
    }
}


?>