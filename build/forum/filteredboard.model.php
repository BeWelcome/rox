<?php


class ForumFilteredBoard extends RoxEntityBase
{
    public function __construct($values, $dao)
    {
        parent::__construct($values, $dao);
    }
    
    //--------------------------------------------------------------------------------
    
    
    protected function get_where_filters()
    {
        $where_filters = array();
        $filters = $this->filters;
        if ($filters->geoname_id) {}
    }
    
    
    protected function get_sortkey_formula()
    {
        $filters = $this->filters;
    }
    
    
    protected function get_topicsInRange($begin, $count, $sortkey_formula = false)
    {
        // TODO: filtered topics in range
        
        if (!$sortkey_formula) {
            $sortkey_formula = $this->sortkey_formula;
        }
        
        $topics = $this->bulkLookup(
            "
SELECT 
    forums_threads.id AS post_id,
    forums_posts.id AS postid,
    forums_posts.threadid AS topic_id,
    UNIX_TIMESTAMP(forums_posts.create_time) AS posttime,
    message,
    IdContent,
    user.id AS user_id,
    user.handle AS user_handle,
    geonames_cache.fk_countrycode
    $sortkey_formula AS sortkey
FROM
    forums_threads
    LEFT JOIN user ON (forums_posts.authorid = user.id)
    LEFT JOIN geonames_cache ON (user.location = geonames_cache.geonameid)
WHERE
    ".implode(' AND
    ', $this->where_filters)."
ORDER BY
    sortkey ASC
LIMIT $begin, $count
            ",
            'post_id'
        );
        
        if (empty($topics)) {
            return $topics;
        }
        
        $min_sortkey = reset($topics)->sortkey;
        $max_sortkey = end($topics)->sortkey;
        
        foreach ($this->bulkLookup(
            "
SELECT
    forum_trads.Sentence,
    languages.id AS language_id,
    languages.ShortCode AS language_code,
    forums_posts.id AS post_id,
    languages.*,
    members.Username AS TranslatorUsername
FROM
    forum_trads,
    languages,
    members,
    forums_posts
WHERE
    $sortkey_formula >= '$min_sortkey'                         AND
    $sortkey_formula <= '$max_sortkey'                         AND
    forum_trads.TableColumn = 'forums_posts.IdContent'         AND
    languages.id = forum_trads.IdLanguage                      AND
    forum_trads.IdRecord = forums_posts.id                     AND
    members.id = forum_trads.IdTranslator                      AND
    UNIX_TIMESTAMP(forums_posts.create_time) >= $min_unixtime  AND
    UNIX_TIMESTAMP(forums_posts.create_time) <= $max_unixtime
ORDER BY
    forum_trads.id ASC
            ",
            array(
                'post_id', 'language_code'
            )
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
    
    
    protected function get_queryString($strings)
    {
        foreach (array(
            'select_string' => '*',
            'from_string' => 'forums_threads',
            'where_string' => '',   // TODO: this is the critical part!!
            'sort_string' => ''
        ) as $key => $value) {
            if (!isset($strings->$key) || !$strings->$key) {
                $strings->$key = $value;
            }
        }
        return
            "
SELECT
    $args->select_string
FROM
    $args->from_string
    ".is_array($filters->tags) ? implode('
    ', array_filter($filters->tags, array($this, 'sql_leftjoin_tag'))) : ''."
    LEFT JOIN user ON (forums_posts.authorid = user.id)
    LEFT JOIN geonames_cache ON (user.location = geonames_cache.geonameid)
WHERE
    $args->where_string
    ".implode(' AND
    ', $this->where_filters)."
$args->sort_string
            "
        ;
    }
    
    
    //--------------------------------------------------------------------------------
    
    
    protected function get_numberOfTopics()
    {
        return $this->singleLookup(
            "
SELECT
    COUNT(*) AS cnt
FROM
    forums_threads
WHERE
    ".implode(' AND
    ', $this->where_filters)."
            "
        )->cnt;
    }
}


?>